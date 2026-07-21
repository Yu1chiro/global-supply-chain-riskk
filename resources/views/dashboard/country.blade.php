{{-- Tampilan: country --}}
@extends('layouts.app')

@section('title', $country->name . ' Dashboard')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <span class="me-2">{{ $country->flag_emoji }}</span>
        {{ $country->name }} Risk Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-primary" onclick="calculateRisk()">
            <i class="fas fa-calculator me-1"></i>Calculate Risk
        </button>
        <button class="btn btn-sm btn-outline-success ms-2" onclick="addToWatchlist()">
            <i class="fas fa-star me-1"></i>Add to Watchlist
        </button>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Total Risk Score</h6>
                <h2 class="mb-0">
                    {{ $country->latestRiskScore ? number_format($country->latestRiskScore->total_risk, 1) : 'N/A' }}
                </h2>
                <span class="badge bg-{{ $country->risk_level === 'Low' ? 'success' : ($country->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                    {{ $country->risk_level }}
                </span>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">GDP (USD)</h6>
                <h4 class="mb-0">${{ number_format($country->gdp ?? 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Inflation Rate</h6>
                <h4 class="mb-0">{{ number_format($country->inflation ?? 0, 1) }}%</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Population</h6>
                <h4 class="mb-0">{{ $country->population ? number_format($country->population) : 'N/A' }}</h4>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Currency</h6>
                <h4 class="mb-0">{{ $country->currency ?? 'N/A' }}</h4>
                <small class="text-muted">{{ $country->currency_symbol ?? '' }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Region</h6>
                <h5 class="mb-0">{{ $country->region ?? 'N/A' }}</h5>
                <small class="text-muted">{{ $country->subregion ?? '' }}</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Languages</h6>
                <h6 class="mb-0">{{ $country->languages ? implode(', ', $country->languages) : 'N/A' }}</h6>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Exports (USD)</h6>
                <h5 class="mb-0">${{ $country->exports ? number_format($country->exports) : 'N/A' }}</h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Imports (USD)</h6>
                <h5 class="mb-0">${{ $country->imports ? number_format($country->imports) : 'N/A' }}</h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Devisa / Reserves (USD)</h6>
                <h5 class="mb-0">${{ $country->reserves ? number_format($country->reserves) : 'N/A' }}</h5>
            </div>
        </div>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-2 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Jumlah Provinsi / Negara Bagian</h6>
                        <h2 class="mb-0">{{ $geoSummary['provinces'] }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded align-self-start">
                        <i class="fas fa-map-location-dot text-primary fa-2x"></i>
                    </div>
                </div>
                @if($geoSummary['provinces'] === 0)
                    <small class="text-muted">Data provinsi tidak tersedia untuk negara ini.</small>
                @endif
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Jumlah Kota</h6>
                        <h2 class="mb-0">{{ $geoSummary['cities'] }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded align-self-start">
                        <i class="fas fa-city text-success fa-2x"></i>
                    </div>
                </div>
                @if($geoSummary['cities'] === 0)
                    <small class="text-muted">Data kota tidak tersedia untuk negara ini.</small>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-anchor me-2"></i>Pelabuhan di {{ $country->name }} ({{ $ports->count() }})</h5>
    </div>
    <div class="card-body p-0">
        @if($ports->isEmpty())
            <p class="text-muted mb-0 p-3">Belum ada data pelabuhan untuk negara ini di database.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nama Pelabuhan</th>
                            <th>Kode</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Kemacetan Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ports as $port)
                        <tr>
                            <td>{{ $port->name }}</td>
                            <td>{{ $port->code ?? '-' }}</td>
                            <td>{{ $port->type ?? '-' }}</td>
                            <td><span class="badge bg-{{ $port->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($port->status) }}</span></td>
                            <td>
                                @if($port->latestTrafficLog)
                                    <span class="badge bg-{{ $port->latestTrafficLog->congestion_level === 'low' ? 'success' : ($port->latestTrafficLog->congestion_level === 'moderate' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($port->latestTrafficLog->congestion_level) }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="row">
    
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Risk Trend (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="riskTrendChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Risk Breakdown</h5>
            </div>
            <div class="card-body">
                <canvas id="riskBreakdownChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>GDP Trend (World Bank)</h5>
            </div>
            <div class="card-body">
                @if(count($gdpTrend) > 0)
                    <canvas id="gdpTrendChart" height="220"></canvas>
                @else
                    <p class="text-muted mb-0">GDP history not available from World Bank for this country.</p>
                @endif
            </div>
        </div>
    </div>

    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Inflation Trend (World Bank)</h5>
            </div>
            <div class="card-body">
                @if(count($inflationTrend) > 0)
                    <canvas id="inflationTrendChart" height="220"></canvas>
                @else
                    <p class="text-muted mb-0">Inflation history not available from World Bank for this country.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-cloud-sun me-2"></i>Current Weather</h5>
    </div>
    <div class="card-body">
        @if($country->weather_data)
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4">
                            {{ $country->weather_data['current_weather']['temperature'] ?? 'N/A' }}°C
                        </div>
                        <small class="text-muted">Temperature</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4">
                            {{ $country->weather_data['current_weather']['windspeed'] ?? 'N/A' }} km/h
                        </div>
                        <small class="text-muted">Wind Speed</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4">
                            {{ $country->weather_data['current_weather']['weathercode'] ?? 'N/A' }}
                        </div>
                        <small class="text-muted">Weather Code</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4">
                            @if(($country->weather_data['current_weather']['windspeed'] ?? 0) > 40)
                                ⚠️
                            @else
                                ✅
                            @endif
                        </div>
                        <small class="text-muted">Storm Risk</small>
                    </div>
                </div>
            </div>
        @else
            <p class="text-muted">Weather data not available</p>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Latest News</h5>
    </div>
    <div class="card-body">
        <div class="list-group">
            @forelse($country->newsCache ?? [] as $news)
                <a href="{{ $news->url ?? '#' }}" class="list-group-item list-group-item-action" target="_blank">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">{{ $news->title }}</h6>
                        <small class="text-muted">{{ $news->published_at->diffForHumans() }}</small>
                    </div>
                    <p class="mb-1 small">{{ Str::limit($news->description ?? '', 200) }}</p>
                    <span class="badge bg-{{ $news->sentiment_result === 'Positive' ? 'success' : ($news->sentiment_result === 'Negative' ? 'danger' : 'secondary') }}">
                        {{ $news->sentiment_result }}
                    </span>
                    <small class="text-muted ms-2">{{ $news->source }}</small>
                </a>
            @empty
                <p class="text-muted">No news available for this country</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Risk Trend Chart
    const ctx1 = document.getElementById('riskTrendChart').getContext('2d');
    const riskData = @json($riskTrend);
    
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: riskData.map(d => d.date),
            datasets: [{
                label: 'Risk Score',
                data: riskData.map(d => d.risk),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Risk Breakdown Chart
    const ctx2 = document.getElementById('riskBreakdownChart').getContext('2d');
    const latestRisk = @json($country->latestRiskScore);
    
    if (latestRisk) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Weather', 'Inflation', 'Currency', 'Political'],
                datasets: [{
                    data: [
                        latestRisk.weather_risk || 0,
                        latestRisk.inflation_risk || 0,
                        latestRisk.currency_risk || 0,
                        latestRisk.political_risk || 0
                    ],
                    backgroundColor: ['#0d6efd', '#ffc107', '#198754', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // GDP Trend Chart
    const gdpTrend = @json($gdpTrend);
    if (gdpTrend.length > 0) {
        const ctxGdp = document.getElementById('gdpTrendChart').getContext('2d');
        new Chart(ctxGdp, {
            type: 'line',
            data: {
                labels: gdpTrend.map(d => d.year),
                datasets: [{
                    label: 'GDP (USD)',
                    data: gdpTrend.map(d => d.value),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => 'GDP: $' + Number(ctx.parsed.y).toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => '$' + (value / 1e9).toFixed(0) + 'B'
                        }
                    }
                }
            }
        });
    }

    // Inflation Trend Chart
    const inflationTrend = @json($inflationTrend);
    if (inflationTrend.length > 0) {
        const ctxInflation = document.getElementById('inflationTrendChart').getContext('2d');
        new Chart(ctxInflation, {
            type: 'line',
            data: {
                labels: inflationTrend.map(d => d.year),
                datasets: [{
                    label: 'Inflation (%)',
                    data: inflationTrend.map(d => d.value),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => 'Inflation: ' + Number(ctx.parsed.y).toFixed(2) + '%'
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => value + '%'
                        }
                    }
                }
            }
        });
    }

    function calculateRisk() {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Calculating...';
        
        fetch('/api/risk/calculate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                country_id: {{ $country->id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            location.reload();
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-calculator me-1"></i>Calculate Risk';
            alert('Failed to calculate risk. Please try again.');
        });
    }

    function addToWatchlist() {
        fetch('/api/watchlist/toggle', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                country_id: {{ $country->id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
            }
        })
        .catch(error => {
            alert('Failed to update watchlist. Please try again.');
        });
    }
</script>
@endpush
@endsection