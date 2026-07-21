{{-- Tampilan: user behavior --}}
@extends('layouts.app')

@section('title', 'User Behavior Analytics')

{{-- Bagian: content --}}
@section('content')
<nav aria-label="breadcrumb" class="mb-2 d-flex align-items-center justify-content-between">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active">User Behavior</li>
    </ol>
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
    </a>
</nav>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-1 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-pie me-2"></i>User Behavior Analytics</h1>

    <form method="GET" class="d-flex align-items-center">
        <label class="me-2 small text-muted mb-0">Rentang waktu:</label>
        <select name="days" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 hari terakhir</option>
            <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 hari terakhir</option>
            <option value="90" {{ $days == 90 ? 'selected' : '' }}>90 hari terakhir</option>
            <option value="365" {{ $days == 365 ? 'selected' : '' }}>1 tahun terakhir</option>
        </select>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Aktivitas</h6>
                <h3 class="mb-0">{{ number_format($summary['total_actions']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">User Aktif</h6>
                <h3 class="mb-0">{{ number_format($summary['active_users']) }} <span class="fs-6 text-muted">/ {{ $summary['total_users'] }}</span></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total View Negara</h6>
                <h3 class="mb-0">{{ number_format($summary['country_views']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Negara Ter-track</h6>
                <h3 class="mb-0">{{ $topCountries->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-globe-asia me-2"></i>Negara Paling Sering Dilihat</h5>
            </div>
            <div class="card-body">
                @if($topCountries->isEmpty())
                    <p class="text-muted small mb-0">Belum ada data kunjungan negara pada rentang waktu ini.</p>
                @else
                    <canvas id="topCountriesChart" height="200" class="mb-3"></canvas>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Negara</th>
                                    <th class="text-end">Total View</th>
                                    <th class="text-end">User Unik</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCountries as $row)
                                <tr>
                                    <td>
                                        @if($row['country']->flag_url)
                                            <img src="{{ $row['country']->flag_url }}" width="20" class="me-2" alt="">
                                        @endif
                                        <a href="{{ route('country.dashboard', $row['country']->id) }}">{{ $row['country']->name }}</a>
                                    </td>
                                    <td class="text-end">{{ $row['views'] }}</td>
                                    <td class="text-end">{{ $row['unique_viewers'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-user-clock me-2"></i>User Paling Aktif</h5>
            </div>
            <div class="card-body">
                @if($topUsers->isEmpty())
                    <p class="text-muted small mb-0">Belum ada aktivitas user pada rentang waktu ini.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>User</th>
                                    <th class="text-end">Total Aktivitas</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUsers as $row)
                                <tr>
                                    <td>
                                        {{ $row->user->name ?? 'User #' . $row->user_id }}
                                        <div class="text-muted small">{{ $row->user->email ?? '' }}</div>
                                    </td>
                                    <td class="text-end">{{ $row->total_actions }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.analytics.behavior.user', $row->user_id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Jenis Aktivitas</h5>
            </div>
            <div class="card-body">
                @if($actionBreakdown->isEmpty())
                    <p class="text-muted small mb-0">Belum ada data.</p>
                @else
                    <div class="mx-auto" style="max-width: 280px; height: 280px;">
                        <canvas id="actionBreakdownChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>

    
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Tren Aktivitas Harian</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyTrendChart" height="140"></canvas>
            </div>
        </div>
    </div>

    
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Aktivitas Terbaru</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Detail</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogs as $log)
                            <tr>
                                <td class="small text-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td class="small">{{ $log->user->name ?? 'Guest' }}</td>
                                <td class="small"><span class="badge bg-light text-dark border">{{ $log->action }}</span></td>
                                <td class="small text-muted">{{ $log->metadata['name'] ?? $log->metadata['title'] ?? '' }}</td>
                                <td class="small text-muted">{{ $log->ip_address }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada aktivitas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if($topCountries->isNotEmpty())
new Chart(document.getElementById('topCountriesChart'), {
    type: 'bar',
    data: {
        labels: @json($topCountries->pluck('country.name')),
        datasets: [{
            label: 'Views',
            data: @json($topCountries->pluck('views')),
            backgroundColor: '#0d6efd',
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
@endif

@if($actionBreakdown->isNotEmpty())
new Chart(document.getElementById('actionBreakdownChart'), {
    type: 'doughnut',
    data: {
        labels: @json($actionBreakdown->pluck('action')),
        datasets: [{
            data: @json($actionBreakdown->pluck('total')),
            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#0dcaf0', '#6c757d'],
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
});
@endif

new Chart(document.getElementById('dailyTrendChart'), {
    type: 'line',
    data: {
        labels: @json(collect($dailyTrend)->pluck('date')),
        datasets: [{
            label: 'Aktivitas',
            data: @json(collect($dailyTrend)->pluck('total')),
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220,53,69,0.1)',
            fill: true,
            tension: 0.3,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
</script>
@endpush
