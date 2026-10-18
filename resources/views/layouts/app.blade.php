<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Subsidi Transportasi') | Subsidi Transportasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/datin.css') }}">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar" aria-label="Menu utama">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/></svg>
            </span>
            <span>
                <strong>Subsidi Transportasi</strong>
                <small>Sistem Informasi Regional</small>
            </span>
        </a>

        <nav class="nav">
            @php($pengguna = auth()->user())
            @if ($pengguna->isModa())
                <p class="nav-title">MENU UTAMA</p>
            @endif
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/></svg>
                <span>Dashboard</span>
            </a>
            @if ($pengguna->isSuperAdmin())
                <a href="{{ route('admin.user.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/></svg>
                    <span>Data User</span>
                </a>
            @elseif ($pengguna->bisa('pemda.unggah'))
                <a href="{{ route('input-data.create') }}" class="nav-link {{ request()->routeIs('input-data.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/></svg>
                    <span>Input Data</span>
                </a>
            @endif
            <a href="{{ route('data-wilayah.index') }}" class="nav-link {{ request()->routeIs('data-wilayah.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2z"/><path d="M9 4v14M15 6v14"/></svg>
                <span>Data Wilayah</span>
            </a>
            @if ($pengguna->isModa() && $pengguna->bisa('laporan.tinjau'))
                <a href="{{ route('permohonan.index') }}" class="nav-link {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V4M4 20h16"/><path d="M9 16v-5M13 16V8M17 16v-3"/></svg>
                    <span>Permohonan</span>
                </a>
            @endif
        </nav>
        @if (auth()->user()->isModa() && auth()->user()->bisa('laporan.tinjau'))
            @php($ringkas = \App\Support\Permohonan::ringkasanSidebar(auth()->user()))
            <a class="side-card" href="{{ route('permohonan.index', ['status' => 'menunggu']) }}">
                <span class="side-card-top">
                    <span>PERLU DITINJAU</span>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 13l2-8h12l2 8M4 13v6h16v-6M4 13h5l1 2h4l1-2h5"/></svg>
                </span>
                <strong>{{ $ringkas['menunggu'] }}</strong>
                <small>
                    @if ($ringkas['lewat'] > 0)
                        {{ $ringkas['lewat'] }} laporan melewati target waktu peninjauan
                    @else
                        Tidak ada laporan yang melewati target waktu peninjauan
                    @endif
                </small>
            </a>
        @endif

        <div class="sidebar-user">
            <span class="avatar" aria-hidden="true"></span>
            <span class="who">
                <strong>{{ auth()->user()->full_name }}</strong>
                <small>{{ auth()->user()->role->nama_role ?? '-' }}</small>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout" title="Keluar" aria-label="Keluar">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
                </button>
            </form>
        </div>
    </aside>
    <div class="backdrop" id="backdrop"></div>

    <div class="main">
        <header class="topbar">
            <button type="button" class="menu-btn" id="menuBtn" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <h1>@yield('heading')@hasSection('subheading')<span class="h1-sep" aria-hidden="true"></span><small>@yield('subheading')</small>@endif</h1>
            <form class="search" action="{{ trim($__env->yieldContent('search_action')) ?: route('data-wilayah.index') }}" method="GET" role="search">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ trim($__env->yieldContent('search_placeholder')) ?: 'Cari data wilayah/provinsi...' }}" aria-label="Cari">
            </form>
            <span class="icon-btn" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 9a6 6 0 10-12 0c0 7-3 8-3 8h18s-3-1-3-8"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
            </span>
            <span class="lang">ID</span>
        </header>

        <main class="content">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error" role="alert">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('js/datin.js') }}" defer></script>
</body>
</html>
