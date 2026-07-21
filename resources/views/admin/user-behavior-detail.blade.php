{{-- Tampilan: user behavior detail --}}
@extends('layouts.app')

@section('title', 'User Behavior - ' . $user->name)

{{-- Bagian: content --}}
@section('content')
<nav aria-label="breadcrumb" class="mb-2 d-flex align-items-center justify-content-between">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.analytics.behavior') }}">User Behavior</a></li>
        <li class="breadcrumb-item active">{{ $user->name }}</li>
    </ol>
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
    </a>
</nav>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-1 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user me-2"></i>{{ $user->name }}</h1>
    <a href="{{ route('admin.analytics.behavior') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Kembali
    </a>
</div>

<p class="text-muted mb-4">{{ $user->email }} &middot; Bergabung {{ $user->created_at->format('d M Y') }}</p>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-globe-asia me-2"></i>Negara Favorit</h5>
            </div>
            <div class="card-body">
                @if($favoriteCountries->isEmpty())
                    <p class="text-muted small mb-0">User ini belum pernah membuka detail negara.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($favoriteCountries as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>
                                @if($row['country']->flag_url)
                                    <img src="{{ $row['country']->flag_url }}" width="20" class="me-2" alt="">
                                @endif
                                {{ $row['country']->name }}
                            </span>
                            <span class="badge bg-primary rounded-pill">{{ $row['views'] }}x</span>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Aktivitas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th>Waktu</th>
                                <th>Aksi</th>
                                <th>Detail</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td class="small text-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td class="small"><span class="badge bg-light text-dark border">{{ $log->action }}</span></td>
                                <td class="small text-muted">{{ $log->metadata['name'] ?? $log->metadata['title'] ?? '' }}</td>
                                <td class="small text-muted">{{ $log->ip_address }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada aktivitas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
            <div class="card-footer bg-white">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
