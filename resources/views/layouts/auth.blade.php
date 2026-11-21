
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1830">

    <title>@yield('title', 'Masuk') | INTRAM - Transportasi Bersubsidi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/login.css') }}?v=5">
</head>

<body class="auth-page">

<div class="auth-shell">

    {{-- PANEL KIRI --}}
    <aside class="auth-hero">

        <div class="hero-glow hero-glow-one"></div>
        <div class="hero-glow hero-glow-two"></div>

        <div class="hero-content">

            {{-- IDENTITAS INSTANSI --}}
            <div class="institution">

                <div class="institution-logos">

                    <div class="institution-logo">
                        <img
                            src="{{ asset('images/logo-kemenhub.png') }}"
                            alt="Logo Kementerian Perhubungan"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                        >
                        <span class="logo-fallback" style="display:none">K</span>
                    </div>

                    <div class="institution-divider"></div>

                    <div class="institution-logo">
                        <img
                            src="{{ asset('images/logo-intram.png') }}"
                            alt="Logo INTRAM"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                        >
                        <span class="logo-fallback" style="display:none">I</span>
                    </div>

                </div>

                <div class="institution-name">
                    <strong>KEMENTERIAN PERHUBUNGAN</strong>
                    <span>
                        Direktorat Jenderal Integrasi Transportasi dan Multimoda
                    </span>
                </div>

            </div>

            {{-- KONTEN UTAMA --}}
            <div class="hero-main">

                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    SISTEM INFORMASI NASIONAL
                </div>

                <h1>
                    Menghubungkan
                    <span>Wilayah.</span>
                    Memperkuat
                    <span>Konektivitas.</span>
                </h1>

                <p class="hero-description">
                    Platform terintegrasi untuk pengelolaan data wilayah,
                    identifikasi potensi daerah, dan kebutuhan konektivitas
                    transportasi bersubsidi di Indonesia.
                </p>

                {{-- ILUSTRASI JARINGAN --}}
                <div class="network-visual">

                    <svg viewBox="0 0 600 250" fill="none" aria-hidden="true">

                        <defs>
                            <linearGradient
                                id="lineGradient"
                                x1="0"
                                y1="0"
                                x2="600"
                                y2="250"
                            >
                                <stop stop-color="#38BDF8" stop-opacity=".15"/>
                                <stop offset=".5" stop-color="#2DD4BF" stop-opacity=".8"/>
                                <stop offset="1" stop-color="#38BDF8" stop-opacity=".2"/>
                            </linearGradient>
                        </defs>

                        <g stroke="url(#lineGradient)" stroke-width="1.5">
                            <path d="M40 185L130 115L230 150L325 70L430 110L555 40"/>
                            <path d="M130 115L180 45L325 70"/>
                            <path d="M230 150L315 215L450 185L555 40"/>
                            <path d="M40 185L115 220L315 215"/>
                            <path d="M325 70L450 185"/>
                            <path d="M180 45L430 110"/>
                        </g>

                        <g fill="#5EEAD4">
                            <circle cx="40" cy="185" r="5" opacity=".5"/>
                            <circle cx="130" cy="115" r="7"/>
                            <circle cx="230" cy="150" r="5" opacity=".7"/>
                            <circle cx="325" cy="70" r="8"/>
                            <circle cx="430" cy="110" r="6"/>
                            <circle cx="555" cy="40" r="5"/>
                            <circle cx="180" cy="45" r="4" opacity=".6"/>
                            <circle cx="315" cy="215" r="6"/>
                            <circle cx="450" cy="185" r="5"/>
                            <circle cx="115" cy="220" r="4" opacity=".5"/>
                        </g>

                        <g fill="#5EEAD4" opacity=".1">
                            <circle cx="130" cy="115" r="20"/>
                            <circle cx="325" cy="70" r="25"/>
                            <circle cx="315" cy="215" r="20"/>
                        </g>

                    </svg>

                    <div class="network-caption">
                        <span class="network-indicator"></span>
                        INTEGRASI DATA TRANSPORTASI REGIONAL
                    </div>

                </div>

                {{-- STATISTIK --}}
                <div class="hero-stats">

                    <div class="hero-stat">
                        <strong>
                            {{ number_format($stats['provinsi'] ?? 0, 0, ',', '.') }}
                        </strong>
                        <span>
                            Provinsi<br>tercakup
                        </span>
                    </div>

                    <div class="hero-stat">
                        <strong>
                            {{ number_format($stats['wilayah'] ?? 0, 0, ',', '.') }}
                        </strong>
                        <span>
                            Wilayah<br>terpetakan
                        </span>
                    </div>

                    <div class="hero-stat">
                        <strong>
                            {{ $stats['kelengkapan'] ?? 0 }}%
                        </strong>
                        <span>
                            Kelengkapan<br>data
                        </span>
                    </div>

                </div>

            </div>

            {{-- FOOTER KIRI --}}
            <footer class="hero-footer">
                <span>
                    INTRAM · Sistem Informasi Transportasi Bersubsidi
                </span>

                <span>
                    © {{ date('Y') }} Kementerian Perhubungan
                </span>
            </footer>

        </div>

    </aside>

    {{-- PANEL KANAN --}}
    <main class="auth-panel">

        <div class="panel-inner">

            {{-- HEADER KANAN --}}
            <header class="panel-top">

                <div class="portal-brand">

                    <span class="portal-mark">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="5" cy="17" r="2"/>
                            <circle cx="12" cy="7" r="2"/>
                            <circle cx="19" cy="15" r="2"/>
                            <path d="M6.2 15.4L10.8 8.7M13.6 8.1L17.5 13.3"/>
                        </svg>
                    </span>

                    <span>
                        <strong>INTRAM</strong>
                        <small>
                            Sistem Informasi Transportasi Bersubsidi
                        </small>
                    </span>

                </div>

                <span class="language-pill">
                    ID · Indonesia
                </span>

            </header>

            {{-- FORM LOGIN --}}
            <div class="panel-body">
                @yield('content')
            </div>

            {{-- FOOTER KANAN --}}
            <footer class="panel-footer">

                <span>
                    © {{ date('Y') }} Kementerian Perhubungan
                </span>

                <span>
                    Direktorat Jenderal Integrasi Transportasi dan Multimoda
                </span>

            </footer>

        </div>

    </main>

</div>

</body>
</html>
