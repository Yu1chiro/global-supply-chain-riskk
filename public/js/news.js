

class NewsManager {
    constructor() {
        this.currentCategory = 'general';
        this.newsContainer = document.getElementById('latest-news');
        this.sentimentContainer = document.getElementById('sentiment-stats');
        this.tabs = document.querySelectorAll('.news-tab');

        this.init();
    }

    init() {
        
        this.tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const category = tab.dataset.category || 'general';
                this.currentCategory = category;

                
                this.tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                this.loadNews(category);
            });
        });

        
        this.loadNews('general');
    }

    async loadNews(category = 'general') {
        if (!this.newsContainer) return;

        
        this.newsContainer.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading news...</p>
            </div>
        `;

        try {
            
            let endpoint = '/api/news';
            let params = new URLSearchParams();

            if (category === 'logistics') {
                endpoint = '/api/news/logistics';
            } else if (category === 'economy') {
                endpoint = '/api/news/economy';
            } else if (category === 'trade') {
                endpoint = '/api/news/trade';
            } else {
                endpoint = '/api/news/fetch';
                params.append('query', 'business economy trade logistics shipping');
                params.append('limit', '15');
            }

            const url = params.toString() ? `${endpoint}?${params.toString()}` : endpoint;

            console.log('Fetching news from:', url);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('News response:', result);

            if (result.success) {
                const articles = result.data || result.articles || [];
                this.renderNews(articles);

                if (result.sentiment_summary) {
                    this.updateSentimentStats(result.sentiment_summary);
                } else {
                    this.calculateAndUpdateSentiment(articles);
                }
            } else {
                throw new Error(result.message || 'Failed to load news');
            }

        } catch (error) {
            console.error('Error loading news:', error);
            this.showError(error.message);
        }
    }

    renderNews(articles) {
        if (!this.newsContainer) return;

        if (!articles || articles.length === 0) {
            this.newsContainer.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-newspaper fa-2x mb-2"></i>
                    <p>No news available for this category</p>
                    <button class="btn btn-sm btn-primary" onclick="window.newsManager.loadNews('${this.currentCategory}')">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            `;
            return;
        }

        let html = '';

        articles.forEach((article, index) => {
            const sentiment = article.sentiment_result || article.sentiment || 'neutral';
            const sentimentBadge = this.getSentimentBadge(sentiment);

            const title = article.title || 'No title';
            const description = article.description || '';
            const url = article.url || '#';
            const source = article.source || article.source_name || 'Unknown';
            const imageUrl = article.image_url || article.image || null;
            const publishedAt = article.published_at || article.publishedAt || null;

            html += `
                <div class="news-item mb-3 p-3 border rounded-3 hover-shadow">
                    <div class="d-flex ${imageUrl ? 'align-items-start' : ''}">
                        ${imageUrl ? `
                            <div class="flex-shrink-0 me-3">
                                <img src="${imageUrl}" alt="News"
                                     class="rounded-2"
                                     style="width:120px; height:80px; object-fit:cover;"
                                     onerror="this.style.display='none'">
                            </div>
                        ` : ''}
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="${url}" target="_blank" class="text-decoration-none text-dark fw-semibold hover-primary">
                                    ${this.truncateText(title, 80)}
                                </a>
                            </h6>
                            ${description ? `
                                <p class="mb-1 small text-muted">${this.truncateText(description, 120)}</p>
                            ` : ''}
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <small class="text-muted">
                                    <i class="fas fa-source"></i> ${source}
                                </small>
                                ${sentimentBadge}
                                ${publishedAt ? `
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i> ${this.formatDate(publishedAt)}
                                    </small>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
            <div class="text-center mt-3">
                <button class="btn btn-sm btn-outline-primary" onclick="window.newsManager.loadNews('${this.currentCategory}')">
                    <i class="fas fa-sync"></i> Refresh News
                </button>
            </div>
        `;

        this.newsContainer.innerHTML = html;
    }

    getSentimentBadge(sentiment) {
        const sentimentMap = {
            'positive': '<span class="badge bg-success"><i class="fas fa-smile"></i> Positive</span>',
            'negative': '<span class="badge bg-danger"><i class="fas fa-frown"></i> Negative</span>',
            'neutral': '<span class="badge bg-secondary"><i class="fas fa-meh"></i> Neutral</span>'
        };
        return sentimentMap[sentiment.toLowerCase()] || sentimentMap.neutral;
    }

    updateSentimentStats(summary) {
        if (!this.sentimentContainer) return;

        const positive = summary.positive || summary.positive_percent || 0;
        const neutral = summary.neutral || summary.neutral_percent || 0;
        const negative = summary.negative || summary.negative_percent || 0;

        this.sentimentContainer.innerHTML = `
            <div class="row text-center">
                <div class="col-4">
                    <div class="text-success fw-bold">${positive}%</div>
                    <small class="text-muted">Positive</small>
                    <div class="progress mt-1" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: ${positive}%"></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-secondary fw-bold">${neutral}%</div>
                    <small class="text-muted">Neutral</small>
                    <div class="progress mt-1" style="height: 6px;">
                        <div class="progress-bar bg-secondary" style="width: ${neutral}%"></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-danger fw-bold">${negative}%</div>
                    <small class="text-muted">Negative</small>
                    <div class="progress mt-1" style="height: 6px;">
                        <div class="progress-bar bg-danger" style="width: ${negative}%"></div>
                    </div>
                </div>
            </div>
        `;
    }

    calculateAndUpdateSentiment(articles) {
        let positive = 0, neutral = 0, negative = 0;

        articles.forEach(article => {
            const sentiment = (article.sentiment_result || article.sentiment || 'neutral').toLowerCase();
            if (sentiment === 'positive') positive++;
            else if (sentiment === 'negative') negative++;
            else neutral++;
        });

        const total = articles.length || 1;
        const summary = {
            positive: Math.round((positive / total) * 100),
            neutral: Math.round((neutral / total) * 100),
            negative: Math.round((negative / total) * 100)
        };

        this.updateSentimentStats(summary);
    }

    showError(message) {
        if (!this.newsContainer) return;

        this.newsContainer.innerHTML = `
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                    <div class="flex-grow-1">
                        <strong>Error loading news</strong>
                        <p class="mb-0 small text-muted">${message || 'Please try again later.'}</p>
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="window.newsManager.loadNews('${this.currentCategory}')">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                </div>
            </div>
        `;
    }

    truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    formatDate(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch {
            return dateString;
        }
    }
}

window.NewsManager = NewsManager;

