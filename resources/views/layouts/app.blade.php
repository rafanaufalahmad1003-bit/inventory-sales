<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - LensRent Rental & Inventory Kamera</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .brand-badge {
            font-size: 0.65rem;
            letter-spacing: 0.5px;
            vertical-align: middle;
        }
        .card {
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05);
            border-radius: 0.65rem;
        }
        .table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            background-color: #f1f5f9;
        }
        .btn {
            border-radius: 0.5rem;
            font-weight: 500;
        }
        .badge {
            font-weight: 500;
            padding: 0.4em 0.65em;
        }
        .bg-dark-slate {
            background-color: #0f172a !important;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
            }
        }
    </style>
    @yield('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark-slate sticky-top shadow-sm no-print">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <span class="p-1 rounded bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-camera-fill"></i>
                </span>
                <span>LensRent</span>
                <span class="badge bg-secondary brand-badge text-uppercase">Camera Hub</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>

                    @if(auth()->user()->isPelanggan())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('products.*') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('products.index') }}">
                                <i class="bi bi-camera me-1"></i> Katalog Rental Kamera
                            </a>
                        </li>
                    @else
                        <!-- Menu Master Data (Staff, Admin, Owner) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('products*') || request()->is('categories*') || request()->is('suppliers*') || request()->is('customers*') ? 'active text-primary fw-semibold' : '' }}" href="#" id="masterDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-database me-1"></i> Master Data
                            </a>
                            <ul class="dropdown-menu shadow-sm">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                                        <i class="bi bi-camera me-2 text-primary"></i> Produk Kamera & Gear
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                                        <i class="bi bi-tags me-2 text-primary"></i> Kategori Produk
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                        <i class="bi bi-person-check me-2 text-primary"></i> Pelanggan (Rental)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                                        <i class="bi bi-truck me-2 text-primary"></i> Pemasok (Supplier)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Menu Transaksi (Staff, Admin, Owner) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('sales*') || request()->is('purchases*') ? 'active text-primary fw-semibold' : '' }}" href="#" id="transaksiDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-arrow-left-right me-1"></i> Transaksi
                            </a>
                            <ul class="dropdown-menu shadow-sm">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                                        <i class="bi bi-receipt-cutoff me-2 text-success"></i> Rental & Penjualan (Stock-Out)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                                        <i class="bi bi-box-arrow-in-down me-2 text-info"></i> Pembelian Stok (Stock-In)
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Menu Khusus Owner & Admin -->
                        @can('admin-or-owner')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('reports.*') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('reports.index') }}">
                                    <i class="bi bi-bar-chart-line me-1"></i> Laporan
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active text-primary fw-semibold' : '' }}" href="{{ route('users.index') }}">
                                    <i class="bi bi-people me-1"></i> Manajemen Pengguna
                                </a>
                            </li>
                        @endcan
                    @endif
                </ul>

                <!-- User Profile & Logout -->
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end text-light d-none d-md-block">
                        <div class="fw-medium small">{{ auth()->user()->name }}</div>
                        <div>
                            @if(auth()->user()->isOwner())
                                <span class="badge bg-danger">Owner</span>
                            @elseif(auth()->user()->isAdmin())
                                <span class="badge bg-primary">Admin</span>
                            @elseif(auth()->user()->isStaff())
                                <span class="badge bg-info text-dark">Staff</span>
                            @else
                                <span class="badge bg-success">Pelanggan</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" title="Keluar">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-sm-inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container-fluid px-lg-4 py-4 flex-grow-1">

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm no-print mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm no-print mb-4" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm no-print mb-4" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <strong>Terjadi kesalahan pada input data:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 text-center text-muted small mt-auto no-print">
        <div class="container-fluid px-lg-4">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start">
                    <strong>LensRent</strong> &copy; {{ date('Y') }} - Inventory & Rental Kamera Management System
                </div>
                <div class="col-md-6 text-md-end">
                    <span>Web Programming 2 &bull; Integrated Laravel Project</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
