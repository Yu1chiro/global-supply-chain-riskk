{{-- Tampilan: sentiment words --}}
@extends('layouts.app')

@section('title', 'Sentiment Words')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-smile me-2"></i>Sentiment Words Management</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-success"><i class="fas fa-plus-circle me-2"></i>Positive Words</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sentiment.words.store') }}" method="POST" class="row g-2 mb-3">
                    @csrf
                    <input type="hidden" name="type" value="positive">
                    <div class="col-7">
                        <input type="text" name="word" class="form-control form-control-sm" placeholder="Kata baru (mis. thriving)" required>
                    </div>
                    <div class="col-3">
                        <input type="number" name="weight" class="form-control form-control-sm" placeholder="Bobot" min="1" max="10" value="1">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-success w-100"><i class="fas fa-plus"></i></button>
                    </div>
                </form>

                <ul class="list-group">
                    @forelse($positiveWords as $word)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $word->word }}
                        <span>
                            <span class="badge bg-success-subtle text-success me-2">w{{ $word->weight }}</span>
                            <form action="{{ route('admin.sentiment.words.destroy', ['type' => 'positive', 'id' => $word->id]) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus kata \'{{ $word->word }}\' ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">No positive words found</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-danger"><i class="fas fa-minus-circle me-2"></i>Negative Words</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sentiment.words.store') }}" method="POST" class="row g-2 mb-3">
                    @csrf
                    <input type="hidden" name="type" value="negative">
                    <div class="col-7">
                        <input type="text" name="word" class="form-control form-control-sm" placeholder="Kata baru (mis. shortage)" required>
                    </div>
                    <div class="col-3">
                        <input type="number" name="weight" class="form-control form-control-sm" placeholder="Bobot" min="1" max="10" value="1">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-sm btn-danger w-100"><i class="fas fa-plus"></i></button>
                    </div>
                </form>

                <ul class="list-group">
                    @forelse($negativeWords as $word)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $word->word }}
                        <span>
                            <span class="badge bg-danger-subtle text-danger me-2">w{{ $word->weight }}</span>
                            <form action="{{ route('admin.sentiment.words.destroy', ['type' => 'negative', 'id' => $word->id]) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus kata \'{{ $word->word }}\' ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted">No negative words found</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
