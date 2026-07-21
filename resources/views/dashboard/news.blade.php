{{-- Tampilan: news --}}

@extends('layouts.app')

@section('title', 'News Intelligence')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-newspaper me-2"></i>News Intelligence</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary active" onclick="fetchNews('all')">
                <i class="fas fa-globe me-1"></i>Semua
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="fetchNews('logistics')">
                <i class="fas fa-truck me-1"></i>Logistics
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="fetchNews('economy')">
                <i class="fas fa-chart-line me-1"></i>Economy
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="fetchNews('trade')">
                <i class="fas fa-handshake me-1"></i>Trade
            </button>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">Positive News</h6>
                <h2 class="text-success" id="positiveCount">0</h2>
                <small class="text-muted" id="positivePercent">0%</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">Neutral News</h6>
                <h2 class="text-warning" id="neutralCount">0</h2>
                <small class="text-muted" id="neutralPercent">0%</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body text-center">
                <h6 class="text-muted">Negative News</h6>
                <h2 class="text-danger" id="negativeCount">0</h2>
                <small class="text-muted" id="negativePercent">0%</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Sentiment Distribution</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-4">
                <div style="position: relative; height: 260px;">
                    <canvas id="sentimentChart"></canvas>
                </div>
            </div>
            <div class="col-lg-8 mt-4 mt-lg-0">
                <div class="d-flex align-items-center h-100">
                    <div class="w-100">
                        <div class="mb-2">
                            <span class="badge bg-success">Positive</span>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" id="positiveBar" style="width: 0%;">
                                    0%
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning">Neutral</span>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-warning" id="neutralBar" style="width: 0%;">
                                    0%
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-danger">Negative</span>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-danger" id="negativeBar" style="width: 0%;">
                                    0%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($articles->isNotEmpty())

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Artikel Internal</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($articles as $article)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    @if($article->image_url)
                    <img src="{{ $article->image_url }}" class="card-img-top" style="height:160px;object-fit:cover;" alt="{{ $article->title }}">
                    @endif
                    <div class="card-body">
                        @if($article->category)
                        <span class="badge bg-secondary mb-2">{{ $article->category }}</span>
                        @endif
                        <h6 class="card-title">{{ $article->title }}</h6>
                        <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit(strip_tags($article->content), 120) }}</p>
                    </div>
                    <div class="card-footer bg-white text-muted small">
                        @if($article->author)
                            <i class="fas fa-user me-1"></i>{{ $article->author }} ·
                        @endif
                        {{ $article->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Latest News</h5>
        <div>
            <select class="form-select form-select-sm" id="sentimentFilter" onchange="filterNews(this.value)">
                <option value="all">All Sentiment</option>
                <option value="Positive">Positive</option>
                <option value="Neutral">Neutral</option>
                <option value="Negative">Negative</option>
            </select>
        </div>
    </div>
    <div class="card-body" id="newsList">
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading news...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let sentimentChart = null;
    let allNews = [];

    // Load news on page load — BACA DARI CACHE (news_cache), bukan live-fetch.
    // Sebelumnya method ini SELALU manggil live GNews API tiap halaman dibuka,
    // padahal news_cache sekarang sudah disegarkan otomatis di background tiap
    // 3 jam lewat scheduled command `news:sync` (lihat routes/console.php).
    // Jadi baca cache = tetap segar, tapi instan & tidak habiskan kuota API.
    document.addEventListener('DOMContentLoaded', function() {
        loadCachedNews('all');
    });

    function loadCachedNews(category) {
        const newsList = document.getElementById('newsList');
        newsList.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Loading news...</p>
            </div>
        `;

        // cache: 'no-store' + timestamp cache-buster supaya browser tidak pernah
        // pakai response GET lama walau URL/kategori sama persis.
        fetch(`/api/news?category=${encodeURIComponent(category)}&limit=20&_=${Date.now()}`, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        })
        .then(response => response.json())
        .then(data => {
            allNews = data.data || [];

            // Fallback: kalau cache masih kosong untuk kategori ini (mis. belum
            // pernah di-sync / scheduler belum jalan di server), live-fetch
            // sekali secara otomatis — user tidak perlu klik "Refresh" manual dulu.
            //
            // Dibatasi cooldown 1 jam PER KATEGORI (disimpan di localStorage) supaya
            // klik tab berulang-ulang saat cache kosong tidak nembak GNews berkali-kali
            // dan menghabiskan kuota harian. Selama masih dalam cooldown, cukup
            // tampilkan pesan "belum ada data" alih-alih live-fetch lagi.
            if (allNews.length === 0) {
                const liveCategory = category === 'all' ? 'logistics' : category;
                const cooldownKey = `newsLiveFetchCooldown_${liveCategory}`;
                const oneHourMs = 60 * 60 * 1000;
                const lastFetch = parseInt(localStorage.getItem(cooldownKey) || '0', 10);
                const now = Date.now();

                if (now - lastFetch >= oneHourMs) {
                    fetchNewsLive(liveCategory);
                } else {
                    const minutesLeft = Math.ceil((oneHourMs - (now - lastFetch)) / 60000);
                    newsList.innerHTML = `<p class="text-muted text-center py-4">Belum ada berita baru untuk kategori ini. Coba lagi dalam ${minutesLeft} menit.</p>`;
                }
                return;
            }

            renderNews(allNews);
            updateSentimentSummary(allNews);
        })
        .catch(error => {
            console.error('Error:', error);
            newsList.innerHTML = '<p class="text-danger text-center py-4">Error loading news. Please try again.</p>';
        });
    }

    function fetchNews(category) {
     
        document.querySelectorAll('.btn-toolbar .btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        if (event && event.target) {
            event.target.closest('button').classList.add('active');
        }
        loadCachedNews(category);
    }

    function fetchNewsLive(category) {
        const newsList = document.getElementById('newsList');
        newsList.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Fetching news...</p>
            </div>
        `;

        // Map category to query
        const queries = {
            'logistics': 'logistics shipping supply chain',
            'economy': 'economy trade business',
            'trade': 'trade import export tariffs'
        };

        const query = queries[category] || 'logistics shipping economy';

        fetch('/api/news/fetch', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: query, category: category })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allNews = data.data || [];

             
                if (allNews.length > 0) {
                    localStorage.setItem(`newsLiveFetchCooldown_${category}`, String(Date.now()));
                    renderNews(allNews);
                    updateSentimentSummary(allNews);
                    showFetchNotice(data.from_cache, data.message);
                } else {
                    newsList.innerHTML = '<p class="text-muted text-center py-4">Belum ada berita untuk kategori ini saat ini. Coba lagi nanti.</p>';
                }
            } else {
                newsList.innerHTML = '<p class="text-danger text-center py-4">Failed to fetch news. Please try again.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            newsList.innerHTML = '<p class="text-danger text-center py-4">Error loading news. Please try again.</p>';
        });
    }

    function showFetchNotice(fromCache, message) {
     
        const existing = document.getElementById('fetchNotice');
        if (existing) existing.remove();
    }

    function renderNews(news) {
        const container = document.getElementById('newsList');
        const filter = document.getElementById('sentimentFilter').value;

        let filteredNews = news;
        if (filter !== 'all') {
            filteredNews = news.filter(n => n.sentiment_result === filter);
        }

        if (filteredNews.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-4">No news available. Click a category to fetch news.</p>';
            return;
        }

        let html = '';
        filteredNews.forEach((item, index) => {
            const sentiment = item.sentiment_result || 'Neutral';
            const badgeClass = sentiment === 'Positive' ? 'success' : (sentiment === 'Negative' ? 'danger' : 'secondary');
            const sentimentIcon = sentiment === 'Positive' ? '😊' : (sentiment === 'Negative' ? '😞' : '😐');

            html += `
                <div class="list-group-item border-0 px-0 py-3 ${index > 0 ? 'border-top' : ''}" data-sentiment="${sentiment}">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1 me-3">
                            <h6 class="mb-1">
                                <span class="badge bg-${badgeClass} me-2">${sentiment}</span>
                                ${item.title}
                            </h6>
                            <p class="mb-1 small text-muted">${item.description || 'No description available'}</p>
                            <div class="d-flex align-items-center small text-muted">
                                <span><i class="fas fa-building me-1"></i>${item.source || 'Unknown'}</span>
                                <span class="mx-2">•</span>
                                <span><i class="fas fa-clock me-1"></i>${item.published_at ? new Date(item.published_at).toLocaleDateString() : 'N/A'}</span>
                                ${item.country ? `<span class="mx-2">•</span><span><i class="fas fa-flag me-1"></i>${item.country.name || 'N/A'}</span>` : ''}
                            </div>
                        </div>
                        ${item.image_url ? `<img src="${item.image_url}" alt="News image" style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px;">` : ''}
                    </div>
                    ${item.url ? `<a href="${item.url}" target="_blank" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-external-link-alt me-1"></i>Read More</a>` : ''}
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function updateSentimentSummary(news) {
        let positive = 0, neutral = 0, negative = 0;

        news.forEach(item => {
            const sentiment = item.sentiment_result || 'Neutral';
            if (sentiment === 'Positive') positive++;
            else if (sentiment === 'Negative') negative++;
            else neutral++;
        });

        const total = positive + neutral + negative;
        const positivePct = total > 0 ? Math.round((positive / total) * 100) : 0;
        const neutralPct = total > 0 ? Math.round((neutral / total) * 100) : 0;
        const negativePct = total > 0 ? Math.round((negative / total) * 100) : 0;

        document.getElementById('positiveCount').textContent = positive;
        document.getElementById('neutralCount').textContent = neutral;
        document.getElementById('negativeCount').textContent = negative;
        document.getElementById('positivePercent').textContent = positivePct + '%';
        document.getElementById('neutralPercent').textContent = neutralPct + '%';
        document.getElementById('negativePercent').textContent = negativePct + '%';

        // Update progress bars
        document.getElementById('positiveBar').style.width = positivePct + '%';
        document.getElementById('positiveBar').textContent = positivePct + '%';
        document.getElementById('neutralBar').style.width = neutralPct + '%';
        document.getElementById('neutralBar').textContent = neutralPct + '%';
        document.getElementById('negativeBar').style.width = negativePct + '%';
        document.getElementById('negativeBar').textContent = negativePct + '%';

        // Update chart
        updateSentimentChart(positive, neutral, negative);
    }

    function updateSentimentChart(positive, neutral, negative) {
        const ctx = document.getElementById('sentimentChart').getContext('2d');

        if (sentimentChart) {
            sentimentChart.destroy();
        }

        sentimentChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{
                    data: [positive, neutral, negative],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function filterNews(value) {
        renderNews(allNews);
    }

</script>
@endpush
@endsection