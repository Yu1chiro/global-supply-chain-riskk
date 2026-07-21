{{-- Tampilan: ports --}}

@extends('layouts.app')

@section('title', 'Port Locations')

{{-- Bagian: content --}}
@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-ship me-2"></i>Global Port Locations</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-primary" onclick="refreshPorts()">
            <i class="fas fa-sync me-1"></i>Refresh
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-map me-2"></i>World Port Locations</h5>
            </div>
            <div class="card-body">
                <div id="portMap" style="height: 500px;"></div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Search Ports</h5>
            </div>
            <div class="card-body">
                <input type="text" id="searchPort" class="form-control mb-2" placeholder="Search port name...">
                <input type="text" id="searchCountry" class="form-control mb-2" placeholder="Search country...">
                <button class="btn btn-primary w-100" onclick="searchPorts()">
                    <i class="fas fa-search me-1"></i>Search
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Ports List</h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <div id="portList">
                    <p class="text-muted text-center py-3">Loading ports...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const map = L.map('portMap').setView([20, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Fix bug: Leaflet ngukur lebar container SEBELUM Bootstrap grid selesai
    // "settle", jadi peta sering ke-render lebih sempit dari kolomnya.
    // invalidateSize() maksa Leaflet ngukur ulang lebar container yang benar.
    setTimeout(() => map.invalidateSize(), 100);
    window.addEventListener('resize', () => map.invalidateSize());

    let allPorts = [];
    let markers = [];

    function loadPorts() {
        const portList = document.getElementById('portList');
        portList.innerHTML = '<p class="text-muted text-center py-3">Loading ports...</p>';

        // per_page=300 — cukup untuk menampilkan (hampir) semua pelabuhan tanpa
        // membebani server dev (php artisan serve bersifat single-threaded) atau
        // membuat render 500+ marker Leaflet jadi berat/lemot di browser.
        fetch('/api/ports?per_page=300')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server merespons dengan status ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    allPorts = data.data;
                } else if (data.data && Array.isArray(data.data)) {
                    allPorts = data.data;
                } else if (Array.isArray(data)) {
                    allPorts = data;
                } else {
                    allPorts = [];
                }

                if (allPorts.length === 0) {
                    portList.innerHTML = '<p class="text-muted text-center py-3">No ports found</p>';
                } else {
                    renderPorts(allPorts);
                    renderPortList(allPorts);
                }
            })
            .catch(error => {
                console.error('Error loading ports:', error);
                document.getElementById('portList').innerHTML =
                    '<p class="text-danger text-center py-3">Failed to load ports: ' + error.message + '</p>';
            });
    }

    function renderPorts(ports) {
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        if (!ports || ports.length === 0) return;

        ports.forEach(port => {
            const marker = L.marker([port.latitude, port.longitude])
                .addTo(map)
                .bindPopup(`
                    <strong>${port.name}</strong><br>
                    Country: ${port.country}<br>
                    Type: ${port.type || 'N/A'}<br>
                    Status: <span class="badge bg-${port.status === 'active' ? 'success' : 'secondary'}">${port.status || 'active'}</span>
                `);
            markers.push(marker);
        });
    }

    function renderPortList(ports) {
        const container = document.getElementById('portList');
        if (!ports || ports.length === 0) {
            container.innerHTML = '<p class="text-muted text-center py-3">No ports found</p>';
            return;
        }

        let html = '<div class="list-group list-group-flush">';
        ports.forEach(port => {
            html += `
                <div class="list-group-item list-group-item-action" onclick="focusPort(${port.latitude}, ${port.longitude})">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${port.name}</strong><br>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>${port.country}</small>
                        </div>
                        <span class="badge bg-${port.status === 'active' ? 'success' : 'secondary'}">${port.status || 'active'}</span>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function focusPort(lat, lng) {
        map.setView([lat, lng], 8);
    }

    function searchPorts() {
        const portName = document.getElementById('searchPort').value.toLowerCase();
        const country = document.getElementById('searchCountry').value.toLowerCase();

        let filtered = allPorts;
        if (portName) filtered = filtered.filter(p => p.name.toLowerCase().includes(portName));
        if (country) filtered = filtered.filter(p => p.country.toLowerCase().includes(country));

        renderPorts(filtered);
        renderPortList(filtered);
    }

    function refreshPorts() {
        loadPorts();
    }

    document.addEventListener('DOMContentLoaded', loadPorts);

    document.getElementById('searchPort').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') searchPorts();
    });
    document.getElementById('searchCountry').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') searchPorts();
    });
</script>
@endpush
@endsection