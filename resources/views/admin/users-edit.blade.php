{{-- Tampilan: users edit --}}
@extends('layouts.app')

@section('title', 'Edit User')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-edit me-2"></i>Edit User</h1>
    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="mb-3 form-check">
                <input type="hidden" name="is_admin" value="0">
                <input type="checkbox" name="is_admin" id="is_admin" class="form-check-input" value="1"
                       {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                       {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                <label class="form-check-label" for="is_admin">Admin</label>
                @if ($user->id === auth()->id())
                    <div class="form-text text-warning">Anda tidak bisa mengubah status admin akun sendiri.</div>
                @endif
            </div>

            <hr>
            <p class="text-muted small">Kosongkan password jika tidak ingin mengubahnya.</p>

            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
