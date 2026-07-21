<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsCache;
use App\Models\Country;
use App\Services\NewsService;
use App\Services\SentimentAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Kelas NewsController: news controller
class NewsController extends Controller
{
    protected NewsService $newsService;
    protected SentimentAnalysisService $sentimentService;

    // inisialisasi objek
    public function __construct(NewsService $newsService, SentimentAnalysisService $sentimentService)
    {
        $this->newsService = $newsService;
        $this->sentimentService = $sentimentService;
    }

    // index
    public function index(Request $request)
    {
        $query = NewsCache::query();

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        if ($request->has('sentiment')) {
            $query->where('sentiment_result', $request->sentiment);
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Urutan FIFO tampil: terbaru (published_at) di atas, berurutan ke bawah.
        // Lantai minimal 7 item per halaman, walau caller minta limit lebih kecil.
        $perPage = max(7, (int) $request->input('limit', 20));
        $news = $query->latest('published_at')->paginate($perPage);

        return response()->json($news)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    // fetch
    public function fetch(Request $request)
    {
        try {
            $request->validate([
                'country_id' => 'nullable|exists:countries,id',
                'query' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            $query = $request->input('query', 'logistics trade shipping economy');
            $category = $request->input('category', 'general');

            $country = null;
            if ($request->has('country_id')) {
                $country = Country::find($request->country_id);
            }

            Log::info('Fetching news for query: ' . $query);

            $articles = $this->newsService->getNews(
                $query,
                $country ? $country->code : null,
                $request->input('limit', 20)
            );

            Log::info('Found ' . count($articles) . ' articles from live API');

            
            
            
            
            if (empty($articles)) {
                Log::info('Live API returned nothing, falling back to cached real news from DB.');

                $cachedQuery = NewsCache::query();

                // Utamakan match by category — ini jauh lebih pasti nemu data,
                // karena news:sync sudah rutin isi news_cache per kategori tiap
                // 3 jam. Sebelumnya fallback ini HANYA cari lewat LIKE keyword
                // di title/description, yang gampang meleset (misal tidak ada
                // artikel tersimpan yang judul/deskripsinya persis mengandung
                // kata "logistics"/"shipping" dst) walau data kategori itu
                // sebenarnya banyak — makanya hasilnya sering kosong.
                if (!empty($category) && $category !== 'general') {
                    $cachedQuery->where('category', $category);
                } else {
                    $keywords = preg_split('/\s+/', trim($query));
                    if (!empty($keywords)) {
                        $cachedQuery->where(function ($q) use ($keywords) {
                            foreach ($keywords as $word) {
                                $q->orWhere('title', 'like', "%{$word}%")
                                  ->orWhere('description', 'like', "%{$word}%");
                            }
                        });
                    }
                }

                if ($country) {
                    $cachedQuery->orWhere('country_id', $country->id);
                }

                $cachedNews = $cachedQuery->latest('published_at')
                    ->limit($request->input('limit', 20))
                    ->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Live news API unavailable (quota/limit). Showing previously fetched real news from cache.',
                    'from_cache' => true,
                    'data' => $cachedNews,
                ]);
            }

            $results = [];
            foreach ($articles as $article) {
                $text = ($article['title'] ?? '') . ' ' . ($article['description'] ?? '');
                $sentiment = $this->sentimentService->analyze($text);

                
                $existing = NewsCache::where('url', $article['url'] ?? '')->first();
                if (!$existing) {
                    $newsItem = NewsCache::create([
                        'title' => $article['title'] ?? 'No title',
                        'description' => $article['description'] ?? null,
                        'content' => $article['content'] ?? null,
                        'url' => $article['url'] ?? null,
                        'source' => $article['source']['name'] ?? 'Unknown',
                        'image_url' => $article['image'] ?? null,
                        'category' => $category,
                        'country_id' => $country ? $country->id : null,
                        'sentiment_data' => $sentiment,
                        'sentiment_result' => $sentiment['sentiment'],
                        'published_at' => $article['publishedAt'] ?? now(),
                    ]);
                    $results[] = $newsItem;
                } else {
                    $results[] = $existing;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Fetched ' . count($results) . ' news articles',
                'from_cache' => false,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch news error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching news: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    // analyze sentiment
    public function analyzeSentiment(int $id)
    {
        $news = NewsCache::findOrFail($id);
        $text = $news->title . ' ' . ($news->description ?? '');

        $sentiment = $this->sentimentService->analyze($text);

        $news->sentiment_data = $sentiment;
        $news->sentiment_result = $sentiment['sentiment'];
        $news->save();

        return response()->json($sentiment);
    }

    // get sentiment summary
    public function getSentimentSummary(Request $request)
    {
        $query = NewsCache::query();

        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        $news = $query->get();
        $texts = [];

        foreach ($news as $item) {
            $texts[] = $item->title . ' ' . ($item->description ?? '');
        }

        if (empty($texts)) {
            return response()->json([
                'positive' => 0,
                'neutral' => 0,
                'negative' => 0,
                'positive_percent' => 0,
                'neutral_percent' => 0,
                'negative_percent' => 0,
            ]);
        }

        $distribution = $this->sentimentService->getSentimentDistribution($texts);

        return response()->json($distribution);
    }

    // get logistics news
    public function getLogisticsNews()
    {
        try {
            $articles = $this->newsService->getLogisticsNews(10);
            return $this->formatNewsResponse($articles);
        } catch (\Exception $e) {
            Log::error('Logistics news error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching logistics news',
                'data' => []
            ], 500);
        }
    }

    // get economy news
    public function getEconomyNews()
    {
        try {
            $articles = $this->newsService->getEconomyNews(10);
            return $this->formatNewsResponse($articles);
        } catch (\Exception $e) {
            Log::error('Economy news error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching economy news',
                'data' => []
            ], 500);
        }
    }

    // get trade news
    public function getTradeNews()
    {
        try {
            $articles = $this->newsService->getTradeNews(10);
            return $this->formatNewsResponse($articles);
        } catch (\Exception $e) {
            Log::error('Trade news error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching trade news',
                'data' => []
            ], 500);
        }
    }

    // format news response
    private function formatNewsResponse(array $articles): \Illuminate\Http\JsonResponse
    {
        $results = [];
        foreach ($articles as $article) {
            $text = ($article['title'] ?? '') . ' ' . ($article['description'] ?? '');
            $sentiment = $this->sentimentService->analyze($text);

            $results[] = [
                'title' => $article['title'] ?? 'No title',
                'description' => $article['description'] ?? null,
                'url' => $article['url'] ?? null,
                'source' => $article['source']['name'] ?? 'Unknown',
                'image_url' => $article['image'] ?? null,
                'published_at' => $article['publishedAt'] ?? now(),
                'sentiment_result' => $sentiment['sentiment'],
                'sentiment_data' => $sentiment,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }
}