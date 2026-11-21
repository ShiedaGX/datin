
@extends('layouts.app')

@section('title', 'Dashboard Konektivitas Regional')
@section('heading', 'Dashboard')

@section('content')

<style>
/* ==========================================
   DASHBOARD INTRAM
   Seluruh CSS menggunakan prefix intram-
   agar tidak bentrok dengan layout utama.
========================================== */

.intram-dashboard {
    --db-navy: #153452;
    --db-teal: #087f83;
    --db-text: #18324d;
    --db-muted: #73859a;
    --db-border: #e5edf4;
    --db-surface: #ffffff;
    color: var(--db-text);
}

.intram-dashboard * {
    box-sizing: border-box;
}

/* HEADER */

.intram-dashboard .intram-hero {
    position: relative;
    overflow: hidden;
    padding: 22px 30px 24px;
    margin-bottom: 24px;
    border-radius: 18px;
    background: linear-gradient(
        110deg,
        #142d4b 0%,
        #1b4562 65%,
        #176d79 100%
    );
    color: #ffffff;
}

.intram-dashboard .intram-hero::before {
    content: "";
    position: absolute;
    width: 320px;
    height: 320px;
    top: -235px;
    right: -80px;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 50%;
    box-shadow:
        0 0 0 65px rgba(255,255,255,.025),
        0 0 0 130px rgba(255,255,255,.018);
    pointer-events: none;
}

.intram-dashboard .intram-hero-top {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 9px;
}

.intram-dashboard .intram-eyebrow {
    color: #86ece5;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.intram-dashboard .intram-logos {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.intram-dashboard .intram-logo {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    border: 1px solid rgba(255,255,255,.17);
    border-radius: 9px;
    background: rgba(255,255,255,.12);
}

.intram-dashboard .intram-logo img {
    display: block;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.intram-dashboard .intram-hero-title {
    position: relative;
    z-index: 1;
    margin: 0 0 10px;
    color: #ffffff;
    font-size: clamp(24px, 2.5vw, 32px);
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -.8px;
}

.intram-dashboard .intram-hero-description {
    position: relative;
    z-index: 1;
    display: block;
    width: 100%;
    max-width: none;
    margin: 0;
    color: #e3edf5;
    font-size: 14px;
    line-height: 1.6;
}

.intram-dashboard .intram-hero-tags {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 18px;
}

.intram-dashboard .intram-hero-tags span {
    display: inline-flex;
    align-items: center;
    padding: 7px 12px;
    border: 1px solid rgba(255,255,255,.16);
    border-radius: 30px;
    background: rgba(255,255,255,.09);
    color: #ffffff;
    font-size: 11px;
    font-weight: 600;
}

/* KPI */

.intram-dashboard .intram-kpis {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.intram-dashboard .intram-kpi {
    min-width: 0;
    padding: 22px;
    border: 1px solid var(--db-border);
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 5px 20px rgba(19,49,79,.035);
}

.intram-dashboard .intram-kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}

.intram-dashboard .intram-kpi-label {
    color: var(--db-muted);
    font-size: 13px;
    font-weight: 600;
}

.intram-dashboard .intram-kpi-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #e8f5f4;
    color: #087f83;
}

.intram-dashboard .intram-kpi-icon svg {
    width: 22px;
    height: 22px;
}

.intram-dashboard .intram-kpi-value {
    display: block;
    margin-top: 10px;
    color: #18324d;
    font-size: clamp(27px, 2.5vw, 36px);
    font-weight: 800;
    letter-spacing: -1px;
    line-height: 1.2;
}

.intram-dashboard .intram-kpi-foot {
    margin-top: 9px;
    color: #8796a8;
    font-size: 12px;
    line-height: 1.5;
}

/* CHART CARDS */

.intram-dashboard .intram-chart-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
    gap: 18px;
    margin-bottom: 22px;
}

.intram-dashboard .intram-card {
    min-width: 0;
    padding: 24px;
    border: 1px solid var(--db-border);
    border-radius: 17px;
    background: #ffffff;
    box-shadow: 0 5px 20px rgba(19,49,79,.035);
}

.intram-dashboard .intram-card-heading {
    margin-bottom: 22px;
}

.intram-dashboard .intram-card-heading h2 {
    margin: 0 0 7px;
    color: #1a344e;
    font-size: 17px;
    font-weight: 800;
}

.intram-dashboard .intram-card-heading p {
    margin: 0;
    color: #8392a4;
    font-size: 12px;
    line-height: 1.6;
}

/* DISTRIBUSI WILAYAH */

.intram-dashboard .intram-bars {
    display: flex;
    flex-direction: column;
    gap: 17px;
}

.intram-dashboard .intram-bar-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
}

.intram-dashboard .intram-bar-name {
    color: #40566e;
    font-size: 13px;
    font-weight: 600;
}

.intram-dashboard .intram-bar-count {
    color: #18324d;
    font-size: 13px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}

.intram-dashboard .intram-bar-track {
    height: 9px;
    overflow: hidden;
    border-radius: 20px;
    background: #edf2f7;
}

.intram-dashboard .intram-bar-fill {
    height: 100%;
    border-radius: 20px;
    background: #0d9488;
}

/* DONUT */

.intram-dashboard .intram-donut-layout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 25px;
}

.intram-dashboard .intram-donut-chart {
    position: relative;
    flex-shrink: 0;
    width: 205px;
    height: 205px;
}

.intram-dashboard .intram-donut-chart svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.intram-dashboard .intram-donut-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.intram-dashboard .intram-donut-center strong {
    color: #193550;
    font-size: 31px;
    font-weight: 800;
}

.intram-dashboard .intram-donut-center span {
    color: #8796a8;
    font-size: 11px;
}

.intram-dashboard .intram-legend {
    display: flex;
    flex-direction: column;
    gap: 13px;
    min-width: 0;
}

.intram-dashboard .intram-legend-item {
    display: flex;
    align-items: center;
    gap: 9px;
}

.intram-dashboard .intram-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.intram-dashboard .intram-legend-label {
    color: #53677e;
    font-size: 12px;
}

.intram-dashboard .intram-legend-percent {
    margin-left: auto;
    color: #18324d;
    font-size: 12px;
    font-weight: 800;
}

/* TABEL */

.intram-dashboard .intram-table-card {
    overflow: hidden;
    padding: 0;
}

.intram-dashboard .intram-table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 23px 24px;
}

.intram-dashboard .intram-table-header h2 {
    margin: 0 0 6px;
    color: #1a344e;
    font-size: 17px;
    font-weight: 800;
}

.intram-dashboard .intram-table-header p {
    margin: 0;
    color: #8392a4;
    font-size: 12px;
}

.intram-dashboard .intram-table-link {
    color: #087f83;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.intram-dashboard .intram-table-scroll {
    width: 100%;
    overflow-x: auto;
}

.intram-dashboard .intram-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.intram-dashboard .intram-table th {
    padding: 14px 22px;
    background: #f6f9fc;
    color: #7c8da0;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .35px;
    white-space: nowrap;
}

.intram-dashboard .intram-table td {
    padding: 17px 22px;
    border-top: 1px solid #edf1f5;
    color: #4b6076;
    font-size: 13px;
    vertical-align: middle;
}

.intram-dashboard .intram-table tbody tr:hover {
    background: #fafcfe;
}

.intram-dashboard .intram-table .intram-region {
    color: #18324d;
    font-weight: 700;
}

.intram-dashboard .intram-status {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 20px;
    background: #e9f5f2;
    color: #087f83;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

/* EMPTY STATE */

.intram-dashboard .intram-empty {
    padding: 25px 15px;
    color: #8392a4;
    font-size: 13px;
    text-align: center;
}

/* RESPONSIVE */

@media (max-width: 1100px) {
    .intram-dashboard .intram-kpis {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .intram-dashboard .intram-chart-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .intram-dashboard .intram-hero {
        padding: 20px;
        border-radius: 15px;
    }

    .intram-dashboard .intram-logo {
        width: 36px;
        height: 36px;
    }

    .intram-dashboard .intram-hero-title {
        font-size: 25px;
    }

    .intram-dashboard .intram-hero-description {
        font-size: 13px;
    }

    .intram-dashboard .intram-hero-tags {
        gap: 7px;
    }

    .intram-dashboard .intram-hero-tags span {
        font-size: 10px;
    }

    .intram-dashboard .intram-donut-layout {
        flex-wrap: wrap;
    }
}

@media (max-width: 520px) {
    .intram-dashboard .intram-kpis {
        grid-template-columns: 1fr;
    }

    .intram-dashboard .intram-kpi {
        padding: 19px;
    }

    .intram-dashboard .intram-card {
        padding: 19px;
    }

    .intram-dashboard .intram-table-card {
        padding: 0;
    }

    .intram-dashboard .intram-table-header {
        padding: 20px;
        flex-wrap: wrap;
    }

    .intram-dashboard .intram-donut-chart {
        width: 190px;
        height: 190px;
    }
}
</style>

<div class="intram-dashboard">

    {{-- =====================================
         HEADER DASHBOARD
    ===================================== --}}
    <section class="intram-hero">

        <div class="intram-hero-top">

            <div class="intram-eyebrow">
                SISTEM INFORMASI REGIONAL · INTRAM
            </div>

            <div class="intram-logos">

                <div class="intram-logo">
                    <img
                        src="{{ asset('images/logo-kemenhub.png') }}"
                        alt="Logo Kementerian Perhubungan"
                        title="Kementerian Perhubungan"
                    >
                </div>

                <div class="intram-logo">
                    <img
                        src="{{ asset('images/logo-intram.png') }}"
                        alt="Logo INTRAM"
                        title="INTRAM"
                    >
                </div>

            </div>

        </div>

        <h1 class="intram-hero-title">
            Dashboard Konektivitas Regional
        </h1>

        <p class="intram-hero-description">
            Ringkasan cakupan administrasi, potensi daerah, dan kebutuhan konektivitas untuk mendukung analisis transportasi bersubsidi.
        </p>

        <div class="intram-hero-tags">
            <span>Data wilayah terintegrasi</span>
            <span>Analisis konektivitas regional</span>
            <span>Transportasi bersubsidi</span>
        </div>

    </section>

    {{-- =====================================
         KPI
    ===================================== --}}
    <section class="intram-kpis">

        {{-- PROVINSI --}}
        <article class="intram-kpi">
            <div class="intram-kpi-top">
                <span class="intram-kpi-label">Total Provinsi</span>

                <span class="intram-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>
                    </svg>
                </span>
            </div>

            <strong class="intram-kpi-value">
                {{ number_format($kpi['provinsi'] ?? 0, 0, ',', '.') }}
            </strong>

            <div class="intram-kpi-foot">
                Provinsi dalam cakupan data
            </div>
        </article>

        {{-- WILAYAH --}}
        <article class="intram-kpi">
            <div class="intram-kpi-top">
                <span class="intram-kpi-label">Total Wilayah Kerja</span>

                <span class="intram-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3z"/>
                        <path d="M9 3v15M15 6v15"/>
                    </svg>
                </span>
            </div>

            <strong class="intram-kpi-value">
                {{ number_format($kpi['wilayah'] ?? 0, 0, ',', '.') }}
            </strong>

            <div class="intram-kpi-foot">
                Wilayah kerja terpetakan
            </div>
        </article>

        {{-- KABUPATEN / KOTA --}}
        <article class="intram-kpi">
            <div class="intram-kpi-top">
                <span class="intram-kpi-label">Total Kabupaten / Kota</span>

                <span class="intram-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="7" width="7" height="14" rx="1"/>
                        <rect x="11" y="3" width="9" height="18" rx="1"/>
                        <path d="M7 11h1M7 15h1M15 7h1M15 11h1M15 15h1"/>
                    </svg>
                </span>
            </div>

            <strong class="intram-kpi-value">
                {{ number_format($kpi['kab_kota'] ?? 0, 0, ',', '.') }}
            </strong>

            <div class="intram-kpi-foot">
                Kabupaten dan kota tercatat
            </div>
        </article>

        {{-- SEKTOR --}}
        <article class="intram-kpi">
            <div class="intram-kpi-top">
                <span class="intram-kpi-label">Sektor Potensi</span>

                <span class="intram-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 20V11M10 20V5M16 20v-8M22 20V8"/>
                        <path d="M2 20h20"/>
                    </svg>
                </span>
            </div>

            <strong class="intram-kpi-value">
                {{ number_format($kpi['sektor'] ?? 0, 0, ',', '.') }}
            </strong>

            <div class="intram-kpi-foot">
                Sektor potensi daerah
            </div>
        </article>

    </section>

    {{-- =====================================
         GRAFIK
    ===================================== --}}
    <div class="intram-chart-grid">

        {{-- DISTRIBUSI WILAYAH --}}
        <section class="intram-card">

            <div class="intram-card-heading">
                <h2>Distribusi per Wilayah</h2>
                <p>Sebaran data berdasarkan wilayah kerja.</p>
            </div>

            <div class="intram-bars">

                @forelse ($distribusi as $item)
                    <div class="intram-bar-row">

                        <div class="intram-bar-head">
                            <span class="intram-bar-name">
                                {{ $item['nama'] }}
                            </span>

                            <strong class="intram-bar-count">
                                {{ number_format($item['jumlah'], 0, ',', '.') }}
                            </strong>
                        </div>

                        <div class="intram-bar-track">
                            <div
                                class="intram-bar-fill"
                                style="
                                    width: {{ max(0, min(100, (float) $item['lebar'])) }}%;
                                    background-color: {{ $item['warna'] }};
                                "
                            ></div>
                        </div>

                    </div>
                @empty
                    <div class="intram-empty">
                        Belum ada data distribusi wilayah.
                    </div>
                @endforelse

            </div>

        </section>

        {{-- KLASIFIKASI WILAYAH --}}
        <section class="intram-card">

            <div class="intram-card-heading">
                <h2>Klasifikasi Wilayah</h2>
                <p>Komposisi klasifikasi wilayah yang tercatat.</p>
            </div>

            <div class="intram-donut-layout">

                <div class="intram-donut-chart">

                    <svg viewBox="0 0 200 200" aria-hidden="true">

                        <circle
                            cx="100"
                            cy="100"
                            r="75"
                            fill="none"
                            stroke="#edf2f7"
                            stroke-width="25"
                        />

                        @foreach ($donat as $item)
                            <circle
                                cx="100"
                                cy="100"
                                r="75"
                                fill="none"
                                stroke="{{ $item['warna'] }}"
                                stroke-width="25"
                                stroke-dasharray="{{ $item['arc'] }} {{ 471.24 - (float) $item['arc'] }}"
                                stroke-dashoffset="-{{ $item['awal'] }}"
                            />
                        @endforeach

                    </svg>

                    <div class="intram-donut-center">
                        <strong>
                            {{ number_format($totalPemda ?? 0, 0, ',', '.') }}
                        </strong>
                        <span>Total Data</span>
                    </div>

                </div>

                <div class="intram-legend">

                    @foreach ($donat as $item)
                        <div class="intram-legend-item">

                            <span
                                class="intram-legend-dot"
                                style="background-color: {{ $item['warna'] }}"
                            ></span>

                            <span class="intram-legend-label">
                                {{ $item['label'] }}
                            </span>

                            <strong class="intram-legend-percent">
                                {{ $item['persen'] }}%
                            </strong>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>

    </div>

    {{-- =====================================
         DATA TERBARU
    ===================================== --}}
    <section class="intram-card intram-table-card">

        <div class="intram-table-header">

            <div>
                <h2>Data Wilayah Terbaru</h2>
                <p>Ringkasan data wilayah yang terakhir tercatat.</p>
            </div>

            <a
                href="{{ route('data-wilayah.index') }}"
                class="intram-table-link"
            >
                Lihat Semua Data &rarr;
            </a>

        </div>

        <div class="intram-table-scroll">

            <table class="intram-table">

                <thead>
                    <tr>
                        <th>Provinsi</th>
                        <th>Kabupaten / Kota</th>
                        <th>Wilayah</th>
                        <th>Klasifikasi</th>
                        <th>Rute Tujuan</th>
                        <th>Sektor Potensi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($terbaru as $row)
                        <tr>

                            <td>
                                {{ $row->kabKota?->provinsi?->nama_provinsi ?? '-' }}
                            </td>

                            <td class="intram-region">
                                {{ $row->kabKota?->nama_kab_kota ?? '-' }}
                            </td>

                            <td>
                                {{ $row->kabKota?->wilayah?->nama_wilayah ?? '-' }}
                            </td>

                            <td>
                                <span class="intram-status">
                                    {{ $row->klasifikasi_label ?? '-' }}
                                </span>
                            </td>

                            <td>
                                {{ number_format($row->jumlah_tujuan ?? 0, 0, ',', '.') }}
                            </td>

                            <td>
                                {{ number_format($row->jumlah_sektor ?? 0, 0, ',', '.') }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="intram-empty">
                                Belum ada data wilayah yang tercatat.
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

</div>

@endsection
