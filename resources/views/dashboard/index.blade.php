{{-- Tampilan: index --}}
@extends('layouts.app')

@section('title', 'Dashboard')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-pie me-2"></i>Global Supply Chain Risk Dashboard</h1>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Countries</h6>
                        <h2 class="mb-0">{{ $riskSummary['total'] }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-globe text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">High Risk Countries</h6>
                        <h2 class="mb-0 text-danger">{{ $riskSummary['high'] }}</h2>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="fas fa-triangle-exclamation text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Active Ports</h6>
                        <h2 class="mb-0">{{ $activePorts }} <small class="text-muted fs-6">/ {{ $totalPorts }}</small></h2>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="fas fa-ship text-info fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Avg Risk Score</h6>
                        <h2 class="mb-0">{{ number_format($riskSummary['average'], 1) }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="fas fa-gauge-high text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shield-halved me-2"></i>Risk Overview</h5>
                <a href="{{ route('analytics.index') }}" class="small">Details &rarr;</a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span><span class="badge bg-success">&nbsp;</span> Low</span>
                    <strong>{{ $riskSummary['low'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span><span class="badge bg-warning">&nbsp;</span> Medium</span>
                    <strong>{{ $riskSummary['medium'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span><span class="badge bg-danger">&nbsp;</span> High</span>
                    <strong>{{ $riskSummary['high'] }}</strong>
                </div>
                <hr>
                <p class="small text-muted mb-1">Top high-risk countries</p>
                @forelse($highRiskCountries as $c)
                    <a href="{{ route('country.dashboard', $c->id) }}" class="d-flex justify-content-between text-decoration-none text-dark border-bottom py-1">
                        <span>{{ $c->flag_emoji }} {{ $c->name }}</span>
                        <span class="badge bg-danger">{{ number_format($c->latestRiskScore->total_risk ?? 0, 1) }}</span>
                    </a>
                @empty
                    <p class="text-muted small mb-0">No high risk countries right now.</p>
                @endforelse
            </div>
        </div>
    </div>

    
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-money-bill-trend-up me-2"></i>Currency Overview</h5>
                <a href="{{ route('currency') }}" class="small">Details &rarr;</a>
            </div>
            <div class="card-body">
                @forelse($currencyOverview as $cur)
                    <div class="d-flex justify-content-between border-bottom py-1">
                        <span>{{ $cur['currency'] }} <span class="text-muted small">({{ $cur['country'] }})</span></span>
                        <span>
                            @if($cur['currency'] === ($latestRates['base'] ?? 'USD'))
                                {{ number_format(1, 4) }}
                            @elseif(isset($latestRates['rates'][$cur['currency']]))
                                {{ number_format($latestRates['rates'][$cur['currency']], 4) }}
                            @else
                                N/A
                            @endif
                        </span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No currency data available.</p>
                @endforelse
            </div>
        </div>
    </div>

    
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-cloud-sun me-2"></i>Weather Summary</h5>
                <a href="{{ route('weather') }}" class="small">Details &rarr;</a>
            </div>
            <div class="card-body">
                <p class="text-muted small">Live monitoring for storms, heavy rain, extreme heat and wind speed across every tracked country.</p>
                <a href="{{ route('weather') }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="fas fa-map me-1"></i>Open Weather Map
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-lg-7 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Latest News</h5>
                <a href="{{ route('news.index') }}" class="small">View All &rarr;</a>
            </div>
            <div class="card-body">
                @forelse($latestNews as $item)
                    <div class="border-bottom py-2">
                        <div class="d-flex justify-content-between">
                            <strong class="small">{{ Str::limit($item->title, 70) }}</strong>
                            @php
                                $s = $item->sentiment_result ?? 'Neutral';
                                $badge = $s === 'Positive' ? 'success' : ($s === 'Negative' ? 'danger' : 'secondary');
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $s }}</span>
                        </div>
                        <span class="text-muted small">{{ $item->country->name ?? '' }} &middot; {{ optional($item->published_at)->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No news yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    
    <div class="col-lg-5 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>Your Recent Activity</h5>
            </div>
            <div class="card-body">
                @forelse($recentActivities as $activity)
                    <div class="border-bottom py-2 small">
                        {{ ucfirst(str_replace('.', ' ', $activity->action)) }}
                        <span class="text-muted d-block">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No recent activity recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
