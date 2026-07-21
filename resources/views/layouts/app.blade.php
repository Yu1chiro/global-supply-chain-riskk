{{-- Tampilan: app --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RiskIntel - Supply Chain Risk Intelligence')</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />

    

    <style>
        :root {
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .risk-low { color: #28a745; }
        .risk-medium { color: #ffc107; }
        .risk-high { color: #dc3545; }

        .bg-risk-low { background-color: #d4edda; }
        .bg-risk-medium { background-color: #fff3cd; }
        .bg-risk-high { background-color: #f8d7da; }

        .sidebar {
            min-height: 100vh;
            background: #1a1a2e;
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: #a8a8b3;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 10px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: rgba(13, 110, 253, 0.6);
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }

        .sidebar .nav-link.text-danger:hover {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px 30px;
            min-height: 100vh;
        }

        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: default;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        #worldMap, #weatherMap, #portMap {
            height: 500px;
            border-radius: 8px;
        }

        .sentiment-positive { color: #28a745; }
        .sentiment-neutral { color: #ffc107; }
        .sentiment-negative { color: #dc3545; }

        .badge-soft-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-soft-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-soft-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* News Item Hover Effect */
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15)!important;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .hover-primary:hover {
            color: #0d6efd !important;
        }

        .news-item {
            background: white;
            transition: all 0.2s ease;
        }

        .news-item:hover {
            background: #f8f9fa;
        }

        .btn-group .btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
        }

        .btn-group .btn.active {
            background-color: #0d6efd;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            .sidebar .nav-link span {
                display: none;
            }
            .main-content {
                margin-left: 60px;
                padding: 15px;
            }
            .sidebar .brand-text {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div>
        
        <nav class="sidebar">
            <div class="p-3">
                <h5 class="text-white mb-4 brand-text">
                    <i class="fas fa-globe me-2"></i>RiskIntel
                </h5>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="fas fa-chart-pie me-2"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('countries*') || request()->routeIs('country.dashboard*') ? 'active' : '' }}"
                       href="{{ route('countries.index') }}">
                        <i class="fas fa-globe me-2"></i><span>Countries</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('weather') ? 'active' : '' }}"
                       href="{{ route('weather') }}">
                        <i class="fas fa-cloud-sun me-2"></i><span>Weather</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('currency') ? 'active' : '' }}"
                       href="{{ route('currency') }}">
                        <i class="fas fa-money-bill-trend-up me-2"></i><span>Currency</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('news*') ? 'active' : '' }}"
                       href="{{ route('news.index') }}">
                        <i class="fas fa-newspaper me-2"></i><span>News</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('ports*') ? 'active' : '' }}"
                       href="{{ route('ports.index') }}">
                        <i class="fas fa-ship me-2"></i><span>Ports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('analytics*') ? 'active' : '' }}"
                       href="{{ route('analytics.index') }}">
                        <i class="fas fa-chart-line me-2"></i><span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('comparison') ? 'active' : '' }}"
                       href="{{ route('comparison') }}">
                        <i class="fas fa-arrows-left-right me-2"></i><span>Comparison</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('watchlist') ? 'active' : '' }}"
                       href="{{ route('watchlist') }}">
                        <i class="fas fa-star me-2"></i><span>Watchlist</span>
                    </a>
                </li>

                
                @auth
                    @if(auth()->user()->is_admin ?? false)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                           href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-user-shield me-2"></i><span>Admin</span>
                        </a>
                    </li>
                    @endif
                @endauth

                
                <li class="nav-item mt-4 border-top pt-3">
                    <a class="nav-link text-danger" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i><span>Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>

        
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    
    @vite(['resources/js/news.js'])

    @stack('scripts')
</body>
</html>

