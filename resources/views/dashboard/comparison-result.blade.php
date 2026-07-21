{{-- Tampilan: comparison result --}}

@extends('layouts.app')

@section('title', 'Comparison Result')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-arrows-left-right me-2"></i>Comparison Result</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('comparison') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Compare
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-5 text-center">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div style="font-size: 3rem;">{{ $country1->flag_emoji }}</div>
                <h3>{{ $country1->name }}</h3>
                <span class="badge bg-{{ $country1->risk_level === 'Low' ? 'success' : ($country1->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                    Risk: {{ $country1->risk_level }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-2 d-flex align-items-center justify-content-center">
        <div class="text-center">
            <div class="display-4 text-primary">VS</div>
        </div>
    </div>
    <div class="col-md-5 text-center">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div style="font-size: 3rem;">{{ $country2->flag_emoji }}</div>
                <h3>{{ $country2->name }}</h3>
                <span class="badge bg-{{ $country2->risk_level === 'Low' ? 'success' : ($country2->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                    Risk: {{ $country2->risk_level }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Detailed Comparison</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;">Metric</th>
                        <th style="width: 35%;" class="text-center">{{ $country1->name }}</th>
                        <th style="width: 10%;" class="text-center">Result</th>
                        <th style="width: 35%;" class="text-center">{{ $country2->name }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>GDP (USD)</strong></td>
                        <td class="text-center">${{ number_format($country1->gdp ?? 0) }}</td>
                        <td class="text-center">
                            @php
                                $gdp1 = $country1->gdp ?? 0;
                                $gdp2 = $country2->gdp ?? 0;
                                $winner = $gdp1 > $gdp2 ? $country1->name : ($gdp2 > $gdp1 ? $country2->name : 'Tie');
                            @endphp
                            <span class="badge bg-primary">{{ $winner }}</span>
                        </td>
                        <td class="text-center">${{ number_format($country2->gdp ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Inflation (%)</strong></td>
                        <td class="text-center">{{ number_format($country1->inflation ?? 0, 1) }}%</td>
                        <td class="text-center">
                            @php
                                $inf1 = $country1->inflation ?? 100;
                                $inf2 = $country2->inflation ?? 100;
                                $winner = $inf1 < $inf2 ? $country1->name : ($inf2 < $inf1 ? $country2->name : 'Tie');
                            @endphp
                            <span class="badge bg-success">{{ $winner }}</span>
                        </td>
                        <td class="text-center">{{ number_format($country2->inflation ?? 0, 1) }}%</td>
                    </tr>
                    <tr>
                        <td><strong>Population</strong></td>
                        <td class="text-center">{{ number_format($country1->population ?? 0) }}</td>
                        <td class="text-center">
                            @php
                                $pop1 = $country1->population ?? 0;
                                $pop2 = $country2->population ?? 0;
                                $winner = $pop1 > $pop2 ? $country1->name : ($pop2 > $pop1 ? $country2->name : 'Tie');
                            @endphp
                            <span class="badge bg-info">{{ $winner }}</span>
                        </td>
                        <td class="text-center">{{ number_format($country2->population ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Currency</strong></td>
                        <td class="text-center">{{ $country1->currency ?? 'N/A' }} ({{ $country1->currency_symbol ?? '' }})</td>
                        <td class="text-center">-</td>
                        <td class="text-center">{{ $country2->currency ?? 'N/A' }} ({{ $country2->currency_symbol ?? '' }})</td>
                    </tr>
                    <tr>
                        <td><strong>Risk Score</strong></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $country1->risk_level === 'Low' ? 'success' : ($country1->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                                {{ $country1->latestRiskScore ? number_format($country1->latestRiskScore->total_risk, 1) : 'N/A' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $risk1 = $country1->latestRiskScore->total_risk ?? 100;
                                $risk2 = $country2->latestRiskScore->total_risk ?? 0;
                                $winner = $risk1 < $risk2 ? $country1->name : ($risk2 < $risk1 ? $country2->name : 'Tie');
                            @endphp
                            <span class="badge bg-success">{{ $winner }}</span>
                            <small class="d-block text-muted">(Lower is better)</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $country2->risk_level === 'Low' ? 'success' : ($country2->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                                {{ $country2->latestRiskScore ? number_format($country2->latestRiskScore->total_risk, 1) : 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Risk Level</strong></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $country1->risk_level === 'Low' ? 'success' : ($country1->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                                {{ $country1->risk_level }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $levelOrder = ['Low' => 1, 'Medium' => 2, 'High' => 3];
                                $l1 = $levelOrder[$country1->risk_level] ?? 0;
                                $l2 = $levelOrder[$country2->risk_level] ?? 0;
                                $winner = $l1 < $l2 ? $country1->name : ($l2 < $l1 ? $country2->name : 'Tie');
                            @endphp
                            <span class="badge bg-success">{{ $winner }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $country2->risk_level === 'Low' ? 'success' : ($country2->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                                {{ $country2->risk_level }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Visual Comparison</h5>
    </div>
    <div class="card-body">
        <canvas id="comparisonChart" height="200"></canvas>
    </div>
</div>

@push('scripts')
<script>
    // Comparison Chart
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['GDP', 'Inflation', 'Population', 'Risk Score', 'Currency Stability'],
            datasets: [
                {
                    label: '{{ $country1->name }}',
                    data: [
                        {{ min(($country1->gdp ?? 0) / 10000000000, 10) }},
                        {{ max(0, 10 - ($country1->inflation ?? 0) / 2) }},
                        {{ min(($country1->population ?? 0) / 10000000, 10) }},
                        {{ max(0, 10 - ($country1->latestRiskScore->total_risk ?? 0) / 10) }},
                        {{ $country1->currency ? 7 : 3 }}
                    ],
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgba(13, 110, 253, 1)'
                },
                {
                    label: '{{ $country2->name }}',
                    data: [
                        {{ min(($country2->gdp ?? 0) / 10000000000, 10) }},
                        {{ max(0, 10 - ($country2->inflation ?? 0) / 2) }},
                        {{ min(($country2->population ?? 0) / 10000000, 10) }},
                        {{ max(0, 10 - ($country2->latestRiskScore->total_risk ?? 0) / 10) }},
                        {{ $country2->currency ? 7 : 3 }}
                    ],
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    borderColor: 'rgba(220, 53, 69, 1)'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
