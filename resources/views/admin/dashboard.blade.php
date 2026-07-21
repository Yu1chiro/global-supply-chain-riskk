{{-- Tampilan: dashboard --}}
@extends('layouts.app')

@section('title', 'Admin Dashboard')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <h1 class="h2"><i class="fas fa-user-shield me-2"></i>Admin Panel</h1>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('admin.ports.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-anchor fa-2x text-primary mb-3"></i>
                    <h5 class="card-title mb-1">Ports</h5>
                    <p class="text-muted small mb-0">Kelola data pelabuhan (tambah, edit, hapus)</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="{{ route('admin.articles.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-file-alt fa-2x text-info mb-3"></i>
                    <h5 class="card-title mb-1">Articles</h5>
                    <p class="text-muted small mb-0">Kelola artikel internal yang tampil di halaman News</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="{{ route('admin.users') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-users fa-2x text-warning mb-3"></i>
                    <h5 class="card-title mb-1">Users</h5>
                    <p class="text-muted small mb-0">Lihat daftar user yang terdaftar</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6 col-lg-3">
        <a href="{{ route('admin.analytics.behavior') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-chart-pie fa-2x text-danger mb-3"></i>
                    <h5 class="card-title mb-1">User Behavior</h5>
                    <p class="text-muted small mb-0">Negara favorit, user paling aktif, tren aktivitas</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Ports</h6>
                <h3 class="mb-0">{{ \App\Models\Port::count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Articles</h6>
                <h3 class="mb-0">{{ \App\Models\Article::count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted mb-1">Total Users</h6>
                <h3 class="mb-0">{{ \App\Models\User::count() }}</h3>
            </div>
        </div>
    </div>
</div>
@endsection
