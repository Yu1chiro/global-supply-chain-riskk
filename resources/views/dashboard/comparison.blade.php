{{-- Tampilan: comparison --}}

@extends('layouts.app')

@section('title', 'Compare Countries')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-arrows-left-right me-2"></i>Country Comparison Engine</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Select Countries to Compare</h5>
            </div>
            <div class="card-body">
                <form id="compareForm" action="{{ route('comparison.data') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-5">
                            <label for="country1" class="form-label">Country 1</label>
                            <select name="country1" id="country1" class="form-select" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">
                                        {{ $country->flag_emoji }} {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="fas fa-arrows-left-right fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="country2" class="form-label">Country 2</label>
                            <select name="country2" id="country2" class="form-select" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">
                                        {{ $country->flag_emoji }} {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-compare me-2"></i>Compare Countries
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Popular Comparisons</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border cursor-pointer" onclick="quickCompare('DE', 'AU')">
                            <div class="card-body text-center">
                                <h5>🇩🇪 vs 🇦🇺</h5>
                                <small class="text-muted">Germany vs Australia</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border cursor-pointer" onclick="quickCompare('US', 'CN')">
                            <div class="card-body text-center">
                                <h5>🇺🇸 vs 🇨🇳</h5>
                                <small class="text-muted">USA vs China</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border cursor-pointer" onclick="quickCompare('ID', 'SG')">
                            <div class="card-body text-center">
                                <h5>🇮🇩 vs 🇸🇬</h5>
                                <small class="text-muted">Indonesia vs Singapore</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border cursor-pointer" onclick="quickCompare('JP', 'GB')">
                            <div class="card-body text-center">
                                <h5>🇯🇵 vs 🇬🇧</h5>
                                <small class="text-muted">Japan vs UK</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function quickCompare(code1, code2) {
        // Find country IDs by code
        const countries = @json($countries->mapWithKeys(fn($c) => [$c->code => $c->id]));
        
        if (countries[code1] && countries[code2]) {
            document.getElementById('country1').value = countries[code1];
            document.getElementById('country2').value = countries[code2];
            document.getElementById('compareForm').submit();
        } else {
            alert('Country not found');
        }
    }

    // Styling for clickable cards
    document.querySelectorAll('.card.border').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('mouseenter', function() {
            this.style.borderColor = '#0d6efd';
            this.style.boxShadow = '0 0 15px rgba(13, 110, 253, 0.2)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.borderColor = '#dee2e6';
            this.style.boxShadow = 'none';
        });
    });
</script>
@endpush
@endsection