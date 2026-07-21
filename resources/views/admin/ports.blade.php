{{-- Tampilan: ports --}}
@extends('layouts.app')

@section('title', 'Manage Ports')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-ship me-2"></i>Manage Ports</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <button class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus me-1"></i>Add Port
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ports ?? [] as $port)
                    <tr>
                        <td>{{ $port->name }}</td>
                        <td>{{ $port->country }}</td>
                        <td>{{ $port->type ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $port->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $port->status ?? 'active' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.ports.edit', $port->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.ports.destroy', $port->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this port? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No ports found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
