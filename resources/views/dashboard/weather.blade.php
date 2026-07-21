{{-- Tampilan: weather --}}

@extends('layouts.app')

@section('title', 'Weather Monitoring')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-cloud-sun me-2"></i>Global Weather Monitoring</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-primary" onclick="refreshWeather()">
            <i class="fas fa-sync me-1"></i>Refresh Weather
        </button>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Countries Monitored</h6>
                <h2 class="mb-0">{{ $countries->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Storm Risk</h6>
                <h2 class="mb-0 text-danger">
                    {{ $countries->filter(function($c) { return ($c->weather_data['current_weather']['windspeed'] ?? 0) > 40; })->count() }}
                </h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Heavy Rain</h6>
                <h2 class="mb-0 text-primary">
                    {{ $countries->filter(function($c) { return max($c->weather_data['daily']['precipitation_sum'] ?? [0]) > 25; })->count() }}
                </h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Extreme Heat</h6>
                <h2 class="mb-0 text-warning">
                    {{ $countries->filter(function($c) { return ($c->weather_data['current_weather']['temperature'] ?? 0) > 35; })->count() }}
                </h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Freezing</h6>
                <h2 class="mb-0 text-info">
                    {{ $countries->filter(function($c) { return ($c->weather_data['current_weather']['temperature'] ?? 0) < 0; })->count() }}
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-map me-2"></i>World Weather Map</h5>
    </div>
    <div class="card-body">
        <div id="weatherMap" style="height: 500px;"></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Weather Details by Country</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Temperature</th>
                        <th>Wind Speed</th>
                        <th>Rain (mm)</th>
                        <th>Weather</th>
                        <th>Storm Risk</th>
                        <th>Risk Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($countries as $country)
                    <tr>
                        <td>
                            <span class="me-2">{{ $country->flag_emoji }}</span>
                            {{ $country->name }}
                        </td>
                        <td>
                            {{ isset($country->weather_data['current_weather']['temperature']) ? $country->weather_data['current_weather']['temperature'] . '°C' : 'N/A' }}
                        </td>
                        <td>
                            {{ isset($country->weather_data['current_weather']['windspeed']) ? $country->weather_data['current_weather']['windspeed'] . ' km/h' : 'N/A' }}
                        </td>
                        <td>
                            @php
                                $rainMm = max($country->weather_data['daily']['precipitation_sum'] ?? [null]);
                            @endphp
                            {{ $rainMm !== null ? $rainMm . ' mm' : 'N/A' }}
                        </td>
                        <td>
                            @php
                                $temp = $country->weather_data['current_weather']['temperature'] ?? 0;
                                $wind = $country->weather_data['current_weather']['windspeed'] ?? 0;
                                $rain = max($country->weather_data['daily']['precipitation_sum'] ?? [0]);
                                $icon = $wind > 40 ? '🌊' : ($rain > 25 ? '🌧️' : ($temp > 35 ? '☀️' : ($temp < 0 ? '❄️' : '☁️')));
                            @endphp
                            {{ $icon }}
                        </td>
                        <td>
                            @php
                                $wind = $country->weather_data['current_weather']['windspeed'] ?? 0;
                                $class = $wind > 60 ? 'danger' : ($wind > 40 ? 'warning' : 'success');
                                $label = $wind > 60 ? 'Extreme' : ($wind > 40 ? 'High' : 'Low');
                            @endphp
                            <span class="badge bg-{{ $class }}">{{ $label }}</span>
                        </td>
                        <td>
                            @if($country->latestRiskScore)
                                {{ number_format($country->latestRiskScore->total_risk, 1) }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize Weather Map
    const map = L.map('weatherMap').setView([20, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Add country markers
    @foreach($countries as $country)
        @if($country->latitude && $country->longitude)
            @php
                $hasWeather = isset($country->weather_data['current_weather']);
                $temp = $country->weather_data['current_weather']['temperature'] ?? null;
                $wind = $country->weather_data['current_weather']['windspeed'] ?? null;
                $rain = $hasWeather ? max($country->weather_data['daily']['precipitation_sum'] ?? [0]) : null;

                if (!$hasWeather) {
                    
                    $color = 'gray';
                    $icon = '❓';
                } else {
                    
                    
                    if ($wind > 60 || $rain > 50) {
                        $color = 'red';
                    } elseif ($wind > 40 || $rain > 25) {
                        $color = 'orange';
                    } elseif ($rain > 10) {
                        $color = 'blue';
                    } else {
                        $color = 'green';
                    }

                    
                    if ($wind > 40) {
                        $icon = '🌊';
                    } elseif ($rain > 25) {
                        $icon = '🌧️';
                    } elseif ($temp > 35) {
                        $icon = '☀️';
                    } elseif ($temp < 0) {
                        $icon = '❄️';
                    } else {
                        $icon = '☁️';
                    }
                }
            @endphp
            const marker{{ $country->id }} = L.circleMarker([{{ $country->latitude }}, {{ $country->longitude }}], {
                radius: 10,
                fillColor: '{{ $color }}',
                color: '{{ $color }}',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.7
            }).addTo(map);

            marker{{ $country->id }}.bindPopup(`
                <div style="text-align: center;">
                    <div style="font-size: 2rem;">{{ $icon }}</div>
                    <strong>{{ $country->name }}</strong><br>
                    Temp: {{ $hasWeather ? $temp . '°C' : 'No data' }}<br>
                    Wind: {{ $hasWeather ? $wind . ' km/h' : 'No data' }}<br>
                    Rain: {{ $hasWeather ? $rain . ' mm' : 'No data' }}<br>
                    Risk: {{ $country->latestRiskScore ? number_format($country->latestRiskScore->total_risk, 1) : 'N/A' }}
                </div>
            `);
        @endif
    @endforeach

    function refreshWeather() {
        location.reload();
    }

    // Auto refresh every 5 minutes
    setInterval(() => {
        location.reload();
    }, 300000);
</script>
@endpush
@endsection
