<?php
// app/Console/Commands/SyncNews.php

namespace App\Console\Commands;

use App\Models\NewsCache;
use App\Services\NewsService;
use App\Services\SentimentAnalysisService;
use Illuminate\Console\Command;

class SyncNews extends Command
{
    protected $signature = 'news:sync';

    protected $description = 'Sync berita terbaru (logistics/economy/trade) dari GNews API ke news_cache secara berkala di background';

    protected array $categories = [
        'logistics' => 'logistics shipping supply chain',
        'economy'   => 'economy trade business',
        'trade'     => 'trade import export tariffs',
    ];

    protected int $maxPerCategory = 30;
    protected int $minPerCategory = 7;

    public function handle(NewsService $newsService, SentimentAnalysisService $sentimentService)
    {
        $this->info('Mulai sync berita untuk ' . count($this->categories) . ' kategori...');

        $totalSaved = 0;

        foreach ($this->categories as $category => $query) {
            $this->line("- Mengambil berita kategori '{$category}'...");

            $articles = $newsService->getNews($query, null, 20);

            if (empty($articles)) {
                $this->warn("  Tidak ada artikel baru untuk '{$category}' (API kosong/kuota habis, skip).");
                continue;
            }

            $savedThisCategory = 0;

            foreach ($articles as $article) {
                $url = $article['url'] ?? null;
                if (!$url) {
                    continue;
                }

               
               $existing = NewsCache::where('url', $url)->first();
if ($existing) {
    if (empty($existing->category) || $existing->category === 'general') {
        $existing->category = $category;
        $existing->save();
        $this->line("  Memperbaiki category artikel lama -> '{$category}': {$existing->title}");
    }
    continue;
}
                $text = ($article['title'] ?? '') . ' ' . ($article['description'] ?? '');
                $sentiment = $sentimentService->analyze($text);

                NewsCache::create([
                    'title' => $article['title'] ?? 'No title',
                    'description' => $article['description'] ?? null,
                    'content' => $article['content'] ?? null,
                    'url' => $url,
                    'source' => $article['source']['name'] ?? 'Unknown',
                    'image_url' => $article['image'] ?? null,
                    'category' => $category,
                    'country_id' => null,
                    'sentiment_data' => $sentiment,
                    'sentiment_result' => $sentiment['sentiment'],
                    'published_at' => $article['publishedAt'] ?? now(),
                ]);

                $savedThisCategory++;
            }

            $this->info("  {$savedThisCategory} artikel baru disimpan untuk '{$category}'.");
            $totalSaved += $savedThisCategory;

            $trimmed = $this->trimCategoryFifo($category);
            if ($trimmed > 0) {
                $this->line("  FIFO trim: {$trimmed} artikel paling lama dibuang dari '{$category}' (sisa maks {$this->maxPerCategory}).");
            }

            // Jeda singkat antar kategori supaya tidak nembak API 3x beruntun
            // (rate-limit friendly), sama seperti pola retry di RestCountriesService.
            sleep(1);
        }

        $this->info("Selesai. Total {$totalSaved} artikel baru tersimpan.");

        return self::SUCCESS;
    }

    /**
     *
     * @return int jumlah artikel yang dibuang
     */
    protected function trimCategoryFifo(string $category): int
    {
        $total = NewsCache::where('category', $category)->count();

        if ($total <= $this->maxPerCategory || $total <= $this->minPerCategory) {
            return 0;
        }

        // Batas aman: jangan sampai hasil akhir kurang dari minPerCategory.
        $keep = max($this->maxPerCategory, $this->minPerCategory);
        $excess = $total - $keep;

        if ($excess <= 0) {
            return 0;
        }

        // Ambil ID artikel paling lama (urutan FIFO: yang masuk duluan / published_at
        // paling kecil, keluar duluan) sebanyak $excess, lalu hapus.
        $idsToDelete = NewsCache::where('category', $category)
            ->orderBy('published_at', 'asc')
            ->limit($excess)
            ->pluck('id');

        if ($idsToDelete->isEmpty()) {
            return 0;
        }

        NewsCache::whereIn('id', $idsToDelete)->delete();

        return $idsToDelete->count();
    }
}