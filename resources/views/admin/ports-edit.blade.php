{{-- Tampilan: ports edit --}}
@extends('layouts.app')

@section('title', 'Edit Port')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-edit me-2"></i>Edit Port</h1>
    <a href="{{ route('admin.ports.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="text-muted">Edit port: {{ $port->name ?? 'N/A' }}</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.ports.update', $port->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $port->name) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="{{ old('country', $port->country) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Latitude</label>
                <input type="number" step="any" name="latitude" class="form-control" value="{{ old('latitude', $port->latitude) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Longitude</label>
                <input type="number" step="any" name="longitude" class="form-control" value="{{ old('longitude', $port->longitude) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach (['active', 'inactive', 'maintenance'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $port->status) === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
