{{-- Tampilan: currency --}}

@extends('layouts.app')

@section('title', 'Currency Impact Dashboard')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-money-bill-trend-up me-2"></i>Currency Impact Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-primary" onclick="refreshRates()">
            <i class="fas fa-sync me-1"></i>Refresh Rates
        </button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Base Currency</h6>
                <h3 class="mb-0">{{ $rates['base'] ?? 'USD' }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Exchange Rate Date</h6>
                <h3 class="mb-0" id="deviceDate">-</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">Currencies Tracked</h6>
                <h3 class="mb-0">{{ count($rates['rates'] ?? []) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Currency Exchange Rates</h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="currencyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Currency Risk</h5>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 300px;">
                    <canvas id="currencyRiskChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Currency Rates by Country</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Currency</th>
                        <th>Symbol</th>
                        <th>Rate (USD)</th>
                        <th>Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currencies as $currency)
                    <tr>
                        <td>{{ $currency['country'] }}</td>
                        <td>{{ $currency['currency'] }}</td>
                        <td>{{ $currency['symbol'] }}</td>
                        <td>
                            {{ $rates['rates'][$currency['currency']] ?? 'N/A' }}
                        </td>
                        <td>
                            @php
                                $rate = $rates['rates'][$currency['currency']] ?? 1;
                                $trend = $rate > 1.5 ? 'Strengthening' : ($rate < 0.5 ? 'Weakening' : 'Stable');
                                $class = $trend === 'Strengthening' ? 'success' : ($trend === 'Weakening' ? 'danger' : 'secondary');
                            @endphp
                            <span class="badge bg-{{ $class }}">{{ $trend }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tampilkan tanggal sesuai device/browser user (bukan tanggal server),
    // supaya selalu sinkron dengan tanggal & zona waktu di perangkat mereka.
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('deviceDate');
        if (el) {
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');
            el.textContent = `${yyyy}-${mm}-${dd}`;
        }
    });

    // Currency Chart
    // SEBELUMNYA: chart ini nge-plot SEMUA 166 currency mentah dari API
    // langsung dengan skala linear. Karena rentang nilainya sangat jauh
    // (mis. IDR/VND belasan ribu per USD, sementara JOD/KWD di bawah 1),
    // currency bernilai kecil jadi rata di 0 dan yang besar bikin satu paku
    // tinggi menjulang, dan karena 166 label kepadatan, Chart.js
    // menyembunyikan sebagian sehingga paku itu kelihatan nempel ke label
    // yang salah.
    //
    // SEKARANG: cuma tampilkan currency milik negara yang benar-benar
    // di-track aplikasi (variabel $currencies dari server), dan pakai
    // skala LOGARITMIK supaya currency kecil (JOD, KWD) dan besar
    // (IDR, VND) sama-sama kebaca jelas di chart yang sama.
    const ctx1 = document.getElementById('currencyChart').getContext('2d');
    const allRates = @json($rates['rates'] ?? []);
    const trackedCurrencies = @json(collect($currencies)->pluck('currency')->unique()->values());

    const chartEntries = trackedCurrencies
        .filter(code => allRates[code] !== undefined)
        .map(code => ({ code, rate: allRates[code] }))
        .sort((a, b) => a.rate - b.rate);

    const labels = chartEntries.map(e => e.code);
    const data = chartEntries.map(e => e.rate);

    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Exchange Rate (USD)',
                data: data,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: '#0d6efd',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `1 USD = ${Number(ctx.parsed.y).toLocaleString()} ${ctx.label}`
                    }
                }
            },
            scales: {
                y: {
                    type: 'logarithmic',
                    title: { display: true, text: 'Rate per 1 USD (log scale)' }
                }
            }
        }
    });

    // Currency Risk Chart
    const ctx2 = document.getElementById('currencyRiskChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Low Risk', 'Medium Risk', 'High Risk'],
            datasets: [{
                data: [60, 25, 15],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
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

    function refreshRates() {
        location.reload();
    }
</script>
@endpush
@endsection