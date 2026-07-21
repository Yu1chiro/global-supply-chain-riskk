{{-- Tampilan: index --}}
@extends('layouts.app')

@section('title', 'Countries')

{{-- Bagian: content --}}
@section('content')
<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Countries</li>
    </ol>
</nav>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-1 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-globe me-2"></i>Countries</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-primary me-2" id="refreshBtn" onclick="refreshData()">
            <i class="fas fa-sync me-1" id="refreshIcon"></i>Refresh Data
        </button>
        <button class="btn btn-sm btn-outline-success" id="syncBtn" onclick="syncCountryData()">
            <i class="fas fa-cloud-arrow-down me-1" id="syncIcon"></i>Sync GDP/Inflasi
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="mb-2"><i class="fas fa-earth-americas me-2"></i>Pantau Negara Lain</h6>
        <p class="text-muted small mb-2">Ketik nama negara mana pun di dunia (bukan cuma yang ada di tabel bawah) untuk mulai memantaunya.</p>
        <div class="position-relative" style="max-width: 420px;">
            <input type="text" id="globalCountrySearch" class="form-control" placeholder="Contoh: Vietnam, Nigeria, Mexico..." autocomplete="off">
            <div id="globalCountryResults" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1000; display:none; max-height: 320px; overflow-y:auto;"></div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Total Countries</h6>
                        <h2 class="mb-0" data-stat="total">{{ $riskSummary['total'] }}</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-globe text-primary fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Low Risk</h6>
                        <h2 class="mb-0 text-success" data-stat="low">{{ $riskSummary['low'] }}</h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Medium Risk</h6>
                        <h2 class="mb-0 text-warning" data-stat="medium">{{ $riskSummary['medium'] }}</h2>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">High Risk</h6>
                        <h2 class="mb-0 text-danger" data-stat="high">{{ $riskSummary['high'] }}</h2>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="fas fa-times-circle text-danger fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-map me-2"></i>Global Risk Map</h5>
    </div>
    <div class="card-body p-0">
        <div id="worldMap"></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Country Risk Overview</h5>
        <div>
            <input type="text" id="searchCountry" class="form-control form-control-sm" placeholder="Search country...">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Currency</th>
                        <th>GDP (USD)</th>
                        <th>Inflation (%)</th>
                        <th>Risk Score</th>
                        <th>Risk Level</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="countriesTableBody">
                    @foreach($countries as $country)
                    <tr data-country-id="{{ $country->id }}">
                        <td>
                            <span class="me-2">{{ $country->flag_emoji }}</span>
                            {{ $country->name }}
                        </td>
                        <td>{{ $country->currency ?? 'N/A' }}</td>
                        <td>${{ number_format($country->gdp ?? 0) }}</td>
                        <td>{{ number_format($country->inflation ?? 0, 1) }}%</td>
                        <td>
                            @if($country->latestRiskScore)
                                {{ number_format($country->latestRiskScore->total_risk, 1) }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @php
                                $level = $country->risk_level;
                                $class = $level === 'Low' ? 'success' : ($level === 'Medium' ? 'warning' : 'danger');
                            @endphp
                            <span class="badge bg-{{ $class }}">{{ $level }}</span>
                        </td>
                        <td>
                            <a href="{{ route('country.dashboard', $country->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" onclick="toggleWatchlist({{ $country->id }})">
                                <i class="far fa-star"></i>
                            </button>
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
    // ============================================
    // Leaflet Map — dibuat reusable supaya markernya bisa di-refresh via AJAX
    // ============================================
    const map = L.map('worldMap').setView([20, 0], 2);

    // Jaga-jaga: kalau container map belum ke-render sempurna pas Leaflet init
    // (misal karena CSS/font belum selesai load), tile bisa gagal muncul/putih.
    // invalidateSize() maksa Leaflet re-check ukuran container.
    setTimeout(() => map.invalidateSize(), 200);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Layer group khusus marker negara, biar gampang di-clear & digambar ulang tanpa reset zoom/pan user
    const countryMarkersLayer = L.layerGroup().addTo(map);

    function riskColor(level) {
        if (level === 'Low') return 'green';
        if (level === 'Medium') return 'orange';
        if (level === 'High') return 'red';
        return 'gray';
    }

    function renderCountryMarkers(countries) {
        countryMarkersLayer.clearLayers();

        countries.forEach(country => {
            if (!country.latitude || !country.longitude) return;

            const level = country.risk_level ?? 'Unknown';
            const risk = country.latest_risk_score
                ? Number(country.latest_risk_score.total_risk).toFixed(1)
                : (country.latestRiskScore ? Number(country.latestRiskScore.total_risk).toFixed(1) : 'N/A');

            const marker = L.circleMarker([country.latitude, country.longitude], {
                radius: 8,
                fillColor: riskColor(level),
                color: riskColor(level),
                weight: 1,
                opacity: 1,
                fillOpacity: 0.7
            });

            marker.bindPopup(`
                <strong>${country.name}</strong><br>
                Risk: ${risk}<br>
                Level: <span class="risk-${level.toLowerCase()}">${level}</span><br>
                GDP: $${Number(country.gdp ?? 0).toLocaleString()}<br>
                Inflation: ${Number(country.inflation ?? 0).toFixed(1)}%
            `);

            countryMarkersLayer.addLayer(marker);
        });
    }

    // Render marker awal dari data yang sudah di-render server (tidak perlu tunggu fetch pertama)
    @php
        $initialCountriesForMap = $countries->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'latitude' => $c->latitude,
                'longitude' => $c->longitude,
                'gdp' => $c->gdp,
                'inflation' => $c->inflation,
                'risk_level' => $c->risk_level,
                'latest_risk_score' => $c->latestRiskScore,
            ];
        });
    @endphp
    renderCountryMarkers(@json($initialCountriesForMap));

    // ============================================
    // Tabel negara — build 1 baris <tr> dari data JSON /api/countries
    // ============================================
    function buildCountryRow(country) {
        const risk = country.latest_risk_score
            ? Number(country.latest_risk_score.total_risk).toFixed(1)
            : 'N/A';
        const level = country.risk_level ?? 'Unknown';
        const badgeClass = level === 'Low' ? 'success' : (level === 'Medium' ? 'warning' : (level === 'High' ? 'danger' : 'secondary'));

        return `
            <tr data-country-id="${country.id}">
                <td><span class="me-2">${country.flag_emoji ?? ''}</span>${country.name}</td>
                <td>${country.currency ?? 'N/A'}</td>
                <td>$${Number(country.gdp ?? 0).toLocaleString()}</td>
                <td>${Number(country.inflation ?? 0).toFixed(1)}%</td>
                <td>${risk}</td>
                <td><span class="badge bg-${badgeClass}">${level}</span></td>
                <td>
                    <a href="/countries/${country.id}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleWatchlist(${country.id})">
                        <i class="far fa-star"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    function renderCountriesTable(countries) {
        const tbody = document.getElementById('countriesTableBody');
        tbody.innerHTML = countries.map(buildCountryRow).join('');
        // Re-apply filter search yang lagi aktif (kalau user sedang ngetik search pas refresh jalan)
        applyCountrySearch();
    }

    // ============================================
    // Global Country Search — cari & tambah negara APA SAJA di dunia
    // (beda dari applyCountrySearch di bawah, yang cuma filter tabel lokal)
    // ============================================
    const globalSearchInput = document.getElementById('globalCountrySearch');
    const globalResultsBox = document.getElementById('globalCountryResults');
    let globalSearchTimeout = null;

    globalSearchInput.addEventListener('input', () => {
        clearTimeout(globalSearchTimeout);
        const q = globalSearchInput.value.trim();

        if (q.length < 2) {
            globalResultsBox.style.display = 'none';
            globalResultsBox.innerHTML = '';
            return;
        }

        // Debounce 400ms biar tidak nembak API tiap ketikan huruf
        globalSearchTimeout = setTimeout(() => searchGlobalCountry(q), 400);
    });

    async function searchGlobalCountry(q) {
        try {
            globalResultsBox.innerHTML = `<div class="list-group-item small text-muted">Mencari "${q}"...</div>`;
            globalResultsBox.style.display = 'block';

            const res = await fetch(`/api/countries/search?q=${encodeURIComponent(q)}`);
            if (!res.ok) throw new Error('Search gagal');
            const results = await res.json();

            if (!results.length) {
                globalResultsBox.innerHTML = `<div class="list-group-item small text-muted">Negara "${q}" tidak ditemukan.</div>`;
                return;
            }

            globalResultsBox.innerHTML = results.map(r => `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        ${r.flag_url ? `<img src="${r.flag_url}" width="20" class="me-2" style="border-radius:2px;">` : ''}
                        <strong>${r.name}</strong>
                        <span class="text-muted small">${r.capital ? '· ' + r.capital : ''} ${r.region ? '· ' + r.region : ''}</span>
                    </div>
                    <button class="btn btn-sm ${r.already_added ? 'btn-outline-primary' : 'btn-success'}"
                            onclick="selectGlobalCountry('${r.code}', ${r.already_added ? r.country_id : 'null'}, this)">
                        ${r.already_added ? '<i class="fas fa-eye me-1"></i>Lihat' : '<i class="fas fa-plus me-1"></i>Pantau'}
                    </button>
                </div>
            `).join('');
        } catch (e) {
            globalResultsBox.innerHTML = `<div class="list-group-item small text-danger">Gagal mencari negara. Coba lagi.</div>`;
        }
    }

    async function selectGlobalCountry(code, existingId, btnEl) {
        // Sudah ada di DB lokal -> langsung ke dashboard negaranya
        if (existingId) {
            window.location.href = `/countries/${existingId}`;
            return;
        }

        const originalHtml = btnEl.innerHTML;
        btnEl.disabled = true;
        btnEl.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const res = await fetch(`/api/countries/add/${code}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'Accept': 'application/json',
                },
            });

            if (!res.ok) throw new Error('Gagal menambahkan negara');
            const country = await res.json();

            // Negara baru berhasil ditambah & disinkron -> langsung buka dashboard-nya
            window.location.href = `/countries/${country.id}`;
        } catch (e) {
            btnEl.disabled = false;
            btnEl.innerHTML = originalHtml;
            alert('Gagal menambahkan negara ini. Coba lagi sebentar lagi.');
        }
    }

    // Klik di luar box hasil -> sembunyikan dropdown
    document.addEventListener('click', (e) => {
        if (!globalSearchInput.contains(e.target) && !globalResultsBox.contains(e.target)) {
            globalResultsBox.style.display = 'none';
        }
    });

    // ============================================
    // Search functionality
    // ============================================
    function applyCountrySearch() {
        const search = document.getElementById('searchCountry').value.toLowerCase();
        const rows = document.querySelectorAll('#countriesTableBody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    }

    document.getElementById('searchCountry').addEventListener('keyup', applyCountrySearch);

    // ============================================
    // refreshData() — AJAX penuh, TANPA reload halaman
    // Update: 4 stat card + tabel negara + marker peta
    // ============================================
    let isRefreshing = false;

    async function refreshData() {
        if (isRefreshing) return;
        isRefreshing = true;

        const icon = document.getElementById('refreshIcon');
        const btn = document.getElementById('refreshBtn');
        icon.classList.add('fa-spin');
        btn.disabled = true;

        try {
            const [summaryRes, countriesRes] = await Promise.all([
                fetch('/api/risk/summary'),
                fetch('/api/countries')
            ]);

            if (!summaryRes.ok || !countriesRes.ok) {
                throw new Error('Gagal mengambil data dari server');
            }

            const summary = await summaryRes.json();
            const countries = await countriesRes.json();

            // Update 4 stat card
            document.querySelector('[data-stat="total"]').textContent = summary.total_countries;
            document.querySelector('[data-stat="low"]').textContent = summary.risks.Low;
            document.querySelector('[data-stat="medium"]').textContent = summary.risks.Medium;
            document.querySelector('[data-stat="high"]').textContent = summary.risks.High;

            // Update tabel negara
            renderCountriesTable(countries);

            // Update marker peta
            renderCountryMarkers(countries);

            if (typeof toastr !== 'undefined') {
                toastr.success('Data berhasil di-refresh');
            }
        } catch (error) {
            console.error('refreshData error:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Gagal refresh data, coba lagi.');
            }
        } finally {
            icon.classList.remove('fa-spin');
            btn.disabled = false;
            isRefreshing = false;
        }
    }

    // ============================================
    // syncCountryData() — panggil POST /api/countries/sync
    // Ambil GDP, inflasi, populasi, ekspor, impor (World Bank)
    // + profil negara (REST Countries) untuk SEMUA negara sekaligus.
    // Setelah sukses, panggil refreshData() supaya tabel & marker peta ke-update.
    // ============================================
    let isSyncing = false;

    async function syncCountryData() {
        if (isSyncing) return;
        isSyncing = true;

        const icon = document.getElementById('syncIcon');
        const btn = document.getElementById('syncBtn');
        icon.classList.add('fa-spin');
        btn.disabled = true;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const res = await fetch('/api/countries/sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (!res.ok) {
                throw new Error('Gagal sync data ke server');
            }

            const data = await res.json();

            // Setelah sync sukses, refresh tabel negara & marker peta tanpa reload halaman
            await refreshData();

            if (typeof toastr !== 'undefined') {
                toastr.success(`Sync berhasil untuk ${data.countries.length} negara`);
            }
        } catch (error) {
            console.error('syncCountryData error:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Gagal sync data, coba lagi.');
            }
        } finally {
            icon.classList.remove('fa-spin');
            btn.disabled = false;
            isSyncing = false;
        }
    }

    function toggleWatchlist(countryId) {
        fetch(`/api/watchlist/toggle/${countryId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Auto-refresh every 5 minutes — pakai fungsi refreshData() yang sama, jadi behavior-nya konsisten
    setInterval(refreshData, 300000);
</script>
@endpush
@endsection