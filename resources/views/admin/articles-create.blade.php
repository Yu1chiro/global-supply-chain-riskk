{{-- Tampilan: articles create --}}
@extends('layouts.app')

@section('title', 'New Article')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-plus me-2"></i>New Article</h1>
    <a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.articles.store') }}" method="POST">
            @csrf
            @include('admin.partials.article-form')

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Save Article
            </button>
        </form>
    </div>
</div>
@endsection
