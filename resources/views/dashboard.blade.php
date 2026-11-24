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

/* ANALISIS KONEKTIVITAS — tambahan, tidak mengganti KPI lama */
.intram-dashboard .intram-connect-section{margin:26px 0 22px}
.intram-dashboard .intram-connect-title{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;flex-wrap:wrap;margin:0 0 16px}
.intram-dashboard .intram-connect-title h2{font-size:21px;font-weight:800;color:#18324d;margin:0 0 5px}
.intram-dashboard .intram-connect-title p{font-size:12px;color:#8392a4;margin:0;line-height:1.6}
.intram-dashboard .intram-connect-pill{display:inline-flex;align-items:center;padding:7px 12px;background:#e8f5f4;border:1px solid #cde8e4;color:#087f83;border-radius:30px;font-size:11px;font-weight:750}
.intram-dashboard .intram-connect-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
.intram-dashboard .intram-connect-kpi{border:1px solid #e5edf4;background:#fff;border-radius:14px;padding:19px;box-shadow:0 5px 20px rgba(19,49,79,.035)}
.intram-dashboard .intram-connect-kpi span{display:block;color:#73859a;font-size:12px;font-weight:650}
.intram-dashboard .intram-connect-kpi strong{display:block;font-size:29px;font-weight:800;color:#18324d;margin:8px 0 5px;line-height:1.2}
.intram-dashboard .intram-connect-kpi small{font-size:11px;color:#8796a8;line-height:1.5}
.intram-dashboard .intram-connect-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.intram-dashboard .intram-connect-bar{height:10px;border-radius:20px;background:#edf2f7;overflow:hidden;margin-top:8px}
.intram-dashboard .intram-connect-fill{height:100%;border-radius:20px;background:#087f83}
.intram-dashboard .intram-connect-row{margin-bottom:16px}
.intram-dashboard .intram-connect-row:last-child{margin-bottom:0}
.intram-dashboard .intram-connect-row-head{display:flex;align-items:center;justify-content:space-between;gap:12px;color:#40566e;font-size:12px;font-weight:650}
.intram-dashboard .intram-connect-row-head strong{color:#18324d;font-variant-numeric:tabular-nums;white-space:nowrap}
.intram-dashboard .intram-connect-note{margin-top:15px;font-size:11px;color:#8796a8;line-height:1.65}
.intram-dashboard .intram-connect-kpis{grid-template-columns:repeat(auto-fit,minmax(185px,1fr))}
.intram-dashboard .intram-connect-kpi strong em{font-style:normal;font-size:14px;font-weight:700;color:#73859a}
.intram-dashboard .intram-insight{border:1px solid #d6e9e7;background:linear-gradient(135deg,#f1faf9,#f8fbff);border-radius:14px;padding:15px 19px;margin-bottom:18px;font-size:12.5px;color:#40566e}
.intram-dashboard .intram-insight>strong{display:block;color:#0b6f73;font-size:12px;letter-spacing:.04em;text-transform:uppercase;margin-bottom:7px}
.intram-dashboard .intram-insight ul{margin:0;padding-left:18px;line-height:1.85}
.intram-dashboard .intram-insight b{color:#18324d}
.intram-dashboard .intram-muted{color:#8796a8;font-weight:600;font-size:11px}
.intram-dashboard .intram-cdonut{display:flex;align-items:center;gap:26px;flex-wrap:wrap}
.intram-dashboard .intram-cdonut-chart{position:relative;width:190px;height:190px;flex:0 0 190px}
.intram-dashboard .intram-cdonut-chart svg{width:100%;height:100%;display:block}
.intram-dashboard .intram-cdonut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none}
.intram-dashboard .intram-cdonut-center strong{font-size:26px;font-weight:800;color:#18324d;line-height:1.1}
.intram-dashboard .intram-cdonut-center span{font-size:11px;color:#8796a8}
.intram-dashboard .intram-cdonut-legend{flex:1 1 170px;min-width:0}
.intram-dashboard .intram-cdonut-item{display:flex;align-items:center;gap:9px;padding:9px 0;border-bottom:1px solid #eef3f8;font-size:13px;color:#40566e}
.intram-dashboard .intram-cdonut-item:last-child{border-bottom:0}
.intram-dashboard .intram-cdonut-dot{width:11px;height:11px;border-radius:4px;flex:0 0 11px}
.intram-dashboard .intram-cdonut-name{flex:1;font-weight:650}
.intram-dashboard .intram-cdonut-val{font-weight:800;color:#18324d;font-variant-numeric:tabular-nums;white-space:nowrap}
.intram-dashboard .intram-cdonut-val small{font-weight:600;color:#8796a8;font-size:11px}
.intram-dashboard .intram-cols{display:flex;align-items:stretch;gap:12px;height:230px;padding-top:6px}
.intram-dashboard .intram-col{flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;gap:4px}
.intram-dashboard .intram-col-val{font-size:13px;font-weight:800;color:#18324d;font-variant-numeric:tabular-nums}
.intram-dashboard .intram-col-track{flex:1;width:100%;max-width:62px;background:#f1f5f9;border-radius:9px 9px 4px 4px;display:flex;align-items:flex-end;overflow:hidden}
.intram-dashboard .intram-col-fill{width:100%;background:linear-gradient(180deg,#14b8a6,#087f83);border-radius:9px 9px 0 0}
.intram-dashboard .intram-col-label{font-size:11px;font-weight:650;color:#40566e;text-align:center;line-height:1.3}
.intram-dashboard .intram-col-pct{font-size:10.5px;color:#8796a8}
@media(max-width:520px){.intram-dashboard .intram-cdonut{justify-content:center}.intram-dashboard .intram-cols{gap:6px}.intram-dashboard .intram-col-label{font-size:9.5px}}
@media(max-width:1100px){.intram-dashboard .intram-connect-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:768px){.intram-dashboard .intram-connect-grid{grid-template-columns:1fr}}
@media(max-width:520px){.intram-dashboard .intram-connect-kpis{grid-template-columns:1fr 1fr;gap:9px}.intram-dashboard .intram-connect-kpi{padding:13px}.intram-dashboard .intram-connect-kpi strong{font-size:24px}}

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
         ANALISIS KONEKTIVITAS (VERIFIKASI FINAL)
         KPI dan grafik dashboard sebelumnya tetap utuh.
    ===================================== --}}
    <section class="intram-connect-section" aria-labelledby="intram-connect-heading">
        <div class="intram-connect-title">
            <div>
                <h2 id="intram-connect-heading">Analisis Konektivitas Regional</h2>
                <p>Ringkasan hubungan kebutuhan angkutan bersubsidi yang dapat dipetakan sesuai hak akses akun.</p>
            </div>
            <span class="intram-connect-pill">Verifikasi Final</span>
        </div>

        @php
            $kv = $konektivitas;
            $warnaModaDari = static fn (string $nama): string => match (mb_strtolower($nama)) {
                'darat' => '#e67e22', 'laut' => '#1688dc', 'udara' => '#8b5cf6', 'kereta api' => '#159b68', default => '#64748b',
            };
            $fmt = static fn ($n, int $d = 0): string => number_format((float) $n, $d, ',', '.');
            $modaTop = $kv['moda'][0] ?? null;
            $asalTop = $kv['top_asal'][0] ?? null;
            $tujuanTop = $kv['top_tujuan'][0] ?? null;
            $kelasTop = collect($kv['kelas_jarak'])->sortByDesc('jumlah')->first();
            $maksKelas = max(1, (int) collect($kv['kelas_jarak'])->max('jumlah'));
        @endphp

        <div class="intram-connect-kpis">
            <article class="intram-connect-kpi">
                <span>Total Hubungan</span>
                <strong>{{ $fmt($kv['hubungan']) }}</strong>
                <small>Pasangan laporan dan tujuan</small>
            </article>
            <article class="intram-connect-kpi">
                <span>Daerah Asal</span>
                <strong>{{ $fmt($kv['asal']) }}</strong>
                <small>Kabupaten/kota asal unik</small>
            </article>
            <article class="intram-connect-kpi">
                <span>Simpul Tujuan</span>
                <strong>{{ $fmt($kv['tujuan']) }}</strong>
                <small>Kabupaten/kota tujuan unik</small>
            </article>
            <article class="intram-connect-kpi">
                <span>Wilayah Terhubung</span>
                <strong>{{ $fmt($kv['wilayah_unik']) }}</strong>
                <small>Gabungan asal dan tujuan unik</small>
            </article>
            <article class="intram-connect-kpi">
                <span>Rata-rata Jarak</span>
                <strong>{{ $fmt($kv['jarak_rata']) }}<em> km</em></strong>
                <small>Garis lurus, terjauh {{ $fmt($kv['jarak_maks']) }} km</small>
            </article>
            <article class="intram-connect-kpi">
                <span>Hubungan per Daerah</span>
                <strong>{{ $fmt($kv['rasio'], 1) }}</strong>
                <small>Rata-rata tujuan tiap daerah asal</small>
            </article>
        </div>

        @if ($kv['hubungan'] > 0)
            <div class="intram-insight">
                <strong>Temuan utama</strong>
                <ul>
                    @if ($modaTop)<li>Moda dominan: <b>{{ $modaTop['nama'] }}</b> ({{ $fmt($modaTop['persen'], 1) }}% dari seluruh hubungan).</li>@endif
                    @if ($asalTop)<li>Daerah asal teraktif: <b>{{ $asalTop['nama'] }}</b> dengan {{ $fmt($asalTop['jumlah']) }} hubungan.</li>@endif
                    @if ($tujuanTop)<li>Simpul tujuan terbanyak dituju: <b>{{ $tujuanTop['nama'] }}</b> ({{ $fmt($tujuanTop['jumlah']) }} hubungan).</li>@endif
                    @if ($kelasTop)<li>Sebagian besar hubungan berjarak <b>{{ $kelasTop['nama'] }}</b> ({{ $fmt($kelasTop['persen'], 1) }}%).</li>@endif
                </ul>
            </div>
        @endif

        <div class="intram-connect-grid">
            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Distribusi Moda Transportasi</h2>
                    <p>Proporsi hubungan menurut moda pada pengajuan final.</p>
                </div>
                @if ($kv['moda'])
                    @php $awalArc = 0.0; @endphp
                    <div class="intram-cdonut">
                        <div class="intram-cdonut-chart">
                            <svg viewBox="0 0 200 200" role="img" aria-label="Distribusi moda transportasi">
                                <circle cx="100" cy="100" r="75" fill="none" stroke="#edf2f7" stroke-width="26" />
                                @foreach ($kv['moda'] as $item)
                                    @php $arc = max(0.0, (float) $item['persen']); @endphp
                                    <circle cx="100" cy="100" r="75" fill="none" stroke="{{ $warnaModaDari($item['nama']) }}" stroke-width="26"
                                        pathLength="100" stroke-dasharray="{{ $arc }} {{ 100 - $arc }}" stroke-dashoffset="-{{ $awalArc }}"
                                        transform="rotate(-90 100 100)"><title>{{ $item['nama'] }}: {{ $fmt($item['jumlah']) }} hubungan</title></circle>
                                    @php $awalArc += $arc; @endphp
                                @endforeach
                            </svg>
                            <div class="intram-cdonut-center"><strong>{{ $fmt($kv['hubungan']) }}</strong><span>Hubungan</span></div>
                        </div>
                        <div class="intram-cdonut-legend">
                            @foreach ($kv['moda'] as $item)
                                <div class="intram-cdonut-item">
                                    <span class="intram-cdonut-dot" style="background:{{ $warnaModaDari($item['nama']) }}"></span>
                                    <span class="intram-cdonut-name">{{ $item['nama'] }}</span>
                                    <span class="intram-cdonut-val">{{ $fmt($item['jumlah']) }}<small> · {{ $fmt($item['persen'], 1) }}%</small></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="intram-empty">Belum ada hubungan konektivitas Verifikasi Final yang dapat dipetakan.</div>
                @endif
            </article>

            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Sebaran Jarak Hubungan</h2>
                    <p>Jumlah hubungan menurut kelas jarak garis lurus asal–tujuan.</p>
                </div>
                @if ($kv['hubungan'] > 0)
                    <div class="intram-cols" role="img" aria-label="Sebaran jarak hubungan">
                        @foreach ($kv['kelas_jarak'] as $item)
                            <div class="intram-col">
                                <span class="intram-col-val">{{ $fmt($item['jumlah']) }}</span>
                                <div class="intram-col-track"><div class="intram-col-fill" style="height:{{ $item['jumlah'] > 0 ? max(3, $item['jumlah'] / $maksKelas * 100) : 0 }}%"></div></div>
                                <span class="intram-col-label">{{ $item['nama'] }}</span>
                                <span class="intram-col-pct">{{ $fmt($item['persen'], 1) }}%</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="intram-empty">Belum ada data jarak yang dapat ditampilkan.</div>
                @endif
            </article>
        </div>

        <div class="intram-connect-grid" style="margin-top:18px">
            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Top 5 Daerah Asal</h2>
                    <p>Daerah dengan jumlah hubungan konektivitas terbanyak.</p>
                </div>
                @php $maksAsal = max(1, $kv['top_asal'][0]['jumlah'] ?? 0); @endphp
                @forelse ($kv['top_asal'] as $index => $item)
                    <div class="intram-connect-row">
                        <div class="intram-connect-row-head"><span>{{ $index + 1 }}. {{ $item['nama'] }}</span><strong>{{ $fmt($item['jumlah']) }}</strong></div>
                        <div class="intram-connect-bar"><div class="intram-connect-fill" style="width:{{ max(0, min(100, $item['jumlah'] / $maksAsal * 100)) }}%"></div></div>
                    </div>
                @empty
                    <div class="intram-empty">Belum ada data daerah asal yang dapat ditampilkan.</div>
                @endforelse
            </article>

            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Top 5 Simpul Tujuan</h2>
                    <p>Daerah yang paling banyak menjadi tujuan hubungan.</p>
                </div>
                @php $maksTujuan = max(1, $kv['top_tujuan'][0]['jumlah'] ?? 0); @endphp
                @forelse ($kv['top_tujuan'] as $index => $item)
                    <div class="intram-connect-row">
                        <div class="intram-connect-row-head"><span>{{ $index + 1 }}. {{ $item['nama'] }}</span><strong>{{ $fmt($item['jumlah']) }}</strong></div>
                        <div class="intram-connect-bar"><div class="intram-connect-fill" style="width:{{ max(0, min(100, $item['jumlah'] / $maksTujuan * 100)) }}%;background:#7c3aed"></div></div>
                    </div>
                @empty
                    <div class="intram-empty">Belum ada data simpul tujuan yang dapat ditampilkan.</div>
                @endforelse
            </article>
        </div>

        <div class="intram-connect-grid" style="margin-top:18px">
            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Top 5 Sektor Komoditas</h2>
                    <p>Porsi sektor dari seluruh entri komoditas pada hubungan Verifikasi Final.</p>
                </div>
                @php $maksSektor = max(1, $kv['top_sektor'][0]['jumlah'] ?? 0); @endphp
                @forelse ($kv['top_sektor'] as $index => $item)
                    <div class="intram-connect-row">
                        <div class="intram-connect-row-head"><span>{{ $index + 1 }}. {{ $item['nama'] }}</span><strong>{{ $fmt($item['jumlah']) }}<small class="intram-muted"> · {{ $fmt($item['persen'], 1) }}%</small></strong></div>
                        <div class="intram-connect-bar"><div class="intram-connect-fill" style="width:{{ max(0, min(100, $item['jumlah'] / $maksSektor * 100)) }}%;background:#0d9488"></div></div>
                    </div>
                @empty
                    <div class="intram-empty">Belum ada data sektor komoditas pada hubungan yang dapat dipetakan.</div>
                @endforelse
            </article>

            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Top 5 Komoditas</h2>
                    <p>Komoditas yang paling sering tercatat pada tujuan pengajuan Verifikasi Final.</p>
                </div>
                @php $maksKomoditas = max(1, $kv['top_komoditas'][0]['jumlah'] ?? 0); @endphp
                @forelse ($kv['top_komoditas'] as $index => $item)
                    <div class="intram-connect-row">
                        <div class="intram-connect-row-head"><span>{{ $index + 1 }}. {{ $item['nama'] }}</span><strong>{{ $fmt($item['jumlah']) }}</strong></div>
                        <div class="intram-connect-bar"><div class="intram-connect-fill" style="width:{{ max(0, min(100, $item['jumlah'] / $maksKomoditas * 100)) }}%;background:#2563eb"></div></div>
                    </div>
                @empty
                    <div class="intram-empty">Belum ada data komoditas pada hubungan yang dapat dipetakan.</div>
                @endforelse
            </article>
        </div>

        <div class="intram-connect-grid" style="margin-top:18px">
            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Top 5 Provinsi Asal</h2>
                    <p>Provinsi dengan hubungan konektivitas terbanyak.</p>
                </div>
                @php $maksProv = max(1, $kv['top_provinsi'][0]['jumlah'] ?? 0); @endphp
                @forelse ($kv['top_provinsi'] as $index => $item)
                    <div class="intram-connect-row">
                        <div class="intram-connect-row-head"><span>{{ $index + 1 }}. {{ $item['nama'] }}</span><strong>{{ $fmt($item['jumlah']) }}</strong></div>
                        <div class="intram-connect-bar"><div class="intram-connect-fill" style="width:{{ max(0, min(100, $item['jumlah'] / $maksProv * 100)) }}%;background:#d97706"></div></div>
                    </div>
                @empty
                    <div class="intram-empty">Belum ada data provinsi asal yang dapat ditampilkan.</div>
                @endforelse
            </article>

            <article class="intram-card">
                <div class="intram-card-heading">
                    <h2>Rata-rata Jarak per Moda</h2>
                    <p>Jarak garis lurus rata-rata hubungan pada tiap moda (km).</p>
                </div>
                @php $maksJarakModa = max(1, (int) collect($kv['jarak_moda'])->max('rata')); @endphp
                @forelse ($kv['jarak_moda'] as $item)
                    <div class="intram-connect-row">
                        <div class="intram-connect-row-head"><span>{{ $item['nama'] }}</span><strong>{{ $fmt($item['rata']) }} km</strong></div>
                        <div class="intram-connect-bar"><div class="intram-connect-fill" style="width:{{ max(2, min(100, $item['rata'] / $maksJarakModa * 100)) }}%;background:{{ $warnaModaDari($item['nama']) }}"></div></div>
                    </div>
                @empty
                    <div class="intram-empty">Belum ada data jarak per moda.</div>
                @endforelse
            </article>
        </div>
        <p class="intram-connect-note">Top sektor dan komoditas dihitung berdasarkan frekuensi entri komoditas, bukan total volume (satuan dapat berbeda). Sektor yang tidak dapat dipetakan secara unik dikelompokkan sebagai belum terpetakan / ambigu. Statistik ini hanya menghitung pengajuan Verifikasi Final dengan koordinat asal dan tujuan valid. Satu pengajuan dengan beberapa tujuan dihitung sebagai beberapa hubungan. Data menunjukkan kebutuhan konektivitas, bukan layanan transportasi aktual.</p>
    </section>

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
