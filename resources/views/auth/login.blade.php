<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | Subsidi Transportasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<div class="shell">

    {{-- ===== Panel kiri (branding) ===== --}}
    <aside class="hero">
        <a class="hero-brand" href="{{ route('login') }}">
            <svg class="logo" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true">
                <rect width="40" height="40" rx="10" fill="#0f8678"/>
                <path d="M13 27L27 12" stroke="#fff" stroke-opacity=".35" stroke-width="1.6"/>
                <circle cx="27" cy="12" r="3.2" fill="none" stroke="#fff" stroke-width="1.8"/>
                <circle cx="13" cy="27" r="3.2" fill="none" stroke="#fff" stroke-width="1.8"/>
            </svg>
            <span>
                <strong>Subsidi transportasi</strong>
                <small>TRANSPORTASI BERSUBSIDI</small>
            </span>
        </a>

        <div class="hero-body">
            <p class="eyebrow"><i></i>PLATFORM DATA NASIONAL</p>
            <h2>Data terhubung untuk layanan transportasi yang lebih tepat.</h2>
            <p class="hero-sub">Satu portal untuk mengelola data wilayah dan pengajuan subsidi transportasi.</p>

            <div class="network">
                <p>JARINGAN LAYANAN TERINTEGRASI</p>
                <svg viewBox="0 0 520 210" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                    <g stroke="#2dd4bf" stroke-opacity=".28" stroke-width="1.4" fill="none">
                        <path d="M60 150L150 90L250 120L330 60L450 100"/>
                        <path d="M150 90L210 40L330 60"/>
                        <path d="M250 120L300 175L420 160L450 100"/>
                        <path d="M60 150L120 185L300 175"/>
                    </g>
                    <g fill="#2dd4bf">
                        <circle cx="60" cy="150" r="5" fill-opacity=".55"/>
                        <circle cx="150" cy="90" r="7" fill-opacity=".9"/>
                        <circle cx="250" cy="120" r="5" fill-opacity=".55"/>
                        <circle cx="330" cy="60" r="8" fill-opacity=".9"/>
                        <circle cx="450" cy="100" r="5" fill-opacity=".55"/>
                        <circle cx="210" cy="40" r="4" fill-opacity=".45"/>
                        <circle cx="300" cy="175" r="6" fill-opacity=".75"/>
                        <circle cx="420" cy="160" r="4" fill-opacity=".45"/>
                        <circle cx="120" cy="185" r="4" fill-opacity=".45"/>
                    </g>
                </svg>
                <span class="pill"><i></i>Sistem terhubung</span>
            </div>

            <dl class="stats">
                <div><dt>{{ number_format($stats['provinsi'], 0, ',', '.') }}</dt><dd>provinsi tercakup</dd></div>
                <div><dt>{{ number_format($stats['wilayah'], 0, ',', '.') }}</dt><dd>wilayah terpetakan</dd></div>
                <div><dt>{{ $stats['kelengkapan'] }}%</dt><dd>kelengkapan data</dd></div>
            </dl>
        </div>

        <p class="hero-foot">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/></svg>
            Akses terbatas untuk instansi dan mitra terverifikasi
        </p>
    </aside>

    {{-- ===== Panel kanan (form) ===== --}}
    <main class="panel">
        <header class="panel-top">
            <span class="portal"><i></i>Portal Data Transportasi Bersubsidi</span>
            <span>Bahasa Indonesia</span>
        </header>

        <form class="login-form" id="loginForm" method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <p class="kicker">AKSES AKUN</p>
            <h1>Selamat datang</h1>
            <p class="lead">Masuk menggunakan akun instansi Anda untuk melanjutkan pengelolaan data.</p>

            @if ($errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <div class="field">
                <label for="email">Alamat email</label>
                <div class="input @error('email') is-invalid @enderror">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@instansi.go.id" required autofocus autocomplete="email">
                </div>
            </div>

            <div class="field">
                <label for="password">Kata sandi</label>
                <div class="input">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/><circle cx="12" cy="15.5" r="1"/></svg>
                    <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required autocomplete="current-password">
                    <button type="button" class="eye" id="togglePw" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="row">
                <label class="check"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span></span>Ingat saya</label>
                <a class="link" href="#">Lupa kata sandi?</a>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <span>Masuk</span>
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>

            <p class="register">Belum memiliki akun? <a class="link" href="#">Daftar akun instansi</a></p>

            <p class="secure">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/><circle cx="12" cy="15.5" r="1"/></svg>
                <span>Koneksi terenkripsi. Informasi akun Anda dilindungi sesuai kebijakan keamanan data pemerintah.</span>
            </p>
        </form>

        <footer class="panel-foot">Butuh bantuan akses? <a class="link" href="#">Hubungi administrator</a></footer>
    </main>
</div>
<script src="{{ asset('js/login.js') }}" defer></script>
</body>
</html>
