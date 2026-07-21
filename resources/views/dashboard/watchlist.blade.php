{{-- Tampilan: watchlist --}}

@extends('layouts.app')

@section('title', 'My Watchlist')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-star me-2"></i>My Watchlist</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCountryModal">
            <i class="fas fa-plus me-1"></i>Add Country
        </button>
        <button class="btn btn-sm btn-outline-primary" onclick="refreshWatchlist()">
            <i class="fas fa-sync me-1"></i>Refresh
        </button>
    </div>
</div>

<div class="modal fade" id="addCountryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-star me-2"></i>Add Country to Watchlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label for="addCountrySelect" class="form-label">Select Country</label>
                <select id="addCountrySelect" class="form-select">
                    <option value="">-- Choose a country --</option>
                    @foreach($allCountries as $c)
                        @unless(in_array($c->id, $watchedIds))
                            <option value="{{ $c->id }}">{{ $c->flag_emoji }} {{ $c->name }}</option>
                        @endunless
                    @endforeach
                </select>
                <div id="addCountryMsg" class="mt-2 small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addToWatchlist()">
                    <i class="fas fa-plus me-1"></i>Add
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        @if(empty($watchlist) || count($watchlist) == 0)
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div style="font-size: 4rem; color: #dee2e6;">
                        <i class="far fa-star"></i>
                    </div>
                    <h4 class="mt-3 text-muted">Your watchlist is empty</h4>
                    <p class="text-muted">Start monitoring countries by clicking the star icon next to any country.</p>
                    <a href="{{ route('countries.index') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-globe me-2"></i>Browse Countries
                    </a>
                </div>
            </div>
        @else
            <div class="row">
                @foreach($watchlist as $item)
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span style="font-size: 2rem;">{{ $item->country->flag_emoji }}</span>
                                    <h5 class="mt-2">{{ $item->country->name }}</h5>
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="removeFromWatchlist({{ $item->country->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">GDP</small>
                                        <p class="mb-0">${{ number_format($item->country->gdp ?? 0) }}</p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Inflation</small>
                                        <p class="mb-0">{{ number_format($item->country->inflation ?? 0, 1) }}%</p>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="text-muted">Currency</small>
                                        <p class="mb-0">{{ $item->country->currency ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Risk Level</small>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $item->country->risk_level === 'Low' ? 'success' : ($item->country->risk_level === 'Medium' ? 'warning' : 'danger') }}">
                                                {{ $item->country->risk_level }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    @php
                                        $score = $item->country->latestRiskScore->total_risk ?? 0;
                                        $color = $score < 30 ? 'bg-success' : ($score < 60 ? 'bg-warning' : 'bg-danger');
                                    @endphp
                                    <div class="progress-bar {{ $color }}" style="width: {{ min($score, 100) }}%;"></div>
                                </div>
                                <small class="text-muted">Risk Score: {{ number_format($score, 1) }}</small>
                            </div>

                            <a href="{{ route('country.dashboard', $item->country->id) }}" class="btn btn-outline-primary w-100 mt-3">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function removeFromWatchlist(countryId) {
        if (!confirm('Remove this country from your watchlist?')) return;

        fetch('/api/watchlist/remove', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ country_id: countryId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            alert('Failed to remove from watchlist. Please try again.');
        });
    }

    function refreshWatchlist() {
        location.reload();
    }

    function addToWatchlist() {
        const select = document.getElementById('addCountrySelect');
        const countryId = select.value;
        const msg = document.getElementById('addCountryMsg');

        if (!countryId) {
            msg.innerHTML = '<span class="text-danger">Please select a country first.</span>';
            return;
        }

        fetch('/api/watchlist/toggle/' + countryId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                msg.innerHTML = '<span class="text-danger">Failed to add country.</span>';
            }
        })
        .catch(error => {
            msg.innerHTML = '<span class="text-danger">Something went wrong. Please try again.</span>';
        });
    }
</script>
@endpush
@endsection
