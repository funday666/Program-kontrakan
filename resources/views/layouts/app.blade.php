<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Sewa Lahan</title>

    <!-- CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --bg-body-color: #f4f6f9;
        }

        body {
            background-color: var(--bg-body-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .mobile-header {
            display: none;
        }

        /* --- DESKTOP (Layar Besar) --- */
        @media (min-width: 992px) {
            #sidebarMenu {
                width: var(--sidebar-width);
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1040;
                border-right: 1px solid #e3e6f0;
                background-color: #ffffff;
                display: flex;
                flex-direction: column;
            }

            .content-wrapper {
                margin-left: var(--sidebar-width);
                min-height: 100vh;
                padding: 2rem;
            }
        }

        /* --- MOBILE & TABLET (Layar Kecil) --- */
        @media (max-width: 991.98px) {
            .mobile-header {
                display: flex;
                position: sticky;
                top: 0;
                z-index: 1030;
                background: #ffffff;
                border-bottom: 1px solid #e3e6f0;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            }

            .content-wrapper {
                padding: 1.25rem 1rem;
                width: 100%;
            }
        }

        /* --- HILANGKAN TAMPILAN SCROLLBAR --- */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* --- DESAIN NAVIGASI MENU --- */
        .nav-link {
            color: #5c636a;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 6px;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .nav-link:hover {
            background-color: #f8f9fa;
            color: #0d6efd;
        }

        .nav-link.active {
            background-color: #ebf3ff;
            color: #0d6efd;
            font-weight: 600;
        }

        .nav-link i {
            font-size: 1.2rem;
            width: 30px;
            display: inline-block;
        }

        /* --- EFEK PANAH DROPDOWN --- */
        .dropdown-toggle-icon {
            transition: transform 0.3s ease;
        }
        .nav-link[aria-expanded="true"] .dropdown-toggle-icon {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>

    <!-- 1. HEADER MOBILE -->
    <div class="mobile-header justify-content-between align-items-center p-3">
        <a class="text-decoration-none fw-bold text-success d-flex align-items-center m-0"
            href="{{ url('/laporan-keuangan') }}">
            <i class="bi bi-houses-fill fs-3 me-2"></i>
            <h5 class="m-0 fw-bolder">PANDE MESARI</h5>
        </a>
        <button class="btn btn-light border shadow-sm" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
            <i class="bi bi-list fs-4"></i>
        </button>
    </div>

    <!-- 2. SIDEBAR KIRI -->
    <div class="offcanvas-lg offcanvas-start bg-white shadow-sm" tabindex="-1" id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel">

        <!-- Header Sidebar (Logo) -->
        <div class="p-4 d-flex align-items-center border-bottom border-light">
            <i class="bi bi-houses-fill text-success fs-2 me-2"></i>
            <h4 class="fw-bolder m-0 text-success" id="sidebarMenuLabel">PANDE MESARI</h4>
            <button type="button" class="btn-close d-lg-none ms-auto shadow-none" data-bs-dismiss="offcanvas"
                data-bs-target="#sidebarMenu" aria-label="Close"></button>
        </div>

        <!-- Body Sidebar (Menu) -->
        <div class="offcanvas-body d-flex flex-column p-3 flex-grow-1 hide-scrollbar" style="overflow-y: auto;">

            <ul class="nav nav-pills flex-column mb-auto mt-2">
                <!-- Menu Dashboard -->
                <li class="nav-item">
                    <a href="{{ url('/laporan-keuangan') }}"
                        class="nav-link {{ request()->is('laporan-keuangan*') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2-fill"></i> Dashboard
                    </a>
                </li>
                
                <!-- Menu DROPDOWN Histori -->
                <li class="nav-item mt-1">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('histori/*') ? 'active' : '' }}" 
                       data-bs-toggle="collapse" 
                       href="#collapseHistori" 
                       role="button" 
                       aria-expanded="{{ request()->is('histori/*') ? 'true' : 'false' }}" 
                       aria-controls="collapseHistori">
                        <div>
                            <i class="bi bi-clock-history"></i> Histori Transaksi
                        </div>
                        <i class="bi bi-chevron-down fs-6 dropdown-toggle-icon" style="width: auto;"></i>
                    </a>
                    
                    <!-- Isi Dropdown (Sub-menu) -->
                    <div class="collapse {{ request()->is('histori/*') ? 'show' : '' }}" id="collapseHistori">
                        <ul class="nav flex-column ms-3 mt-1 mb-2" style="border-left: 2px solid #e9ecef;">
                            <li class="nav-item ps-2 mb-1">
                                <a class="nav-link text-muted py-2 {{ request()->is('histori/pemasukan') ? 'fw-bold text-primary bg-light' : '' }}" href="{{ url('/histori/pemasukan') }}" style="font-size: 0.9rem;">
                                    <i class="bi bi-box-arrow-in-down-left fs-6"></i> Pemasukan
                                </a>
                            </li>
                            <li class="nav-item ps-2 mb-1">
                                <a class="nav-link text-muted py-2 {{ request()->is('histori/pengeluaran') ? 'fw-bold text-primary bg-light' : '' }}" href="{{ url('/histori/pengeluaran') }}" style="font-size: 0.9rem;">
                                    <i class="bi bi-box-arrow-up-right fs-6"></i> Pengeluaran
                                </a>
                            </li>
                            <li class="nav-item ps-2">
                                <a class="nav-link text-muted py-2 {{ request()->is('histori/mutasi') ? 'fw-bold text-primary bg-light' : '' }}" href="{{ url('/histori/mutasi') }}" style="font-size: 0.9rem;">
                                    <i class="bi bi-arrow-left-right fs-6"></i> Mutasi Dana
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Menu Data Kontrak Sewa -->
                <li class="nav-item">
                    <a href="{{ url('/sewa-lahan') }}"
                        class="nav-link {{ request()->is('sewa-lahan*') ? 'active' : '' }}">
                        <i class="bi bi-card-checklist"></i> Data Kontrak Sewa
                    </a>
                </li>

                <!-- Menu Manajemen User -->
                <li class="nav-item">
                    <a href="{{ url('/users') }}" class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Manajemen User
                    </a>
                </li>
            </ul>

            <!-- Bagian Bawah: Info User & Logout -->
            @auth
                <div class="mt-4 pt-3 border-top border-light mt-auto">
                    <div class="d-flex align-items-center px-2 mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3"
                            style="width: 42px; height: 42px;">
                            <i class="bi bi-person-fill fs-5"></i>
                        </div>
                        <div class="text-truncate">
                            <small class="text-muted d-block fw-semibold"
                                style="font-size: 11px; letter-spacing: 0.5px;">ADMIN</small>
                            <span class="fw-bold text-dark text-truncate d-block">{{ Auth::user()->name }}</span>
                        </div>
                    </div>

                    <form action="{{ url('/logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="btn btn-outline-danger w-100 fw-semibold text-start px-3 py-2 rounded-3 border-0 bg-danger bg-opacity-10 d-flex align-items-center">
                            <i class="bi bi-box-arrow-right fs-5 me-2"></i> Keluar (Logout)
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>

    <!-- 3. KONTEN UTAMA -->
    <main class="content-wrapper">
        @yield('content')
    </main>

    <!-- SCRIPT BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>