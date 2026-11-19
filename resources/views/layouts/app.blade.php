
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

    {{-- SIDEBAR --}}
    <aside class="sidebar" id="sidebar" aria-label="Menu utama">

        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-icon">
                <svg viewBox="0 0 24 24" width="22" height="22"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/>
                </svg>
            </span>

            <span>
                <strong>Subsidi Transportasi</strong>
                <small>Sistem Informasi Regional</small>
            </span>
        </a>

        <nav class="nav">

            @php($pengguna = auth()->user())

            @if ($pengguna->isModa() || $pengguna->isIntram())
                <p class="nav-title">MENU UTAMA</p>
            @endif

            {{-- DASHBOARD --}}
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">

                <svg viewBox="0 0 24 24" width="20" height="20"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 11l9-8 9 8"/>
                    <path d="M5 10v10h5v-6h4v6h5V10"/>
                </svg>

                <span>Dashboard</span>
            </a>


            {{-- MENU MODA --}}
            @if ($pengguna->isModa() && $pengguna->bisa('chr.pantau'))

                <a href="{{ route('surat.index') }}"
                   class="nav-link {{ request()->routeIs('surat.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/>
                        <path d="M13.5 6.5l4 4"/>
                    </svg>

                    <span>Surat</span>
                </a>

            @endif


            {{-- MENU SUPER ADMIN --}}
            @if ($pengguna->isSuperAdmin())

                {{-- DATA USER --}}
                <a href="{{ route('admin.user.index') }}"
                   class="nav-link {{ request()->routeIs('admin.user.*', 'admin.role.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/>
                        <path d="M13.5 6.5l4 4"/>
                    </svg>

                    <span>Data User</span>
                </a>


                {{-- MASTER SEKTOR --}}
                <a href="{{ route('admin.sektor.index') }}"
                   class="nav-link {{ request()->routeIs('admin.sektor.*', 'admin.komoditas.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7h16M4 12h16M4 17h10"/>
                    </svg>

                    <span>Master Sektor</span>
                </a>


                {{-- MASTER SATUAN DATA --}}
                <a href="{{ route('admin.master-satuan.index') }}"
                   class="nav-link {{ request()->routeIs('admin.master-satuan.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 5h16v14H4z"/>
                        <path d="M8 9h8M8 13h5M8 16h3"/>
                    </svg>

                    <span>Master Satuan Data</span>
                </a>


            {{-- MENU PEMDA --}}
            @elseif ($pengguna->bisa('pemda.unggah'))

                <a href="{{ route('input-data.create') }}"
                   class="nav-link {{ request()->routeIs('input-data.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/>
                        <path d="M13.5 6.5l4 4"/>
                    </svg>

                    <span>Input Data</span>
                </a>

            @endif


            {{-- DATA WILAYAH --}}
            <a href="{{ route('data-wilayah.index') }}"
               class="nav-link {{ request()->routeIs('data-wilayah.*') ? 'active' : '' }}">

                <svg viewBox="0 0 24 24" width="20" height="20"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2z"/>
                    <path d="M9 4v14M15 6v14"/>
                </svg>

                <span>Data Wilayah</span>
            </a>


            {{-- MENU PERMOHONAN MODA --}}
            @if ($pengguna->isModa() && $pengguna->bisa('laporan.tinjau'))

                <a href="{{ route('permohonan.index') }}"
                   class="nav-link {{ request()->routeIs('permohonan.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20V4M4 20h16"/>
                        <path d="M9 16v-5M13 16V8M17 16v-3"/>
                    </svg>

                    <span>Permohonan</span>
                </a>

            @endif


            {{-- MENU APPROVAL INTRAM --}}
            @if ($pengguna->isIntram() && $pengguna->bisa('chr.tinjau'))

                <a href="{{ route('approval.index') }}"
                   class="nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20V4M4 20h16"/>
                        <path d="M9 16v-5M13 16V8M17 16v-3"/>
                    </svg>

                    <span>Approval</span>
                </a>

            @endif


            {{-- HASIL UNGGAHAN INTRAM --}}
            @if ($pengguna->isIntram() && ($pengguna->bisa('cakupan.lihat_unggahan') || $pengguna->bisa('rekomendasi_teknis.unggah')))

                <a href="{{ route('hasil.index') }}"
                   class="nav-link {{ request()->routeIs('hasil.*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>

                    <span>Hasil Unggahan</span>
                </a>

            @endif

        </nav>


        {{-- SIDECARD --}}
        @hasSection('sidecard')

            @yield('sidecard')

        @elseif (auth()->user()->isModa() && auth()->user()->bisa('laporan.tinjau'))

            @php($ringkas = \App\Support\Permohonan::ringkasanSidebar(auth()->user()))

            <a class="side-card" href="{{ route('permohonan.index', ['status' => 'menunggu']) }}">

                <span class="side-card-top">
                    <span>PERLU DITINJAU</span>

                    <svg viewBox="0 0 24 24" width="22" height="22"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M4 13l2-8h12l2 8M4 13v6h16v-6M4 13h5l1 2h4l1-2h5"/>
                    </svg>
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

        @elseif (auth()->user()->isIntram())

            @php($menunggu = \App\Models\PengajuanSurat::whereIn('status', [
                \App\Support\SuratOptions::TERKIRIM,
                \App\Support\SuratOptions::DALAM_REVIEW
            ])->count())

            <a class="side-card side-info" href="{{ route('approval.index') }}">

                <span class="side-card-top side-info-top">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v5M12 8h.01"/>
                    </svg>

                    <span>Ruang kerja INTRAM</span>

                </span>

                <small>
                    Tinjau Surat CHR dari user moda dan lihat hasil unggahan Pemda.
                    {{ $menunggu }} surat menunggu peninjauan.
                </small>

            </a>

        @endif


        {{-- INFORMASI PENGGUNA SIDEBAR --}}
        <div class="sidebar-user">

            <span class="avatar" aria-hidden="true"></span>

            <a class="who" href="{{ route('akun.password') }}" title="Akun saya">
                <strong>{{ auth()->user()->full_name }}</strong>
                <small>{{ auth()->user()->role->nama_role ?? '-' }}</small>
            </a>

            <a class="logout" href="{{ route('akun.password') }}"
               title="Ubah kata sandi" aria-label="Ubah kata sandi">

                <svg viewBox="0 0 24 24" width="18" height="18"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <rect x="5" y="11" width="14" height="9" rx="2"/>
                    <path d="M8 11V8a4 4 0 018 0v3"/>
                    <circle cx="12" cy="15.5" r="1"/>
                </svg>

            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="logout"
                        title="Keluar" aria-label="Keluar">

                    <svg viewBox="0 0 24 24" width="18" height="18"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <path d="M16 17l5-5-5-5M21 12H9"/>
                    </svg>

                </button>
            </form>

        </div>

    </aside>


    <div class="backdrop" id="backdrop"></div>


    {{-- MAIN CONTENT --}}
    <div class="main">

        {{-- TOPBAR --}}
        <header class="topbar">

            <button type="button" class="menu-btn"
                    id="menuBtn" aria-label="Buka menu"
                    aria-controls="sidebar" aria-expanded="false">

                <svg viewBox="0 0 24 24" width="22" height="22"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>

            </button>


            <h1>
                @yield('heading')

                @hasSection('subheading')
                    <span class="h1-sep" aria-hidden="true"></span>
                    <small>@yield('subheading')</small>
                @endif
            </h1>


            {{-- SEARCH --}}
            <form class="search"
                  action="{{ trim($__env->yieldContent('search_action')) ?: route('data-wilayah.index') }}"
                  method="GET" role="search">

                <svg viewBox="0 0 24 24" width="18" height="18"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-3.5-3.5"/>
                </svg>

                <input type="search"
                       name="q"
                       value="{{ request('q') }}"
                       placeholder="{{ trim($__env->yieldContent('search_placeholder')) ?: 'Cari data wilayah/provinsi...' }}"
                       aria-label="Cari">

            </form>


            {{-- NOTIFIKASI --}}
            @php($notif = auth()->user()->notifikasi())

            <div class="notif" id="notif">

                <button type="button"
                        class="icon-btn notif-btn"
                        id="notifBtn"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="notifPanel"
                        aria-label="Notifikasi{{ $notif->count() ? ' (' . $notif->count() . ' baru)' : '' }}">

                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 9a6 6 0 10-12 0c0 7-3 8-3 8h18s-3-1-3-8"/>
                        <path d="M13.7 21a2 2 0 01-3.4 0"/>
                    </svg>

                    @if ($notif->count())
                        <span class="notif-dot">{{ $notif->count() }}</span>
                    @endif

                </button>


                <div class="notif-panel" id="notifPanel" hidden>

                    <header>Notifikasi</header>

                    @forelse ($notif as $n)

                        <a class="notif-item" href="{{ $n['url'] }}">
                            <strong>{{ $n['judul'] }}</strong>
                            <span>{{ $n['isi'] }}</span>
                            <small>{{ $n['waktu'] }}</small>
                        </a>

                    @empty

                        <p class="notif-empty">Belum ada notifikasi.</p>

                    @endforelse

                </div>

            </div>


            <span class="lang">ID</span>

        </header>


        {{-- HALAMAN --}}
        <main class="content">

            @if (session('success'))
                <div class="alert alert-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')

        </main>

    </div>

</div>


{{-- JAVASCRIPT --}}
<script src="{{ asset('js/datin.js') }}" defer></script>

</body>
</html>
