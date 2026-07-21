{{-- Tampilan: index --}}
@extends('layouts.app')

@section('title', 'Analytics')

{{-- Bagian: content --}}
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Analytics</li>
    </ol>
</nav>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-1 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-line me-2"></i>Analytics</h1>
</div>

<ul class="nav nav-tabs mb-3" id="analyticsTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-risk" type="button">Risk Trend</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-economic" type="button">GDP &amp; Inflation</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-currency" type="button">Currency Trend</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-historical" type="button">Historical Analysis</button></li>
</ul>

<div class="tab-content">
    
    <div class="tab-pane fade show active" id="tab-risk">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Top 10 Highest Risk Countries</h5>
            </div>
            <div class="card-body">
                <canvas id="riskTrendChart" height="90"></canvas>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Risk Level Distribution</h5>
            </div>
            <div class="card-body">
                <div class="mx-auto" style="max-width: 280px; height: 280px;">
                    <canvas id="riskDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    
    <div class="tab-pane fade" id="tab-economic">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Pick a country to see its GDP and inflation trend (data from World Bank).
                    For the full economic breakdown, open the country's detail page from
                    <a href="{{ route('countries.index') }}">Countries</a>.
                </p>
                <select id="econCountrySelect" class="form-select mb-3" style="max-width:320px;"></select>
                <canvas id="gdpInflationChart" height="90"></canvas>
            </div>
        </div>
    </div>

    
    <div class="tab-pane fade" id="tab-currency">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-trend-up me-2"></i>Currency Snapshot vs USD</h5>
            </div>
            <div class="card-body">
                <canvas id="currencySnapshotChart" height="90"></canvas>
                <p class="text-muted small mt-3 mb-0">For full exchange-rate tools and risk-adjusted currency view, see <a href="{{ route('currency') }}">Currency</a>.</p>
            </div>
        </div>
    </div>

    
    <div class="tab-pane fade" id="tab-historical">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Historical Analysis — Top Risk Countries</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Country</th>
                                <th>Risk Score</th>
                                <th>Risk Level</th>
                                <th>GDP (USD)</th>
                                <th>Inflation (%)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topRiskCountries as $c)
                            <tr>
                                <td>{{ $c->flag_emoji }} {{ $c->name }}</td>
                                <td>{{ number_format($c->latestRiskScore->total_risk ?? 0, 1) }}</td>
                                <td><span class="badge bg-{{ $c->risk_level === 'Low' ? 'success' : ($c->risk_level === 'Medium' ? 'warning' : 'danger') }}">{{ $c->risk_level }}</span></td>
                                <td>${{ number_format($c->gdp ?? 0) }}</td>
                                <td>{{ number_format($c->inflation ?? 0, 1) }}%</td>
                                <td><a href="{{ route('country.dashboard', $c->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    // ----- Risk Trend (top 10) -----
    const topRisk = @json($topRiskCountries->map(fn($c) => [
        'name' => $c->name,
        'risk' => $c->latestRiskScore->total_risk ?? 0,
    ]));

    new Chart(document.getElementById('riskTrendChart'), {
        type: 'bar',
        data: {
            labels: topRisk.map(c => c.name),
            datasets: [{ label: 'Risk Score', data: topRisk.map(c => c.risk), backgroundColor: '#dc3545' }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // ----- Risk Distribution -----
    const riskSummary = @json($riskSummary);
    new Chart(document.getElementById('riskDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: ['Low', 'Medium', 'High'],
            datasets: [{ data: [riskSummary.low, riskSummary.medium, riskSummary.high], backgroundColor: ['#198754', '#ffc107', '#dc3545'] }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // ----- GDP & Inflation (uses existing /api/countries + /api/countries/{id}/economic-history) -----
    let gdpInflationChart = null;
    try {
        const res = await fetch('/api/countries');
        const countries = await res.json();
        const select = document.getElementById('econCountrySelect');
        select.innerHTML = countries.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

        async function loadEconomic(id) {
            const histRes = await fetch(`/api/countries/${id}/economic-history`);
            const hist = await histRes.json();
            const ctx = document.getElementById('gdpInflationChart');
            if (gdpInflationChart) gdpInflationChart.destroy();
            gdpInflationChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: (hist.gdp || []).map(p => p.year ?? p.date ?? ''),
                    datasets: [
                        { label: 'GDP', data: (hist.gdp || []).map(p => p.value), borderColor: '#0d6efd', yAxisID: 'y' },
                        { label: 'Inflation (%)', data: (hist.inflation || []).map(p => p.value), borderColor: '#fd7e14', yAxisID: 'y1' },
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { type: 'linear', position: 'left' },
                        y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false } },
                    }
                }
            });
        }

        if (countries.length) {
            select.value = countries[0].id;
            loadEconomic(countries[0].id);
        }
        select.addEventListener('change', () => loadEconomic(select.value));
    } catch (e) {
        console.error('Failed to load economic analytics', e);
    }

    // ----- Currency Snapshot -----
    try {
        const curRes = await fetch('/api/currency/rates');
        const data = await curRes.json();
        // Endpoint mengembalikan { base, rates: {...}, date }.
        // Yang perlu di-plot adalah isi dari `rates`, bukan objek top-level-nya.
        const entries = Object.entries(data.rates || {}).slice(0, 15);
        new Chart(document.getElementById('currencySnapshotChart'), {
            type: 'bar',
            data: {
                labels: entries.map(([code]) => code),
                datasets: [{ label: `Rate vs ${data.base || 'USD'}`, data: entries.map(([, v]) => v), backgroundColor: '#0dcaf0' }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    } catch (e) {
        console.error('Failed to load currency analytics', e);
    }
});
</script>
@endpush
@endsection
