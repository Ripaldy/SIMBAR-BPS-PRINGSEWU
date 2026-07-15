<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMBAR - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('Logo_BPS.PNG') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body class="dashboard-body" id="app-body">

<div class="dashboard-container" id="dashboard-container">

    {{-- MOBILE OVERLAY --}}
    <div class="mobile-overlay" id="mobile-overlay" onclick="closeMobileMenu()" style="display:none;"></div>

    {{-- SIDEBAR --}}
    <nav class="sidebar" id="sidebar">
        {{-- Tombol tutup (mobile) --}}
        <button id="mobile-close-btn" onclick="closeMobileMenu()" style="display:none; position:absolute; top:15px; right:15px; background:none; border:none; color:#94a3b8; cursor:pointer;">
            <i data-lucide="x" style="width:24px;height:24px;"></i>
        </button>

        {{-- Profile --}}
        <div class="sidebar-profile" id="sidebar-profile">
            <div class="sidebar-avatar">
                @if(auth()->user()->foto_profil)
                    <img src="{{ asset('uploads/' . auth()->user()->foto_profil) }}" alt="Profil">
                @else
                    <i data-lucide="user" style="width:50px;height:50px;color:#cbd5e1;"></i>
                @endif
            </div>
            <div id="sidebar-user-info">
                <h2 class="sidebar-name">{{ auth()->user()->nama_lengkap }}</h2>
                <span class="sidebar-role">{{ auth()->user()->role }}</span>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="sidebar-nav">
            {{-- Profil Saya --}}
            <a href="{{ route('profil.index') }}" class="nav-item {{ request()->routeIs('profil.*') ? 'active' : '' }}">
                <i data-lucide="user" style="width:20px;height:20px;flex-shrink:0;"></i>
                <span>Profil Saya</span>
            </a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i data-lucide="home" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Dasbor Utama</span>
                </a>
                <a href="{{ route('aset.index') }}" class="nav-item {{ request()->routeIs('aset.*') ? 'active' : '' }}">
                    <i data-lucide="boxes" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Manajemen Aset</span>
                </a>
                <a href="{{ route('pengguna.index') }}" class="nav-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}">
                    <i data-lucide="users" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Manajemen Pengguna</span>
                </a>
                <a href="{{ route('persetujuan.index') }}" class="nav-item {{ request()->routeIs('persetujuan.*') ? 'active' : '' }}">
                    <i data-lucide="bell" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Persetujuan Barang</span>
                </a>
                <a href="{{ route('otomatisasi.index') }}" class="nav-item {{ request()->routeIs('otomatisasi.*') ? 'active' : '' }}">
                    <i data-lucide="settings" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Pengaturan Otomatisasi</span>
                </a>
                <a href="{{ route('laporan.index') }}" class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <i data-lucide="clock" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Riwayat Laporan</span>
                </a>
            @elseif(auth()->user()->isPemimpin())
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i data-lucide="home" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Dasbor Utama</span>
                </a>
                <a href="{{ route('katalog.index') }}" class="nav-item {{ request()->routeIs('katalog.*') ? 'active' : '' }}">
                    <i data-lucide="shopping-cart" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Katalog Barang</span>
                </a>
                <a href="{{ route('riwayat.index') }}" class="nav-item {{ request()->routeIs('riwayat.*') ? 'active' : '' }}">
                    <i data-lucide="list" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Riwayat Pengajuan</span>
                </a>
            @elseif(auth()->user()->isPegawai())
                <a href="{{ route('katalog.index') }}" class="nav-item {{ request()->routeIs('katalog.*') ? 'active' : '' }}">
                    <i data-lucide="shopping-cart" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Katalog Barang</span>
                </a>
                <a href="{{ route('riwayat.index') }}" class="nav-item {{ request()->routeIs('riwayat.*') ? 'active' : '' }}">
                    <i data-lucide="list" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Riwayat Pengajuan</span>
                </a>
            @endif
        </div>

        {{-- Footer --}}
        <div class="sidebar-footer">
            {{-- Toggle button (desktop only) --}}
            <button class="sidebar-toggle" id="sidebar-toggle-btn" onclick="toggleSidebar()" 
                style="position:absolute; right:-16px; top:0; transform:translateY(-50%); width:32px; height:32px; background:white; border:2px solid #1f4068; border-radius:50%; display:flex; justify-content:center; align-items:center; cursor:pointer; z-index:100; padding:0;">
                <i data-lucide="arrow-left" id="toggle-icon" style="width:20px;height:20px;color:#1f4068;stroke-width:2.5px;"></i>
            </button>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i data-lucide="log-out" style="width:20px;height:20px;flex-shrink:0;"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="main-wrapper">

        {{-- HEADER --}}
        <header class="app-header">
            <div class="header-brand">
                {{-- Hamburger (mobile) --}}
                <button id="hamburger-btn" onclick="openMobileMenu()" style="display:none; background:none; border:none; padding:5px; cursor:pointer; color:#1f4068;">
                    <i data-lucide="menu" style="width:26px;height:26px;"></i>
                </button>

                <div class="header-logo">
                    <i data-lucide="boxes" style="width:22px;height:22px;color:white;"></i>
                </div>
                <div>
                    <h3 class="header-title">
                        @if(auth()->user()->isPegawai()) PORTAL LAYANAN
                        @elseif(auth()->user()->isPemimpin()) MONITORING &amp; PERSETUJUAN
                        @else PANEL MANAJEMEN
                        @endif
                    </h3>
                    <span class="header-subtitle" id="header-subtitle">
                        @if(auth()->user()->isPegawai()) PEGAWAI DASHBOARD
                        @elseif(auth()->user()->isPemimpin()) LEADER DASHBOARD
                        @else ADMIN DASHBOARD
                        @endif
                    </span>
                </div>
            </div>
            <div class="header-user">
                <div class="header-user-info">
                    <h4 class="header-user-name">{{ auth()->user()->nama_lengkap }}</h4>
                    <span class="header-user-email">{{ auth()->user()->email }}</span>
                </div>
                <div class="header-avatar">
                    @if(auth()->user()->foto_profil)
                        <img src="{{ asset('uploads/' . auth()->user()->foto_profil) }}" alt="Profil">
                    @else
                        <i data-lucide="user" style="width:20px;height:20px;color:#7f8c8d;"></i>
                    @endif
                </div>
            </div>
        </header>

        {{-- CONTENT --}}
        <main class="content-area">
            @if(session('success'))
                <div class="alert alert-success" id="flash-success" style="margin-bottom:20px;">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error" style="margin-bottom:20px;">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger" style="background:#fef2f2; border-left:4px solid #ef4444; color:#991b1b; padding:15px; border-radius:6px; margin-bottom:20px;">
                    <ul style="margin:0; padding-left:20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    // ============ SIDEBAR TOGGLE (DESKTOP) ============
    let sidebarOpen = true;

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const icon = document.getElementById('toggle-icon');
        const userInfo = document.getElementById('sidebar-user-info');
        sidebarOpen = !sidebarOpen;
        sidebar.classList.toggle('collapsed', !sidebarOpen);
        if (icon) icon.setAttribute('data-lucide', sidebarOpen ? 'arrow-left' : 'arrow-right');
        if (userInfo) userInfo.style.display = sidebarOpen ? 'block' : 'none';
        lucide.createIcons();
    }

    // ============ MOBILE MENU ============
    function openMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const closeBtn = document.getElementById('mobile-close-btn');
        sidebar.classList.add('mobile-open');
        overlay.style.display = 'block';
        if (closeBtn) closeBtn.style.display = 'block';
    }
    function closeMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        const closeBtn = document.getElementById('mobile-close-btn');
        sidebar.classList.remove('mobile-open');
        overlay.style.display = 'none';
        if (closeBtn) closeBtn.style.display = 'none';
    }

    // ============ RESPONSIVE CHECK ============
    function checkMobile() {
        const isMobile = window.innerWidth < 768;
        const hamburger = document.getElementById('hamburger-btn');
        const toggleBtn = document.getElementById('sidebar-toggle-btn');
        const contentArea = document.querySelector('.content-area');

        if (hamburger) hamburger.style.display = isMobile ? 'flex' : 'none';
        if (toggleBtn) toggleBtn.style.display = isMobile ? 'none' : 'block';
        if (contentArea) contentArea.style.padding = isMobile ? '15px' : '30px';

        if (!isMobile) {
            closeMobileMenu();
        }
    }
    window.addEventListener('resize', checkMobile);
    checkMobile();

    // ============ AUTO-HIDE FLASH MESSAGE ============
    setTimeout(() => {
        const el = document.getElementById('flash-success');
        if (el) el.style.display = 'none';
    }, 4000);

    // Init Lucide icons
    lucide.createIcons();
</script>

@stack('scripts')
</body>
</html>
