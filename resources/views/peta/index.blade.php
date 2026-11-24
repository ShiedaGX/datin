@extends('layouts.app')

@section('title', 'Peta Konektivitas Regional')
@section('heading', 'Peta Konektivitas Regional')
@section('subheading', 'Pengajuan Verifikasi Final')

@section('content')

<style>
    .pk-shell {
        --pk-ink:#14243b;
        --pk-muted:#718198;
        --pk-border:#e3eaf2;
        --pk-blue:#2563eb;
        display:grid;
        grid-template-columns:326px minmax(0,1fr);
        height:min(820px,calc(100vh - 145px));
        min-height:660px;
        font-family:Inter,'Plus Jakarta Sans','Barlow Semi Condensed',system-ui,sans-serif;
        border:1px solid #e0e8f1;
        border-radius:20px;
        overflow:hidden;
        background:#fff;
        box-shadow:0 14px 46px rgba(22,47,82,.09);
        position:relative;
    }

    /* =====================================================
       FULLSCREEN WORKSPACE
       ===================================================== */

    .pk-shell:fullscreen,
    .pk-shell.pk-fullscreen-fallback {
        width:100%;
        height:100%;
        height:100dvh;
        max-width:none;
        max-height:none;
        min-height:0;
        margin:0;
        padding:0;
        border:0;
        border-radius:0;
        box-shadow:none;
        background:#fff;
        grid-template-columns:326px minmax(0,1fr);
        grid-template-rows:minmax(0,1fr);
    }

    .pk-shell.pk-fullscreen-fallback {
        position:fixed;
        inset:0;
        z-index:2147483647;
    }

    .pk-shell.pk-sidebar-hidden {
        grid-template-columns:minmax(0,1fr);
    }

    .pk-shell.pk-sidebar-hidden .pk-panel {
        display:none;
    }

    .pk-shell:fullscreen .pk-canvas,
    .pk-shell.pk-fullscreen-fallback .pk-canvas {
        width:100%;
        height:100%;
        min-height:0;
    }

    .pk-shell:fullscreen .pk-panel,
    .pk-shell.pk-fullscreen-fallback .pk-panel {
        max-height:none;
        min-height:0;
    }

    .pk-map-tools {
        position:absolute;
        top:20px;
        right:17px;
        z-index:20;
        display:flex;
        align-items:center;
        gap:7px;
    }

    .pk-tool-button {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        min-height:38px;
        padding:9px 12px;
        border:1px solid #dce6f0;
        border-radius:10px;
        background:rgba(255,255,255,.97);
        color:#334760;
        box-shadow:0 5px 18px rgba(20,55,88,.09);
        font:inherit;
        font-size:11px;
        font-weight:750;
        cursor:pointer;
        white-space:nowrap;
        transition:background .2s,color .2s;
    }

    .pk-tool-button:hover {
        background:#eff6ff;
        color:#2563eb;
    }

    .pk-tool-button:focus-visible {
        outline:2px solid #2563eb;
        outline-offset:2px;
    }

    .pk-tool-button svg {
        width:16px;
        height:16px;
        flex-shrink:0;
        fill:none;
        stroke:currentColor;
        stroke-width:1.8;
        stroke-linecap:round;
        stroke-linejoin:round;
    }

    .pk-tool-button[aria-pressed=true] {
        background:#183a5c;
        border-color:#183a5c;
        color:#fff;
    }

    .pk-panel {
        background:#fff;
        color:var(--pk-ink);
        overflow:auto;
        min-height:0;
        padding:0;
        scrollbar-width:thin;
        scrollbar-color:#d8e1ec transparent;
        border-right:1px solid var(--pk-border);
    }

    .pk-panel-head {
        padding:25px 23px 21px;
        background:linear-gradient(150deg,#10243e 0%,#1c4167 100%);
        color:#fff;
        position:relative;
        overflow:hidden;
    }

    .pk-panel-head:after {
        content:'';
        position:absolute;
        width:180px;
        height:180px;
        border-radius:50%;
        border:1px solid rgba(255,255,255,.09);
        right:-72px;
        top:-83px;
        box-shadow:0 0 0 35px rgba(255,255,255,.025),
                   0 0 0 70px rgba(255,255,255,.025);
        pointer-events:none;
    }

    .pk-eyebrow {
        font-size:10px;
        font-weight:800;
        letter-spacing:2px;
        color:#a9d3ff;
        text-transform:uppercase;
        display:flex;
        align-items:center;
        gap:8px;
        margin-bottom:12px;
    }

    .pk-eyebrow:before {
        content:'';
        width:7px;
        height:7px;
        background:#52d5b3;
        border-radius:50%;
        box-shadow:0 0 0 4px rgba(82,213,179,.15);
    }

    .pk-panel h2 {
        font-size:21px;
        line-height:1.25;
        font-weight:750;
        letter-spacing:-.5px;
        margin:0 0 10px;
        color:#fff;
    }

    .pk-panel-head p {
        font-size:12px;
        color:#c3d3e6;
        line-height:1.65;
        margin:0;
    }

    .pk-panel-head strong {
        color:#fff;
        font-weight:650;
    }

    .pk-panel-body {
        padding:22px 21px 26px;
    }

    .pk-section-title {
        font-size:10px;
        font-weight:800;
        letter-spacing:1.4px;
        text-transform:uppercase;
        color:#8b9bb0;
        margin:3px 0 15px;
        display:flex;
        align-items:center;
        gap:9px;
    }

    .pk-section-title:after {
        content:'';
        flex:1;
        height:1px;
        background:#e9eef4;
    }

    .pk-field {
        margin:0 0 17px;
    }

    .pk-field label {
        display:block;
        color:#334760;
        font-size:12px;
        margin-bottom:7px;
        font-weight:700;
    }

    .pk-field input,
    .pk-field select {
        display:block;
        width:100%;
        min-height:41px;
        padding:9px 12px;
        background:#f8fafc;
        color:#1d344f;
        border:1px solid #dfe7f0;
        border-radius:10px;
        font:inherit;
        font-size:12px;
        outline:none;
        box-sizing:border-box;
    }

    .pk-field select {
        padding-right:34px;
        font-weight:550;
        cursor:pointer;
        transition:border-color .18s,box-shadow .18s;
    }

    .pk-field select:hover {
        border-color:#afc4dc;
        background:#fff;
    }

    .pk-field input:focus,
    .pk-field select:focus {
        border-color:#4c88df;
        box-shadow:0 0 0 3px rgba(37,99,235,.11);
        background:#fff;
    }

    .pk-field select:disabled {
        opacity:.65;
        cursor:not-allowed;
    }

    .pk-search-results {
        max-height:220px;
        overflow-y:auto;
        border:1px solid #e0e8f1;
        border-radius:10px;
        margin-top:7px;
        background:#fff;
    }

    .pk-search-results[hidden] {
        display:none;
    }

    .pk-search-item {
        width:100%;
        display:block;
        text-align:left;
        border:0;
        border-bottom:1px solid #edf1f6;
        background:#fff;
        padding:10px 12px;
        color:#243950;
        cursor:pointer;
        font:inherit;
        font-size:12px;
    }

    .pk-search-item:last-child {
        border-bottom:0;
    }

    .pk-search-item:hover,
    .pk-search-item:focus-visible {
        background:#eff6ff;
    }

    .pk-search-item small {
        display:block;
        color:#7c8da1;
        margin-top:3px;
        font-size:10px;
    }

    .pk-search-hint {
        padding:10px 12px;
        color:#7c8da1;
        font-size:11px;
    }

    .pk-toggles {
        display:flex;
        gap:7px;
        flex-wrap:wrap;
    }

    .pk-toggle {
        border:1px solid #dce6f0;
        border-radius:9px;
        background:#f5f8fc;
        color:#263b54;
        padding:8px 10px;
        cursor:pointer;
        font:inherit;
        font-weight:650;
        font-size:11px;
        transition:background .2s,border-color .2s,opacity .2s,transform .2s;
    }

    .pk-toggle:hover {
        background:#eaf2fd;
        transform:translateY(-1px);
    }

    .pk-toggle[aria-pressed=false] {
        opacity:.45;
        background:#fff;
    }

    .pk-reset-filter {
        width:100%;
        padding:11px 14px;
        border:1px solid #dce6f0;
        border-radius:10px;
        background:#f5f8fc;
        color:#334760;
        font:inherit;
        font-size:12px;
        font-weight:700;
        cursor:pointer;
        transition:background .2s,border-color .2s;
    }

    .pk-reset-filter:hover {
        background:#eaf2fd;
        border-color:#a9c5e7;
    }

    .pk-summary {
        margin:21px 0 15px;
        padding:14px 15px;
        background:#eff6ff;
        border:1px solid #d9e8fc;
        border-radius:12px;
        font-size:12px;
        color:#315579;
        line-height:1.65;
        font-weight:650;
    }

    .pk-stat-grid {
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:8px;
    }

    .pk-stat {
        background:#fff;
        border:1px solid #d9e8fc;
        border-radius:9px;
        padding:10px;
        min-width:0;
    }

    .pk-stat span {
        display:block;
        font-size:10px;
        font-weight:500;
        color:#718198;
        line-height:1.5;
    }

    .pk-stat strong {
        display:block;
        font-size:23px;
        color:#183a5c;
        margin-top:4px;
        letter-spacing:-.5px;
        font-variant-numeric:tabular-nums;
    }

    .pk-potensi {
        border-top:1px solid #d9e8fc;
        margin-top:12px;
        padding-top:12px;
    }

    .pk-potensi-list {
        max-height:230px;
        overflow:auto;
        margin-top:8px;
        scrollbar-width:thin;
    }

    .pk-potensi-row {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:10px;
        padding:8px 0;
        border-bottom:1px solid #dce7f4;
        font-size:11px;
    }

    .pk-potensi-row span {
        overflow-wrap:anywhere;
    }

    .pk-potensi-row strong {
        text-align:right;
        flex-shrink:0;
    }

    .pk-summary-note {
        font-size:10px;
        font-weight:400;
        margin-top:9px;
        color:#718198;
        line-height:1.5;
    }

    .pk-summary-warning {
        font-size:10px;
        color:#946522;
        margin-top:9px;
        font-weight:600;
    }

    .pk-detail {
        background:#fff;
        color:#1b3049;
        border:1px solid #e2e9f2;
        border-radius:12px;
        padding:16px;
        line-height:1.5;
        font-size:12px;
        box-shadow:0 3px 13px rgba(19,49,83,.035);
        overflow-wrap:anywhere;
    }

    .pk-detail h3 {
        font-size:15px;
        line-height:1.4;
        font-weight:750;
        margin:0 0 8px;
        letter-spacing:-.2px;
    }

    .pk-detail p {
        color:#687c95;
        margin:7px 0;
        line-height:1.55;
    }

    .pk-detail table {
        width:100%;
        border-collapse:collapse;
        margin-top:10px;
    }

    .pk-detail th,
    .pk-detail td {
        text-align:left;
        vertical-align:top;
        border-bottom:1px solid #e7edf5;
        padding:9px 3px;
        font-size:11px;
    }

    .pk-detail th {
        color:#687c95;
        font-weight:700;
    }

    .pk-detail .pk-muted {
        color:#8392a4;
        font-size:10px;
        margin-top:3px;
    }

    .pk-direction {
        margin-top:14px;
        padding:11px;
        border-radius:9px;
        font-size:11px;
        line-height:1.6;
    }

    .pk-direction strong {
        display:block;
        font-size:12px;
        margin-bottom:3px;
    }

    .pk-direction-out {
        background:#eff6ff;
        color:#315579;
        border:1px solid #d9e8fc;
    }

    .pk-direction-in {
        background:#ecfdf5;
        color:#166534;
        border:1px solid #d1fae5;
    }

    .pk-related-region {
        margin-top:14px;
        padding-top:14px;
        border-top:1px solid #e7edf5;
    }

    .pk-related-region h4 {
        font-size:13px;
        margin:0 0 5px;
        color:#243950;
    }

    .pk-report-card {
        margin-top:10px;
        padding:11px;
        background:#f8fafc;
        border:1px solid #e7edf5;
        border-radius:9px;
    }

    .pk-report-number {
        font-size:10px;
        color:#718198;
        font-weight:750;
        text-transform:uppercase;
        letter-spacing:.5px;
    }

    .pk-report-focus {
        display:block;
        width:100%;
        padding:8px 10px;
        margin-top:12px;
        border:1px solid #d9e5f2;
        border-radius:7px;
        background:#fff;
        color:#315579;
        font:inherit;
        font-size:10px;
        font-weight:700;
        cursor:pointer;
    }

    .pk-report-focus:hover {
        background:#eff6ff;
    }

    .pk-canvas {
        background:radial-gradient(
            ellipse at 50% 42%,
            #e6f5fa 0%,
            #d8ebf5 64%,
            #cfe5f2 100%
        );
        position:relative;
        overflow:hidden;
        min-width:0;
        min-height:0;
    }

    #pk-map {
        width:100%;
        height:100%;
        display:block;
        cursor:grab;
        touch-action:none;
    }

    #pk-map:active {
        cursor:grabbing;
    }

    .pk-land {
        fill:#f6f2e6;
        stroke:#bdcfc6;
        stroke-width:.75;
        vector-effect:non-scaling-stroke;
        transition:fill .18s;
    }

    .pk-land:hover {
        fill:#f9f5e9;
    }

    .pk-line {
        fill:none;
        stroke-opacity:.16;
        stroke-width:1;
        vector-effect:non-scaling-stroke;
        stroke-linecap:round;
        cursor:pointer;
        transition:stroke-opacity .22s,stroke-width .22s;
    }

    .pk-line.pk-dimmed {
        stroke-opacity:.04;
        stroke-width:1;
    }

    .pk-line.pk-selected {
        stroke-opacity:.9;
        stroke-width:2;
    }

    .pk-line:hover {
        stroke-opacity:.9;
        stroke-width:2;
    }

    .pk-point {
        stroke:#fff;
        stroke-width:1.7;
        vector-effect:non-scaling-stroke;
        cursor:pointer;
        filter:drop-shadow(0 1px 2px rgba(23,42,65,.32));
    }

    .pk-point:hover {
        stroke-width:2.5;
    }

    .pk-city-dot {
        fill:#52677a;
        stroke:#fff;
        stroke-width:1.2;
        vector-effect:non-scaling-stroke;
        pointer-events:none;
    }

    .pk-city-label {
        fill:#405268;
        font-size:10px;
        font-weight:650;
        paint-order:stroke;
        stroke:#f6f2e6;
        stroke-width:3px;
        stroke-linejoin:round;
        pointer-events:none;
    }

    .pk-city-label.pk-city-major {
        font-size:12px;
        font-weight:800;
        fill:#20334a;
    }

    .pk-water-label {
        fill:#4386aa;
        font-size:11px;
        font-weight:650;
        font-style:italic;
        letter-spacing:.5px;
        text-anchor:middle;
        paint-order:stroke;
        stroke:#dcecf6;
        stroke-width:3px;
        stroke-linejoin:round;
        pointer-events:none;
    }

    .pk-water-label.pk-water-ocean {
        font-size:13px;
        letter-spacing:1.4px;
        fill:#2b759e;
    }

    .pk-water-label.pk-water-strait {
        font-size:9px;
        letter-spacing:0;
        fill:#5594b4;
    }

    .pk-map-header {
        position:absolute;
        left:22px;
        top:20px;
        z-index:5;
        pointer-events:none;
        background:rgba(255,255,255,.93);
        border:1px solid rgba(222,233,243,.9);
        box-shadow:0 7px 25px rgba(33,66,101,.08);
        border-radius:13px;
        padding:12px 16px;
        backdrop-filter:blur(10px);
        max-width:calc(100% - 44px);
    }

    .pk-map-header strong {
        display:block;
        color:#1b344d;
        font-size:13px;
        letter-spacing:-.1px;
    }

    .pk-map-header span {
        display:block;
        margin-top:4px;
        color:#8193a7;
        font-size:10px;
        font-weight:550;
    }

    .pk-legend {
        position:absolute;
        left:18px;
        right:76px;
        bottom:17px;
        display:flex;
        flex-direction:column;
        align-items:flex-start;
        gap:8px;
        background:rgba(255,255,255,.95);
        color:#243950;
        border:1px solid rgba(221,231,242,.9);
        border-radius:13px;
        padding:13px 17px;
        font-size:10px;
        line-height:1.5;
        box-shadow:0 9px 30px rgba(35,68,100,.10);
        backdrop-filter:blur(10px);
        z-index:10;
    }

    .pk-legend-row {
        display:flex;
        flex-wrap:wrap;
        align-items:center;
        column-gap:15px;
        row-gap:7px;
        width:100%;
    }

    .pk-legend-heading {
        font-size:10px;
        font-weight:800;
        min-width:115px;
        white-space:nowrap;
        color:#52667d;
    }

    .pk-legend-item {
        display:inline-flex;
        align-items:center;
        gap:6px;
        white-space:nowrap;
        color:#4d6178;
        font-weight:600;
    }

    .pk-dot {
        display:inline-block;
        width:9px;
        height:9px;
        border-radius:50%;
        flex-shrink:0;
        margin:0;
        box-shadow:0 0 0 2px rgba(0,0,0,.035);
    }

    .pk-line-legend {
        display:inline-block;
        width:20px;
        height:0;
        border-top:3px solid;
        border-radius:3px;
        flex-shrink:0;
        margin:0;
    }

    .pk-legend-note {
        width:100%;
        padding-top:8px;
        border-top:1px solid #e9eef4;
        color:#8795a7;
        font-size:10px;
        line-height:1.5;
    }

    .pk-zoom {
        position:absolute;
        right:17px;
        bottom:17px;
        display:flex;
        flex-direction:column;
        gap:5px;
        z-index:11;
        background:rgba(255,255,255,.95);
        border:1px solid #e1e9f2;
        border-radius:12px;
        padding:5px;
        box-shadow:0 8px 22px rgba(34,66,98,.12);
    }

    .pk-zoom button {
        width:35px;
        height:35px;
        border:0;
        background:transparent;
        color:#38536d;
        border-radius:8px;
        cursor:pointer;
        font-size:20px;
        font-weight:500;
        transition:background .2s,color .2s;
    }

    .pk-zoom button:hover {
        background:#e9f2ff;
        color:#1d64bb;
    }

    .pk-zoom button:focus-visible {
        outline:2px solid #3b82f6;
        outline-offset:1px;
    }

    .pk-empty {
        position:absolute;
        top:110px;
        left:22px;
        right:22px;
        background:#fff8e9;
        color:#785a22;
        border:1px solid #f2dfae;
        padding:13px 15px;
        border-radius:10px;
        font-size:12px;
        display:none;
        z-index:8;
        box-shadow:0 4px 16px rgba(0,0,0,.05);
    }

    /* Legenda lebih ringkas pada fullscreen */

    .pk-shell:fullscreen .pk-legend,
    .pk-shell.pk-fullscreen-fallback .pk-legend {
        right:auto;
        max-width:calc(100% - 100px);
        width:max-content;
    }

    @media(max-width:1200px) {
        .pk-tool-text {
            display:none;
        }

        .pk-tool-button {
            width:38px;
            padding:9px;
        }

        .pk-map-header {
            max-width:calc(100% - 130px);
        }
    }

    @media(max-width:1050px) {
        .pk-shell {
            grid-template-columns:292px minmax(0,1fr);
        }

        .pk-legend {
            right:69px;
        }
    }

    @media(max-width:850px) {
        .pk-shell {
            grid-template-columns:1fr;
            height:auto;
            min-height:0;
            border-radius:15px;
        }

        .pk-panel {
            max-height:440px;
            border-right:0;
            border-bottom:1px solid #e5edf5;
        }

        .pk-panel-head {
            padding:21px;
        }

        .pk-panel-body {
            padding:19px 21px;
        }

        .pk-canvas {
            height:65vh;
            min-height:460px;
        }

        .pk-legend {
            left:10px;
            right:60px;
            bottom:10px;
            padding:10px 12px;
            max-height:170px;
            overflow-y:auto;
        }

        .pk-legend-row {
            column-gap:10px;
            row-gap:6px;
        }

        .pk-legend-heading {
            min-width:100%;
        }

        .pk-map-header {
            left:12px;
            top:12px;
        }

        .pk-map-tools {
            right:12px;
            top:12px;
        }

        .pk-zoom {
            right:10px;
            bottom:10px;
        }

        .pk-shell:fullscreen,
        .pk-shell.pk-fullscreen-fallback {
            grid-template-columns:minmax(0,1fr);
        }

        .pk-shell:fullscreen .pk-panel,
        .pk-shell.pk-fullscreen-fallback .pk-panel {
            position:absolute;
            left:0;
            top:0;
            bottom:0;
            width:min(326px,78%);
            max-height:100%;
            z-index:30;
            box-shadow:10px 0 35px rgba(20,36,59,.18);
        }

        .pk-shell:fullscreen .pk-canvas,
        .pk-shell.pk-fullscreen-fallback .pk-canvas {
            height:100%;
            min-height:0;
        }
    }

    @media(max-width:520px) {
        .pk-map-header {
            padding:9px 12px;
        }

        .pk-map-header strong {
            font-size:11px;
        }

        .pk-map-header span {
            display:none;
        }

        .pk-legend {
            font-size:9px;
        }

        .pk-legend-item {
            white-space:normal;
        }
    }
</style>

<div class="pk-shell" id="pk-shell">

    {{-- SIDEBAR --}}

    <aside class="pk-panel" id="pk-panel">

        <div class="pk-panel-head">

            <div class="pk-eyebrow">
                DATIN · GEOSPATIAL INTELLIGENCE
            </div>

            <h2>Peta Kebutuhan Angkutan Bersubsidi</h2>

            <p>
                Data berstatus <strong>Verifikasi Final</strong>
                sesuai hak akses akun yang sedang login.
                Titik menggunakan koordinat kabupaten/kota
                dalam database DATIN.
            </p>

        </div>

        <div class="pk-panel-body">

            <div class="pk-section-title">
                Cari konektivitas
            </div>

            <div class="pk-field">

                <label for="pk-search">
                    Cari wilayah, komoditas, atau nomor laporan
                </label>

                <input
                    id="pk-search"
                    type="search"
                    placeholder="Contoh: Ambon, sapi, laporan..."
                    autocomplete="off"
                    aria-controls="pk-search-results"
                    aria-expanded="false"
                >

                <div
                    id="pk-search-results"
                    class="pk-search-results"
                    hidden
                ></div>

            </div>

            <div class="pk-section-title">
                Filter konektivitas
            </div>

            <div class="pk-field">
                <label>Klasifikasi daerah</label>
                <div id="pk-klas" class="pk-toggles"></div>
            </div>

            <div class="pk-field">

                <label for="pk-moda">
                    Moda transportasi
                </label>

                <select
                    id="pk-moda"
                    @if($modaTerkunci) disabled @endif
                >
                    <option value="">
                        {{ $modaTerkunci ? 'Moda akun saya' : 'Semua moda' }}
                    </option>
                </select>

            </div>

            <div class="pk-field">

                <label for="pk-prov">
                    Provinsi asal
                </label>

                <select id="pk-prov">
                    <option value="">Semua provinsi</option>
                </select>

            </div>

            <div class="pk-field">

                <label for="pk-sektor">
                    Sektor komoditas tujuan
                </label>

                <select id="pk-sektor">
                    <option value="">Semua sektor</option>
                </select>

            </div>

            <div class="pk-field">

                <label for="pk-komoditas">
                    Nama komoditas
                </label>

                <select id="pk-komoditas">
                    <option value="">Semua komoditas</option>
                </select>

            </div>

            <div class="pk-field">

                <label for="pk-jarak">
                    Kelas jarak lurus
                </label>

                <select id="pk-jarak">
                    <option value="">Semua jarak</option>
                </select>

            </div>

            <div class="pk-section-title" style="margin-top:23px">
                Tampilan peta
            </div>

            <div class="pk-field">

                <label for="pk-kota">
                    Label kota pada peta
                </label>

                <select id="pk-kota">
                    <option value="otomatis">
                        Otomatis mengikuti zoom
                    </option>
                    <option value="utama">
                        Hanya kota utama
                    </option>
                    <option value="semua">
                        Semua kota referensi
                    </option>
                    <option value="sembunyi">
                        Sembunyikan label kota
                    </option>
                </select>

            </div>

            <div class="pk-field">

                <label for="pk-perairan">
                    Label perairan pada peta
                </label>

                <select id="pk-perairan">
                    <option value="otomatis">
                        Otomatis mengikuti zoom
                    </option>
                    <option value="utama">
                        Samudra dan laut utama
                    </option>
                    <option value="semua">
                        Semua nama perairan
                    </option>
                    <option value="sembunyi">
                        Sembunyikan label perairan
                    </option>
                </select>

            </div>

            <button
                type="button"
                id="pk-reset-filter"
                class="pk-reset-filter"
            >
                ⟲ Reset seluruh filter
            </button>

            <div
                class="pk-summary"
                id="pk-summary"
                aria-live="polite"
            >
                Memuat ringkasan konektivitas...
            </div>

            <div
                class="pk-detail"
                id="pk-detail"
                aria-live="polite"
            >
                <h3>Detail konektivitas</h3>

                <p>
                    Pilih garis atau titik pada peta
                    untuk melihat rute dan komoditasnya.
                </p>
            </div>

        </div>

    </aside>

    {{-- AREA PETA --}}

    <section
        class="pk-canvas"
        id="pk-canvas"
        aria-label="Peta konektivitas Indonesia"
    >

        <div class="pk-map-header">

            <strong>
                Eksplorasi Konektivitas Indonesia
            </strong>

            <span>
                Jelajahi rute, simpul wilayah, dan potensi komoditas
            </span>

        </div>

        {{-- KONTROL FULLSCREEN DAN SIDEBAR --}}

        <div class="pk-map-tools">

            <button
                type="button"
                id="pk-toggle-sidebar"
                class="pk-tool-button"
                title="Sembunyikan filter dan detail"
                aria-label="Sembunyikan filter dan detail"
                aria-controls="pk-panel"
                aria-expanded="true"
            >

                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                    <path d="M9 4v16"/>
                </svg>

                <span
                    class="pk-tool-text"
                    id="pk-sidebar-text"
                >
                    Sembunyikan filter
                </span>

            </button>

            <button
                type="button"
                id="pk-fullscreen"
                class="pk-tool-button"
                title="Tampilkan layar penuh"
                aria-label="Tampilkan layar penuh"
                aria-pressed="false"
            >

                <svg
                    id="pk-fullscreen-icon"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M8 3H3v5M16 3h5v5M21 16v5h-5M8 21H3v-5"/>
                </svg>

                <span
                    class="pk-tool-text"
                    id="pk-fullscreen-text"
                >
                    Fullscreen
                </span>

            </button>

        </div>

        <svg
            id="pk-map"
            role="img"
            aria-label="Peta asal dan tujuan konektivitas regional"
        ></svg>

        <div class="pk-empty" id="pk-empty"></div>

        {{-- LEGENDA --}}

        <div class="pk-legend">

            <div class="pk-legend-row">

                <strong class="pk-legend-heading">
                    Klasifikasi Daerah:
                </strong>

                <span class="pk-legend-item">
                    <span class="pk-dot" style="background:#c8372d"></span>
                    Prioritas &amp; Strategis (PS)
                </span>

                <span class="pk-legend-item">
                    <span class="pk-dot" style="background:#e3a812"></span>
                    Prioritas (P)
                </span>

                <span class="pk-legend-item">
                    <span class="pk-dot" style="background:#1f7a58"></span>
                    Strategis (S)
                </span>

                <span class="pk-legend-item">
                    <span class="pk-dot" style="background:#15263f"></span>
                    Simpul tujuan
                </span>

            </div>

            <div class="pk-legend-row">

                <strong class="pk-legend-heading">
                    Moda Transportasi:
                </strong>

                <span class="pk-legend-item">
                    <span class="pk-line-legend" style="border-color:#e67e22"></span>
                    Darat
                </span>

                <span class="pk-legend-item">
                    <span class="pk-line-legend" style="border-color:#1688dc"></span>
                    Laut
                </span>

                <span class="pk-legend-item">
                    <span class="pk-line-legend" style="border-color:#8b5cf6"></span>
                    Udara
                </span>

                <span class="pk-legend-item">
                    <span class="pk-line-legend" style="border-color:#159b68"></span>
                    Kereta Api
                </span>

            </div>

            <div class="pk-legend-row">

                <strong class="pk-legend-heading">
                    Referensi Geografis:
                </strong>

                <span class="pk-legend-item">
                    <span
                        class="pk-dot"
                        style="background:#435469;width:7px;height:7px"
                    ></span>
                    Label kota (bukan pengajuan)
                </span>

            </div>

            <div class="pk-legend-note">
                Garis menunjukkan kebutuhan konektivitas,
                bukan lintasan transportasi aktual.
                Jarak = garis lurus Haversine.
                Label kota dan perairan digunakan
                sebagai orientasi geografis.
            </div>

        </div>

        {{-- KONTROL ZOOM --}}

        <div class="pk-zoom">

            <button
                type="button"
                id="pk-plus"
                aria-label="Perbesar"
                title="Perbesar"
            >+</button>

            <button
                type="button"
                id="pk-minus"
                aria-label="Perkecil"
                title="Perkecil"
            >−</button>

            <button
                type="button"
                id="pk-reset"
                aria-label="Atur ulang posisi peta"
                title="Atur ulang posisi peta"
            >⟲</button>

        </div>

    </section>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js"></script>

<script>
(() => {

    'use strict';

    // =====================================================
    // DATA UTAMA
    // =====================================================

    const points = @json($titikPeta);
    const routes = @json($rutePeta);

    const byId = new Map(
        points.map(p => [p.id, p])
    );

    const routeKeys = new Map(
        routes.map((r, index) => [r, index])
    );

    const select = id =>
        document.getElementById(id);

    const normalize = s =>
        String(s ?? '')
            .toLocaleLowerCase('id-ID')
            .trim()
            .replace(/\s+/g, ' ');

    const esc = s =>
        String(s ?? '').replace(
            /[&<>"']/g,
            ch => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[ch])
        );

    const formatter = new Intl.NumberFormat(
        'id-ID',
        { maximumFractionDigits: 2 }
    );

    const fmt = n => formatter.format(n);

    const unique = xs =>
        [...new Set(xs.filter(Boolean))]
            .sort((a, b) =>
                String(a).localeCompare(String(b), 'id')
            );

    const options = (id, values) => {

        const element = select(id);

        for (const v of values) {

            const o = document.createElement('option');

            o.value = v;
            o.textContent = v;

            element.appendChild(o);

        }

    };

    function jumlahValid(value) {

        return value !== null
            && value !== undefined
            && String(value).trim() !== ''
            && Number.isFinite(Number(value));

    }

    // =====================================================
    // WARNA MODA DAN KLASIFIKASI
    // =====================================================

    const warnaModa = {
        1: '#e67e22',
        2: '#1688dc',
        3: '#8b5cf6',
        4: '#159b68'
    };

    const warnaGaris = r =>
        warnaModa[r.id_moda] || '#64748b';

    const color = k =>
        /prioritas.*strategis|strategis.*prioritas|\bPS\b/i.test(k)
            ? '#c8372d'
            : /prioritas|^P$/i.test(k)
                ? '#e3a812'
                : /strategis|^S$/i.test(k)
                    ? '#1f7a58'
                    : '#6c82a0';

    // =====================================================
    // REFERENSI PERAIRAN
    // =====================================================

    const perairanReferensi = [

        {nama:'Samudra Hindia',lon:103.0,lat:-10.8,jenis:'samudra',utama:true},
        {nama:'Samudra Pasifik',lon:137.5,lat:2.8,jenis:'samudra',utama:true},
        {nama:'Laut Jawa',lon:111.5,lat:-5.25,jenis:'laut',utama:true},
        {nama:'Laut Banda',lon:126.5,lat:-5.1,jenis:'laut',utama:true},
        {nama:'Laut Sulawesi',lon:122.0,lat:3.1,jenis:'laut',utama:true},
        {nama:'Laut Arafura',lon:135.0,lat:-8.0,jenis:'laut',utama:true},
        {nama:'Laut Flores',lon:120.0,lat:-7.3,jenis:'laut'},
        {nama:'Laut Seram',lon:129.0,lat:-2.6,jenis:'laut'},
        {nama:'Laut Halmahera',lon:129.5,lat:1.0,jenis:'laut'},
        {nama:'Laut Natuna',lon:108.0,lat:3.5,jenis:'laut'},
        {nama:'Laut Timor',lon:126.5,lat:-11.1,jenis:'laut'},
        {nama:'Selat Malaka',lon:99.5,lat:3.1,jenis:'selat'},
        {nama:'Selat Sunda',lon:105.85,lat:-5.95,jenis:'selat'},
        {nama:'Selat Karimata',lon:108.4,lat:-2.0,jenis:'selat'},
        {nama:'Selat Makassar',lon:118.2,lat:-2.7,jenis:'selat'},
        {nama:'Selat Lombok',lon:115.75,lat:-8.5,jenis:'selat'},
        {nama:'Selat Bali',lon:114.5,lat:-8.3,jenis:'selat'},
        {nama:'Selat Alas',lon:116.75,lat:-8.45,jenis:'selat'},
        {nama:'Selat Ombai',lon:124.5,lat:-8.5,jenis:'selat'}

    ];

    // =====================================================
    // REFERENSI KOTA
    // =====================================================

    const kotaReferensi = [

        {nama:'Jakarta',lon:106.8456,lat:-6.2088,utama:true},
        {nama:'Medan',lon:98.6722,lat:3.5952,utama:true},
        {nama:'Surabaya',lon:112.7521,lat:-7.2575,utama:true},
        {nama:'Makassar',lon:119.4327,lat:-5.1477,utama:true},
        {nama:'Bandung',lon:107.6191,lat:-6.9175,utama:true},
        {nama:'Semarang',lon:110.4203,lat:-6.9667,utama:true},
        {nama:'Palembang',lon:104.7566,lat:-2.9761,utama:true},
        {nama:'Denpasar',lon:115.2126,lat:-8.6705,utama:true},

        {nama:'Banda Aceh',lon:95.3238,lat:5.5483},
        {nama:'Padang',lon:100.3543,lat:-0.9471},
        {nama:'Pekanbaru',lon:101.4478,lat:0.5071},
        {nama:'Tanjungpinang',lon:104.4550,lat:0.9186},
        {nama:'Jambi',lon:103.6131,lat:-1.6101},
        {nama:'Bengkulu',lon:102.2655,lat:-3.7928},
        {nama:'Pangkalpinang',lon:106.1099,lat:-2.1291},
        {nama:'Bandar Lampung',lon:105.2667,lat:-5.4292},
        {nama:'Serang',lon:106.1503,lat:-6.1200},
        {nama:'Yogyakarta',lon:110.3695,lat:-7.7956},
        {nama:'Mataram',lon:116.1167,lat:-8.5833},
        {nama:'Kupang',lon:123.6070,lat:-10.1772},
        {nama:'Pontianak',lon:109.3414,lat:-0.0263},
        {nama:'Palangka Raya',lon:113.9213,lat:-2.2096},
        {nama:'Banjarmasin',lon:114.5908,lat:-3.3186},
        {nama:'Samarinda',lon:117.1537,lat:-0.5022},
        {nama:'Tanjung Selor',lon:117.3736,lat:2.8375},
        {nama:'Manado',lon:124.8421,lat:1.4748},
        {nama:'Gorontalo',lon:123.0595,lat:0.5435},
        {nama:'Palu',lon:119.8707,lat:-0.8917},
        {nama:'Kendari',lon:122.5149,lat:-3.9985},
        {nama:'Mamuju',lon:118.8867,lat:-2.6806},
        {nama:'Ambon',lon:128.1814,lat:-3.6954},
        {nama:'Sofifi',lon:127.5690,lat:0.7373},
        {nama:'Jayapura',lon:140.7181,lat:-2.5337},
        {nama:'Manokwari',lon:134.0620,lat:-0.8615},
        {nama:'Sorong',lon:131.2549,lat:-0.8762},
        {nama:'Nabire',lon:135.4950,lat:-3.3682},
        {nama:'Wamena',lon:138.9490,lat:-4.0950},
        {nama:'Merauke',lon:140.4018,lat:-8.4932}

    ];

    // =====================================================
    // INISIALISASI FILTER
    // =====================================================

    options(
        'pk-moda',
        unique(routes.map(r => r.moda))
    );

    options(
        'pk-prov',
        unique(routes.map(r =>
            byId.get(r.asal)?.provinsi
        ))
    );

    options(
        'pk-sektor',
        unique(routes.flatMap(r => r.sektor || []))
    );

    options('pk-jarak', [
        '<100 km',
        '100–300 km',
        '300–700 km',
        '700–1500 km',
        '>1500 km'
    ]);

    const classes = unique(
        routes.map(r => r.klasifikasi)
    );

    const enabled = new Set(classes);

    // =====================================================
    // FILTER SEKTOR DAN KOMODITAS
    // =====================================================

    function cocokSektor(k, sektor) {

        if (!sektor) {
            return true;
        }

        const daftar = Array.isArray(k.sektor)
            ? k.sektor
            : [k.sektor];

        return daftar.some(s =>
            normalize(s) === normalize(sektor)
        );

    }

    function isiKomoditas() {

        const sektor = select('pk-sektor').value;
        const dropdown = select('pk-komoditas');
        const sebelumnya = dropdown.value;
        const daftar = new Map();

        routes.forEach(r => {

            (r.komoditas || []).forEach(k => {

                if (!k.nama || !cocokSektor(k, sektor)) {
                    return;
                }

                const key = normalize(k.nama);

                if (!key) {
                    return;
                }

                if (!daftar.has(key)) {
                    daftar.set(key, String(k.nama).trim());
                }

            });

        });

        dropdown.replaceChildren();

        const semua = document.createElement('option');

        semua.value = '';
        semua.textContent = 'Semua komoditas';

        dropdown.appendChild(semua);

        [...daftar.entries()]
            .sort((a, b) => a[1].localeCompare(b[1], 'id'))
            .forEach(([key, nama]) => {

                const o = document.createElement('option');

                o.value = key;
                o.textContent = nama;

                dropdown.appendChild(o);

            });

        dropdown.value = daftar.has(sebelumnya)
            ? sebelumnya
            : '';

    }

    isiKomoditas();

    classes.forEach(k => {

        const b = document.createElement('button');

        b.type = 'button';
        b.className = 'pk-toggle';
        b.textContent = k;
        b.style.borderLeft = '4px solid ' + color(k);

        b.setAttribute('aria-pressed', 'true');

        b.addEventListener('click', () => {

            if (enabled.has(k)) {
                enabled.delete(k);
            } else {
                enabled.add(k);
            }

            b.setAttribute(
                'aria-pressed',
                String(enabled.has(k))
            );

            update();
            cariKonektivitas();

        });

        select('pk-klas').appendChild(b);

    });

    // =====================================================
    // INISIALISASI PETA
    // =====================================================

    const svg = d3.select('#pk-map');

    const root = svg.append('g');
    const land = root.append('g');
    const waterLayer = root.append('g');
    const links = root.append('g');
    const cityLayer = root.append('g');
    const dots = root.append('g');

    const projection = d3.geoMercator();
    const path = d3.geoPath(projection);

    let currentZoom = 1;
    let activeOrigins = new Set();
    let selected = null;
    let selectedWilayah = null;
    let geo = null;

    let ukuranPeta = null;
    let resizeFrame = null;

    // =====================================================
    // UKURAN MARKER ADAPTIF
    // =====================================================

    function radiusTitik(p) {

        const isOrigin = activeOrigins.has(p.id);
        const isSelected = selectedWilayah === p.id;

        const base = isSelected
            ? 8
            : (isOrigin ? 6 : 5);

        const shrink =
            1 + 0.14 * Math.log2(
                Math.max(1, currentZoom)
            );

        return base / (currentZoom * shrink);

    }

    function sesuaikanUkuranTitik() {

        dots.selectAll('circle.pk-point')
            .attr('r', radiusTitik);

    }

    const zoom = d3.zoom()

        .scaleExtent([1, 12])

        .on('zoom', e => {

            root.attr('transform', e.transform);

            currentZoom = e.transform.k;

            sesuaikanUkuranTitik();

            gambarLabelKota();

            gambarLabelPerairan();

        });

    svg.call(zoom);

    // =====================================================
    // FULLSCREEN DAN SIDEBAR
    // =====================================================

    const shell = select('pk-shell');
    const panel = select('pk-panel');
    const fullscreenButton = select('pk-fullscreen');
    const sidebarButton = select('pk-toggle-sidebar');

    let fullscreenAktif = false;
    let sidebarSebelumFullscreen = false;
    let bodyOverflowSebelumnya = '';

    function fullscreenNativeAktif() {

        return document.fullscreenElement === shell;

    }

    function fullscreenFallbackAktif() {

        return shell.classList.contains(
            'pk-fullscreen-fallback'
        );

    }

    function sedangFullscreen() {

        return fullscreenNativeAktif()
            || fullscreenFallbackAktif();

    }

    function jadwalkanRender() {

        if (resizeFrame !== null) {

            cancelAnimationFrame(resizeFrame);

        }

        resizeFrame = requestAnimationFrame(() => {

            resizeFrame = null;

            render();

        });

    }

    function aturSidebar(sembunyi) {

        shell.classList.toggle(
            'pk-sidebar-hidden',
            sembunyi
        );

        sidebarButton.setAttribute(
            'aria-expanded',
            String(!sembunyi)
        );

        const teks = sembunyi
            ? 'Filter & Detail'
            : 'Sembunyikan filter';

        select('pk-sidebar-text').textContent = teks;

        sidebarButton.title = teks;

        sidebarButton.setAttribute(
            'aria-label',
            teks
        );

        jadwalkanRender();

    }

    function sinkronFullscreen() {

        const aktif = sedangFullscreen();

        if (aktif && !fullscreenAktif) {

            fullscreenAktif = true;

            aturSidebar(true);

        } else if (!aktif && fullscreenAktif) {

            fullscreenAktif = false;

            aturSidebar(
                sidebarSebelumFullscreen
            );

        }

        fullscreenButton.setAttribute(
            'aria-pressed',
            String(aktif)
        );

        select('pk-fullscreen-text').textContent =
            aktif
                ? 'Keluar fullscreen'
                : 'Fullscreen';

        fullscreenButton.title = aktif
            ? 'Keluar fullscreen (Esc)'
            : 'Tampilkan layar penuh';

        fullscreenButton.setAttribute(
            'aria-label',
            fullscreenButton.title
        );

        select('pk-fullscreen-icon').innerHTML = aktif

            ? '<path d="M3 8h5V3M21 8h-5V3M16 21v-5h5M8 21v-5H3"/>'

            : '<path d="M8 3H3v5M16 3h5v5M21 16v5h-5M8 21H3v-5"/>';

        jadwalkanRender();

    }

    function masukFallback() {

        bodyOverflowSebelumnya =
            document.body.style.overflow;

        document.body.style.overflow = 'hidden';

        shell.classList.add(
            'pk-fullscreen-fallback'
        );

        sinkronFullscreen();

    }

    function keluarFallback() {

        shell.classList.remove(
            'pk-fullscreen-fallback'
        );

        document.body.style.overflow =
            bodyOverflowSebelumnya;

        sinkronFullscreen();

    }

    async function toggleFullscreen() {

        if (fullscreenNativeAktif()) {

            try {

                await document.exitFullscreen();

            } catch (err) {

                console.warn(
                    'Gagal keluar fullscreen:',
                    err
                );

            }

            return;

        }

        if (fullscreenFallbackAktif()) {

            keluarFallback();

            return;

        }

        sidebarSebelumFullscreen =
            shell.classList.contains(
                'pk-sidebar-hidden'
            );

        if (
            typeof shell.requestFullscreen === 'function'
            && document.fullscreenEnabled !== false
        ) {

            try {

                await shell.requestFullscreen();

                sinkronFullscreen();

            } catch (err) {

                masukFallback();

            }

        } else {

            masukFallback();

        }

    }

    fullscreenButton.addEventListener(
        'click',
        toggleFullscreen
    );

    sidebarButton.addEventListener('click', () => {

        const tersembunyi = shell.classList.contains(
            'pk-sidebar-hidden'
        );

        aturSidebar(!tersembunyi);

    });

    document.addEventListener(
        'fullscreenchange',
        sinkronFullscreen
    );

    document.addEventListener('keydown', e => {

        if (
            e.key === 'Escape'
            && fullscreenFallbackAktif()
        ) {

            e.preventDefault();

            keluarFallback();

        }

    });

    // Menampilkan sidebar ketika pengguna
    // memilih titik atau garis konektivitas.

    function tampilkanDetail() {

        if (
            shell.classList.contains(
                'pk-sidebar-hidden'
            )
        ) {

            aturSidebar(false);

        }

        // Pastikan ukuran peta sudah sesuai
        // sebelum menjalankan animasi fokus.

        render();

        requestAnimationFrame(() => {

            const detailElement = select('pk-detail');

            const panelRect =
                panel.getBoundingClientRect();

            const detailRect =
                detailElement.getBoundingClientRect();

            panel.scrollTo({

                top: panel.scrollTop
                    + detailRect.top
                    - panelRect.top
                    - 16,

                behavior:'smooth'

            });

        });

    }

    // =====================================================
    // FOKUS PETA
    // =====================================================

    function fokusKoordinat(coords, maxScale = 8) {

        if (!geo || !coords.length) {
            return;
        }

        const validCoords = coords.filter(c =>
            Array.isArray(c)
            && Number.isFinite(c[0])
            && Number.isFinite(c[1])
        );

        if (!validCoords.length) {
            return;
        }

        const w = svg.node().clientWidth || 900;
        const h = svg.node().clientHeight || 650;

        const xs = validCoords.map(c => c[0]);
        const ys = validCoords.map(c => c[1]);

        const minX = Math.min(...xs);
        const maxX = Math.max(...xs);
        const minY = Math.min(...ys);
        const maxY = Math.max(...ys);

        const dx = Math.max(maxX - minX, 25);
        const dy = Math.max(maxY - minY, 25);

        const k = Math.max(
            1,
            Math.min(
                maxScale,
                .77 * Math.min(w / dx, h / dy)
            )
        );

        const cx = (minX + maxX) / 2;
        const cy = (minY + maxY) / 2;

        const target = d3.zoomIdentity

            .translate(
                w / 2 - k * cx,
                h / 2 - k * cy
            )

            .scale(k);

        svg.interrupt()
            .transition()
            .duration(650)
            .call(zoom.transform, target);

    }

    function fokusRute(r) {

        const a = byId.get(r.asal);
        const b = byId.get(r.tujuan);

        if (a && b) {

            fokusKoordinat([
                projection([a.lon, a.lat]),
                projection([b.lon, b.lat])
            ], 8);

        }

    }

    function fokusWilayah(p) {

        if (p) {

            fokusKoordinat([
                projection([p.lon, p.lat])
            ], 7);

        }

    }

    // =====================================================
    // DATA KOMODITAS SESUAI FILTER
    // =====================================================

    function komoditasTerfilter(r) {

        const sektor = select('pk-sektor').value;
        const nama = select('pk-komoditas').value;

        return (r.komoditas || []).filter(k =>

            cocokSektor(k, sektor)

            && (
                !nama
                || normalize(k.nama) === nama
            )

        );

    }

    // =====================================================
    // FILTER SELURUH RUTE
    // =====================================================

    function shown() {

        const moda = select('pk-moda').value;
        const provinsi = select('pk-prov').value;
        const sektor = select('pk-sektor').value;
        const komoditas = select('pk-komoditas').value;
        const jarak = select('pk-jarak').value;

        return routes.filter(r => {

            if (!enabled.has(r.klasifikasi)) {
                return false;
            }

            if (moda && String(r.moda) !== moda) {
                return false;
            }

            if (
                provinsi
                && byId.get(r.asal)?.provinsi !== provinsi
            ) {
                return false;
            }

            if (
                sektor
                && !(r.sektor || []).some(s =>
                    normalize(s) === normalize(sektor)
                )
                && !komoditasTerfilter(r).length
            ) {
                return false;
            }

            if (
                komoditas
                && !komoditasTerfilter(r).length
            ) {
                return false;
            }

            if (
                jarak
                && r.kelasJarak !== jarak
            ) {
                return false;
            }

            return true;

        });

    }

    // =====================================================
    // TABEL KOMODITAS
    // =====================================================

    function tabelKomoditas(r) {

        const data = komoditasTerfilter(r);

        const rows = data.length

            ? data.map(k => {

                const jumlah = jumlahValid(k.jumlah)
                    ? fmt(Number(k.jumlah))
                    : '–';

                const sektor = Array.isArray(k.sektor)
                    ? k.sektor.join(', ')
                    : k.sektor;

                return `

                    <tr>

                        <td>

                            ${esc(k.nama)}

                            <div class="pk-muted">

                                ${esc(
                                    sektor ||
                                    'Sektor belum terpetakan'
                                )}

                            </div>

                        </td>

                        <td>

                            ${jumlah}

                            ${esc(
                                k.satuan ||
                                '(satuan belum tersedia)'
                            )}

                        </td>

                    </tr>

                `;

            }).join('')

            : `

                <tr>
                    <td colspan="2">
                        Belum ada detail komoditas
                        yang sesuai filter.
                    </td>
                </tr>

            `;

        return `

            <table>

                <thead>
                    <tr>
                        <th>Komoditas / sektor</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>

                <tbody>
                    ${rows}
                </tbody>

            </table>

        `;

    }

    // =====================================================
    // DETAIL SATU RUTE
    // =====================================================

    function detail(r) {

        if (!r) {

            select('pk-detail').innerHTML = `

                <h3>Detail konektivitas</h3>

                <p>
                    Pilih garis atau titik pada peta
                    untuk melihat rute dan komoditasnya.
                </p>

            `;

            return;

        }

        const a = byId.get(r.asal);
        const b = byId.get(r.tujuan);

        select('pk-detail').innerHTML = `

            <h3>
                ${esc(a?.nama || 'Asal')}
                →
                ${esc(b?.nama || 'Tujuan')}
            </h3>

            <p>
                ${esc(r.laporan)}
                · ${esc(r.klasifikasi)}
                · ${esc(r.moda)}
            </p>

            <p>
                Jarak lurus:
                <strong>${esc(r.jarak)} km</strong>
            </p>

            ${tabelKomoditas(r)}

        `;

    }

    // =====================================================
    // DETAIL SELURUH PENGAJUAN
    // =====================================================

    function kelompokWilayah(relasi, arah) {

        const kelompok = new Map();

        relasi.forEach(r => {

            const id = arah === 'keluar'
                ? r.tujuan
                : r.asal;

            if (!kelompok.has(id)) {
                kelompok.set(id, []);
            }

            kelompok.get(id).push(r);

        });

        return [...kelompok.entries()]

            .sort((a, b) =>

                String(byId.get(a[0])?.nama || '')

                    .localeCompare(
                        String(byId.get(b[0])?.nama || ''),
                        'id'
                    )

            )

            .map(([id, daftar], index) => {

                const wilayah = byId.get(id);

                return `

                    <div class="pk-related-region">

                        <h4>
                            ${index + 1}.
                            ${esc(
                                wilayah?.nama ||
                                'Wilayah tidak diketahui'
                            )}
                        </h4>

                        <p>
                            ${daftar.length} hubungan pengajuan
                        </p>

                        ${daftar.map((r, i) => `

                            <div class="pk-report-card">

                                <div class="pk-report-number">
                                    Pengajuan ${i + 1}
                                </div>

                                <p>
                                    <strong>
                                        ${esc(r.laporan)}
                                    </strong>
                                </p>

                                <p>
                                    ${esc(r.klasifikasi)}
                                    · ${esc(r.moda)}
                                </p>

                                <p>
                                    Jarak lurus:
                                    <strong>
                                        ${esc(r.jarak)} km
                                    </strong>
                                </p>

                                ${tabelKomoditas(r)}

                                <button
                                    type="button"
                                    class="pk-report-focus"
                                    data-route-index="${routeKeys.get(r)}"
                                >
                                    Lihat rute pada peta →
                                </button>

                            </div>

                        `).join('')}

                    </div>

                `;

            })

            .join('');

    }

    // =====================================================
    // DETAIL DAERAH ASAL DAN TUJUAN
    // =====================================================

    function detailWilayah(id, rs) {

        const wilayah = byId.get(id);

        const keluar = rs.filter(r => r.asal === id);
        const masuk = rs.filter(r => r.tujuan === id);

        if (
            !wilayah
            || (!keluar.length && !masuk.length)
        ) {

            detail(null);

            return;

        }

        const jumlahTujuan = new Set(
            keluar.map(r => r.tujuan)
        ).size;

        const jumlahAsal = new Set(
            masuk.map(r => r.asal)
        ).size;

        select('pk-detail').innerHTML = `

            <h3>
                ${esc(wilayah.nama)}
            </h3>

            <p>
                ${esc(wilayah.provinsi || '')}
            </p>

            ${keluar.length ? `

                <div class="pk-direction pk-direction-out">

                    <strong>
                        Sebagai daerah asal
                    </strong>

                    <div>
                        ${jumlahTujuan} daerah tujuan
                        · ${keluar.length} hubungan
                    </div>

                </div>

                ${kelompokWilayah(keluar, 'keluar')}

            ` : ''}

            ${masuk.length ? `

                <div class="pk-direction pk-direction-in">

                    <strong>
                        Sebagai daerah tujuan
                    </strong>

                    <div>
                        ${jumlahAsal} daerah asal
                        · ${masuk.length} hubungan
                    </div>

                </div>

                ${kelompokWilayah(masuk, 'masuk')}

            ` : ''}

        `;

    }

    function pilihWilayah(p) {

        selected = null;
        selectedWilayah = p.id;

        update();

        tampilkanDetail();

        fokusWilayah(p);

    }

    function pilihRute(r) {

        selected = r;
        selectedWilayah = null;

        update();

        tampilkanDetail();

        fokusRute(r);

    }

    select('pk-detail').addEventListener('click', e => {

        const button = e.target.closest(
            '[data-route-index]'
        );

        if (!button) {
            return;
        }

        const index = Number(
            button.dataset.routeIndex
        );

        const r = routes[index];

        if (r && shown().includes(r)) {

            pilihRute(r);

        }

    });

    // =====================================================
    // RINGKASAN POTENSI KOMODITAS
    // =====================================================

    function ringkasan(rs, origins, destinations) {

        const groups = new Map();

        let unknown = 0;

        rs.forEach(r => {

            komoditasTerfilter(r).forEach(k => {

                const nama = String(k.nama ?? '').trim();

                const satuan = String(k.satuan ?? '').trim();

                if (
                    !nama
                    || !satuan
                    || !jumlahValid(k.jumlah)
                ) {

                    unknown++;

                    return;

                }

                // Komoditas dan satuan berbeda
                // tidak boleh dijumlahkan bersama.

                const key = JSON.stringify([
                    normalize(nama),
                    normalize(satuan)
                ]);

                if (!groups.has(key)) {

                    groups.set(key, {
                        nama,
                        satuan,
                        total:0
                    });

                }

                groups.get(key).total += Number(k.jumlah);

            });

        });

        const rows = [...groups.values()]

            .sort((a, b) =>

                a.nama.localeCompare(b.nama, 'id')

                || a.satuan.localeCompare(b.satuan, 'id')

            );

        const jumlahProvinsi = new Set(

            [...origins]

                .map(id => byId.get(id)?.provinsi)

                .filter(Boolean)

        ).size;

        const stats = [

            ['Hubungan konektivitas', rs.length],

            ['Daerah asal', origins.size],

            ['Simpul tujuan', destinations.size],

            ['Provinsi asal', jumlahProvinsi]

        ];

        select('pk-summary').innerHTML = `

            <div class="pk-stat-grid">

                ${stats.map(([label, value]) => `

                    <div class="pk-stat">

                        <span>
                            ${esc(label)}
                        </span>

                        <strong>
                            ${fmt(value)}
                        </strong>

                    </div>

                `).join('')}

            </div>

            <div class="pk-potensi">

                <strong>
                    Potensi komoditas sesuai filter
                </strong>

                ${rows.length ? `

                    <div class="pk-potensi-list">

                        ${rows.map(g => `

                            <div class="pk-potensi-row">

                                <span>
                                    ${esc(g.nama)}
                                </span>

                                <strong>
                                    ${fmt(g.total)}
                                    ${esc(g.satuan)}
                                </strong>

                            </div>

                        `).join('')}

                    </div>

                ` : `

                    <div class="pk-summary-note">
                        Belum ada jumlah komoditas
                        dengan satuan valid yang
                        sesuai filter.
                    </div>

                `}

            </div>

            ${unknown ? `

                <div class="pk-summary-warning">
                    ${unknown} rincian tanpa jumlah
                    atau satuan valid tidak dijumlahkan.
                </div>

            ` : ''}

            <div class="pk-summary-note">
                Total berdasarkan rincian tujuan
                pengajuan, bukan produksi unik wilayah.
                Komoditas dan satuan berbeda
                tidak digabungkan.
            </div>

        `;

    }

    // =====================================================
    // GARIS KONEKTIVITAS
    // =====================================================

    function curvePath(r) {

        const a = byId.get(r.asal);
        const b = byId.get(r.tujuan);

        if (!a || !b) {
            return '';
        }

        const [x1, y1] = projection([a.lon, a.lat]);
        const [x2, y2] = projection([b.lon, b.lat]);

        const mx = (x1 + x2) / 2;
        const my = (y1 + y2) / 2;

        const dx = x2 - x1;
        const dy = y2 - y1;

        const bend = .18;

        return `M${x1},${y1}Q${mx-dy*bend},${my+dx*bend} ${x2},${y2}`;

    }

    // =====================================================
    // UPDATE PETA
    // =====================================================

    function update() {

        if (!geo) {
            return;
        }

        const rs = shown();

        const origins = new Set(
            rs.map(r => r.asal)
        );

        const destinations = new Set(
            rs.map(r => r.tujuan)
        );

        const ids = new Set([
            ...origins,
            ...destinations
        ]);

        activeOrigins = origins;

        ringkasan(
            rs,
            origins,
            destinations
        );

        if (
            selected
            && !rs.includes(selected)
        ) {

            selected = null;

        }

        if (
            selectedWilayah !== null
            && !ids.has(selectedWilayah)
        ) {

            selectedWilayah = null;

        }

        const terkait = r => {

            if (selected) {

                return r === selected;

            }

            return selectedWilayah !== null

                && (
                    r.asal === selectedWilayah
                    || r.tujuan === selectedWilayah
                );

        };

        select('pk-empty').style.display =
            rs.length ? 'none' : 'block';

        select('pk-empty').textContent =

            'Belum ada rute Verifikasi Final yang ' +

            'sesuai filter dan memiliki koordinat lengkap.';

        // -------------------------------------------------
        // GAMBAR GARIS
        // -------------------------------------------------

        const line = links

            .selectAll('path.pk-line')

            .data(
                rs,
                r => routeKeys.get(r)
            );

        line.exit().remove();

        line.enter()

            .append('path')

            .attr('class', 'pk-line')

            .append('title');

        links.selectAll('path.pk-line')

            .on('click', (e, r) => {

                e.stopPropagation();

                pilihRute(r);

            })

            .attr('d', curvePath)

            .attr('stroke', warnaGaris)

            .classed('pk-selected', terkait)

            .classed('pk-dimmed', r =>

                (
                    selected !== null
                    || selectedWilayah !== null
                )

                && !terkait(r)

            )

            .select('title')

            .text(r =>

                `${byId.get(r.asal)?.nama || 'Asal'} → ` +

                `${byId.get(r.tujuan)?.nama || 'Tujuan'}: ` +

                `${r.jarak} km`

            );

        // -------------------------------------------------
        // GAMBAR TITIK
        // -------------------------------------------------

        const p = points.filter(p =>
            ids.has(p.id)
        );

        const circles = dots

            .selectAll('circle.pk-point')

            .data(p, p => p.id);

        circles.exit().remove();

        circles.enter()

            .append('circle')

            .attr('class', 'pk-point')

            .append('title');

        dots.selectAll('circle.pk-point')

            .on('click', (e, p) => {

                e.stopPropagation();

                pilihWilayah(p);

            })

            .attr('cx', p =>
                projection([p.lon, p.lat])[0]
            )

            .attr('cy', p =>
                projection([p.lon, p.lat])[1]
            )

            .attr('r', radiusTitik)

            .attr('fill', p =>

                origins.has(p.id)

                    ? color(
                        rs.find(r =>
                            r.asal === p.id
                        )?.klasifikasi || ''
                    )

                    : '#15263f'

            )

            .select('title')

            .text(p => p.nama);

        // -------------------------------------------------
        // UPDATE DETAIL
        // -------------------------------------------------

        if (selectedWilayah !== null) {

            detailWilayah(
                selectedWilayah,
                rs
            );

        } else {

            detail(selected);

        }

    }

    // =====================================================
    // PENCARIAN WILAYAH, RUTE, DAN KOMODITAS
    // =====================================================

    const searchInput = select('pk-search');

    const searchResults = select('pk-search-results');

    function tutupHasil() {

        searchResults.hidden = true;

        searchInput.setAttribute(
            'aria-expanded',
            'false'
        );

    }

    function cariKonektivitas() {

        const q = normalize(searchInput.value);

        searchResults.replaceChildren();

        if (!q) {

            tutupHasil();

            return;

        }

        const rs = shown();

        const ids = new Set(
            rs.flatMap(r => [r.asal, r.tujuan])
        );

        const wilayah = points.filter(p =>

            ids.has(p.id)

            && normalize(
                `${p.nama} ${p.provinsi ?? ''}`
            ).includes(q)

        ).slice(0, 8);

        const rute = rs.filter(r => {

            const namaKomoditas = komoditasTerfilter(r)

                .map(k => k.nama)

                .join(' ');

            return normalize(

                `${r.laporan ?? ''} ` +

                `${byId.get(r.asal)?.nama ?? ''} ` +

                `${byId.get(r.tujuan)?.nama ?? ''} ` +

                `${namaKomoditas}`

            ).includes(q);

        }).slice(0, 10);

        const add = (title, sub, fn) => {

            const b = document.createElement('button');

            b.type = 'button';

            b.className = 'pk-search-item';

            b.textContent = title;

            const small = document.createElement('small');

            small.textContent = sub;

            b.appendChild(small);

            b.addEventListener('click', () => {

                fn();

                searchInput.value = title;

                tutupHasil();

            });

            searchResults.appendChild(b);

        };

        wilayah.forEach(p => {

            add(

                p.nama,

                p.provinsi
                    ? `Wilayah · ${p.provinsi}`
                    : 'Wilayah',

                () => pilihWilayah(p)

            );

        });

        rute.forEach(r => {

            const namaKomoditas = komoditasTerfilter(r)

                .map(k => k.nama)

                .filter(Boolean)

                .slice(0, 3)

                .join(', ');

            add(

                `${byId.get(r.asal)?.nama ?? 'Asal'} → ` +

                `${byId.get(r.tujuan)?.nama ?? 'Tujuan'}`,

                `Rute · ${r.laporan ?? 'Tanpa nomor laporan'} ` +

                `· ${r.moda ?? ''}` +

                (
                    namaKomoditas
                        ? ` · ${namaKomoditas}`
                        : ''
                ),

                () => pilihRute(r)

            );

        });

        if (!wilayah.length && !rute.length) {

            const hint = document.createElement('div');

            hint.className = 'pk-search-hint';

            hint.textContent =
                'Tidak ada hasil pada filter aktif.';

            searchResults.appendChild(hint);

        }

        searchResults.hidden = false;

        searchInput.setAttribute(
            'aria-expanded',
            'true'
        );

    }

    searchInput.addEventListener(
        'input',
        cariKonektivitas
    );

    searchInput.addEventListener('keydown', e => {

        if (e.key === 'Escape') {

            tutupHasil();

        }

    });

    document.addEventListener('click', e => {

        if (
            !searchInput.contains(e.target)
            && !searchResults.contains(e.target)
        ) {

            tutupHasil();

        }

    });

    // =====================================================
    // SMART LABEL KOTA
    // =====================================================

    function gambarLabelKota() {

        if (!geo) {
            return;
        }

        const mode = select('pk-kota').value;

        let tampil = [];

        if (mode === 'semua') {

            tampil = kotaReferensi;

        } else if (mode === 'utama') {

            tampil = kotaReferensi.filter(k =>
                k.utama
            );

        } else if (mode === 'otomatis') {

            if (currentZoom < 1.8) {

                tampil = kotaReferensi.filter(k =>
                    k.utama
                );

            } else if (currentZoom < 3) {

                tampil = kotaReferensi

                    .filter(k => k.utama)

                    .filter((k, i) => i % 2 === 0);

            }

        }

        cityLayer.selectAll('circle')

            .data(tampil, d => d.nama)

            .join('circle')

            .attr('class', 'pk-city-dot')

            .attr('cx', d =>
                projection([d.lon, d.lat])[0]
            )

            .attr('cy', d =>
                projection([d.lon, d.lat])[1]
            )

            .attr('r', d =>
                (d.utama ? 3 : 2) / currentZoom
            )

            .style('stroke-width', 1.2);

        cityLayer.selectAll('text')

            .data(tampil, d => d.nama)

            .join('text')

            .attr('class', d =>

                'pk-city-label'

                + (d.utama ? ' pk-city-major' : '')

            )

            .attr('x', d =>

                projection([d.lon, d.lat])[0]

                + 6 / currentZoom

            )

            .attr('y', d =>

                projection([d.lon, d.lat])[1]

                - 5 / currentZoom

            )

            .style('font-size', d =>

                `${(d.utama ? 12 : 10) / currentZoom}px`

            )

            .style(
                'stroke-width',
                `${3 / currentZoom}px`
            )

            .text(d => d.nama);

    }

    // =====================================================
    // SMART LABEL PERAIRAN
    // =====================================================

    function gambarLabelPerairan() {

        if (!geo) {
            return;
        }

        const mode = select('pk-perairan').value;

        const tampil = mode === 'sembunyi'

            ? []

            : perairanReferensi.filter(d =>

                mode === 'semua'

                || (
                    mode === 'utama'
                    && d.utama
                )

                || (
                    mode === 'otomatis'
                    && currentZoom < 3
                    && d.utama
                )

            );

        waterLayer.selectAll('text')

            .data(tampil, d => d.nama)

            .join('text')

            .attr('class', d =>

                'pk-water-label'

                + (
                    d.jenis === 'samudra'
                        ? ' pk-water-ocean'
                        : ''
                )

                + (
                    d.jenis === 'selat'
                        ? ' pk-water-strait'
                        : ''
                )

            )

            .attr('x', d =>
                projection([d.lon, d.lat])[0]
            )

            .attr('y', d =>
                projection([d.lon, d.lat])[1]
            )

            .style('font-size', d =>

                `${(
                    d.jenis === 'samudra'
                        ? 13
                        : d.jenis === 'selat'
                            ? 9
                            : 11
                ) / currentZoom}px`

            )

            .style(
                'stroke-width',
                `${3 / currentZoom}px`
            )

            .text(d => d.nama);

    }

    // =====================================================
    // RENDER PETA ADAPTIF
    // Mempertahankan pusat geografis dan level zoom
    // saat fullscreen atau sidebar berubah ukuran.
    // =====================================================

    function render() {

        if (!geo) {
            return;
        }

        const w = svg.node().clientWidth;
        const h = svg.node().clientHeight;

        if (w <= 40 || h <= 40) {
            return;
        }

        if (
            ukuranPeta
            && ukuranPeta.w === w
            && ukuranPeta.h === h
        ) {

            return;

        }

        svg.interrupt();

        const transformLama = d3.zoomTransform(
            svg.node()
        );

        let pusatGeografis = null;

        let tampilanAwal = true;

        if (ukuranPeta) {

            tampilanAwal =

                Math.abs(transformLama.k - 1) < 0.0001

                && Math.abs(transformLama.x) < 0.0001

                && Math.abs(transformLama.y) < 0.0001;

            const pusatLama = transformLama.invert([

                ukuranPeta.w / 2,

                ukuranPeta.h / 2

            ]);

            pusatGeografis = projection.invert(
                pusatLama
            );

        }

        svg.attr(
            'viewBox',
            `0 0 ${w} ${h}`
        );

        projection.fitExtent([

            [20, 20],

            [w - 20, h - 20]

        ], geo);

        ukuranPeta = {w, h};

        land.selectAll('path')

            .data(geo.features)

            .join('path')

            .attr('class', 'pk-land')

            .attr('d', path)

            .selectAll('title')

            .data(d => [d])

            .join('title')

            .text(d =>
                d.properties?.provinsi || ''
            );

        let target = d3.zoomIdentity;

        if (
            pusatGeografis
            && !tampilanAwal
            && pusatGeografis.every(Number.isFinite)
        ) {

            const pusatBaru = projection(
                pusatGeografis
            );

            const k = transformLama.k;

            target = d3.zoomIdentity

                .translate(

                    w / 2 - k * pusatBaru[0],

                    h / 2 - k * pusatBaru[1]

                )

                .scale(k);

        }

        svg.call(
            zoom.transform,
            target
        );

        update();

        gambarLabelKota();

        gambarLabelPerairan();

    }

    // =====================================================
    // EVENT FILTER
    // =====================================================

    for (const id of [

        'pk-moda',

        'pk-prov',

        'pk-sektor',

        'pk-komoditas',

        'pk-jarak'

    ]) {

        select(id).addEventListener('change', () => {

            if (id === 'pk-sektor') {

                isiKomoditas();

            }

            update();

            cariKonektivitas();

        });

    }

    // =====================================================
    // RESET SELURUH FILTER
    // =====================================================

    select('pk-reset-filter')

        .addEventListener('click', () => {

            for (const id of [

                'pk-moda',

                'pk-prov',

                'pk-sektor',

                'pk-komoditas',

                'pk-jarak'

            ]) {

                // Moda terkunci berdasarkan
                // hak akses tidak diubah.

                if (!select(id).disabled) {

                    select(id).value = '';

                }

            }

            enabled.clear();

            classes.forEach(k => enabled.add(k));

            select('pk-klas')

                .querySelectorAll('button')

                .forEach(b => {

                    b.setAttribute(
                        'aria-pressed',
                        'true'
                    );

                });

            isiKomoditas();

            selected = null;

            selectedWilayah = null;

            searchInput.value = '';

            tutupHasil();

            select('pk-kota').value = 'otomatis';

            select('pk-perairan').value = 'otomatis';

            if (geo) {

                update();

                gambarLabelKota();

                gambarLabelPerairan();

                svg.interrupt()

                    .transition()

                    .duration(500)

                    .call(
                        zoom.transform,
                        d3.zoomIdentity
                    );

            }

        });

    // =====================================================
    // EVENT LABEL
    // =====================================================

    select('pk-kota').addEventListener(
        'change',
        gambarLabelKota
    );

    select('pk-perairan').addEventListener(
        'change',
        gambarLabelPerairan
    );

    // =====================================================
    // KONTROL ZOOM
    // =====================================================

    select('pk-plus').addEventListener('click', () => {

        svg.interrupt()

            .transition()

            .duration(250)

            .call(
                zoom.scaleBy,
                1.5
            );

    });

    select('pk-minus').addEventListener('click', () => {

        svg.interrupt()

            .transition()

            .duration(250)

            .call(
                zoom.scaleBy,
                1 / 1.5
            );

    });

    select('pk-reset').addEventListener('click', () => {

        svg.interrupt()

            .transition()

            .duration(500)

            .call(
                zoom.transform,
                d3.zoomIdentity
            );

    });

    // =====================================================
    // LOAD GEOJSON INDONESIA
    // =====================================================

    fetch(@json(asset('data/indonesia-provinsi.json')))

        .then(r => {

            if (!r.ok) {

                throw Error(
                    'GeoJSON tidak ditemukan'
                );

            }

            return r.json();

        })

        .then(g => {

            geo = g;

            render();

            if (typeof ResizeObserver !== 'undefined') {

                const observer = new ResizeObserver(
                    jadwalkanRender
                );

                observer.observe(svg.node());

            } else {

                window.addEventListener(
                    'resize',
                    jadwalkanRender
                );

            }

        })

        .catch(err => {

            select('pk-summary').textContent =
                'Data peta belum dapat dimuat.';

            select('pk-empty').style.display =
                'block';

            select('pk-empty').textContent =

                'Gagal memuat GeoJSON peta: '

                + err.message;

        });

})();
</script>

@endsection
