{{-- Tampilan: articles edit --}}
@extends('layouts.app')

@section('title', 'Edit Article')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-edit me-2"></i>Edit Article</h1>
    <a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.articles.update', $article->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.partials.article-form', ['article' => $article])

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Update Article
            </button>
        </form>
    </div>
</div>
@endsection
