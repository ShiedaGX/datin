@extends('layouts.app')

@section('title', 'Master Sektor & Komoditas')
@section('heading', 'Master Sektor & Komoditas')
@section('subheading', 'Kelola sektor dan komoditas untuk pengajuan subsidi transportasi')

@section('content')

@php
    $idTerpilih = $terpilih?->id_sektor;

    $tautan = fn ($id = null, array $extra = []) => route(
        'admin.sektor.index',
        array_filter(
            ['sektor' => $id] + $extra,
            fn ($v) => $v !== null && $v !== ''
        )
    );
@endphp

<style>
    /* =====================================================
       LAYOUT MASTER SEKTOR
    ===================================================== */

    .ms-grid {
        display: grid;
        grid-template-columns: 252px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
        width: 100%;
    }

    .ms-grid > .ms-side,
    .ms-grid > .ms-main {
        margin: 0 !important;
        margin-top: 0 !important;
        align-self: start;
        min-width: 0;
        box-sizing: border-box;
    }

    /* =====================================================
       SIDEBAR SEKTOR
    ===================================================== */

    .ms-side {
        padding: 20px;
    }

    .ms-side h2 {
        margin: 0 0 14px;
        color: #18324f;
        font-size: 15px;
        font-weight: 700;
    }

    .ms-list {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 350px;
        overflow-y: auto;
        scrollbar-width: thin;
    }

    .ms-list li {
        margin: 0 0 4px;
    }

    .ms-list a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 38px;
        padding: 9px 11px;
        border-radius: 8px;
        color: #43546b;
        text-decoration: none;
        font-size: 12px;
        line-height: 1.5;
        transition: background .2s, color .2s;
    }

    .ms-list a:hover {
        background: #f1f6f8;
        color: #087e77;
    }

    .ms-list a.aktif {
        background: #d8f3ed;
        color: #087c73;
        font-weight: 700;
    }

    .ms-list a span {
        flex: 1;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .ms-list a b {
        flex-shrink: 0;
        min-width: 26px;
        padding: 3px 7px;
        border-radius: 20px;
        background: #e8edf3;
        color: #52657a;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
    }

    .ms-list a.aktif b {
        background: #fff;
        color: #087c73;
    }

    /* =====================================================
       TAMBAH SEKTOR
    ===================================================== */

    .ms-tambah {
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #e4eaf0;
    }

    .ms-tambah summary {
        color: #087c73;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        list-style: none;
    }

    .ms-tambah summary::-webkit-details-marker {
        display: none;
    }

    .ms-tambah summary::before {
        content: "+";
        display: inline-block;
        margin-right: 7px;
        font-size: 16px;
    }

    .ms-tambah[open] summary::before {
        content: "−";
    }

    .ms-tambah form {
        margin-top: 15px;
    }

    .ms-tambah .field {
        margin-bottom: 12px;
    }

    .ms-tambah label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
    }

    .ms-tambah input {
        display: block;
        width: 100%;
        height: 39px;
        padding: 9px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #1e293b;
        font: inherit;
        font-size: 12px;
        box-sizing: border-box;
    }

    .ms-tambah button {
        width: 100%;
        justify-content: center;
    }

    /* =====================================================
       KONTEN UTAMA
    ===================================================== */

    .ms-main {
        padding: 20px;
    }

    .ms-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .ms-head h2 {
        margin: 0 0 5px;
        color: #18324f;
        font-size: 16px;
        font-weight: 700;
    }

    .ms-head p {
        margin: 0;
        font-size: 12px;
        line-height: 1.6;
    }

    /* =====================================================
       PENCARIAN
    ===================================================== */

    .ms-cari {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ms-cari input {
        width: 200px;
        height: 39px;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #1e293b;
        font: inherit;
        font-size: 12px;
        box-sizing: border-box;
    }

    .ms-cari input::placeholder {
        color: #94a3b8;
    }

    .ms-cari input:focus,
    .ms-tambah input:focus,
    .ms-tools input:focus,
    .ms-table input:focus {
        outline: none;
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, .10);
    }

    /* =====================================================
       TOOLS KOMODITAS
    ===================================================== */

    .ms-tools {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 14px;
        border: 1px solid #e2eaf0;
        border-radius: 9px;
        background: #f8fafc;
    }

    .ms-tambah-k {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1 1 300px;
        min-width: 0;
    }

    .ms-tambah-k input {
        flex: 1;
        min-width: 0;
        height: 39px;
        padding: 9px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        font: inherit;
        font-size: 12px;
        box-sizing: border-box;
    }

    .ms-tools .btn {
        min-height: 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
    }

    /* =====================================================
       UBAH / HAPUS SEKTOR
    ===================================================== */

    .ms-ubah {
        position: relative;
    }

    .ms-ubah summary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 39px;
        padding: 0 12px;
        border: 1px solid #d0dbe5;
        border-radius: 7px;
        background: #fff;
        color: #465b70;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .ms-ubah-isi {
        margin-top: 12px;
        padding: 13px;
        background: #fff;
        border: 1px solid #dce5ed;
        border-radius: 8px;
    }

    .ms-ubah-isi form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    .ms-ubah-isi form:last-child {
        margin-bottom: 0;
    }

    .ms-ubah-isi input {
        height: 38px;
        min-width: 0;
        padding: 8px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        font: inherit;
        font-size: 12px;
        box-sizing: border-box;
    }

    .ms-ubah-isi input[name="nama_sektor"] {
        flex: 1 1 180px;
    }

    .ms-ubah-isi .kecil {
        width: 80px;
    }

    /* =====================================================
       HINT
    ===================================================== */

    .ms-hint {
        margin: 0 0 18px;
        padding: 13px 15px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
    }

    /* =====================================================
       TABEL KOMODITAS
    ===================================================== */

    .ms-table {
        width: 100%;
    }

    .ms-table th,
    .ms-table td {
        vertical-align: middle;
    }

    .ms-table .num {
        text-align: center;
        width: 90px;
    }

    .ms-table .td-aksi {
        width: 65px;
    }

    .baris-form {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .baris-form input {
        flex: 1;
        min-width: 120px;
        height: 35px;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #1e293b;
        font: inherit;
        font-size: 12px;
        box-sizing: border-box;
    }

    .baris-form .btn {
        min-height: 35px;
        padding: 0 12px;
        font-size: 12px;
        white-space: nowrap;
    }

    .ms-table .acts {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .ms-table .acts form {
        margin: 0;
    }

    .ms-table .acts .act {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* =====================================================
       PAGINATION
    ===================================================== */

    .ms-pager {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    /* =====================================================
       RESPONSIVE
    ===================================================== */

    @media (max-width: 1050px) {
        .ms-grid {
            grid-template-columns: 220px minmax(0, 1fr);
            gap: 14px;
        }
    }

    @media (max-width: 900px) {
        .ms-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .ms-side,
        .ms-main {
            width: 100%;
        }

        .ms-list {
            max-height: 230px;
        }
    }

    @media (max-width: 650px) {
        .ms-side,
        .ms-main {
            padding: 15px;
        }

        .ms-head {
            flex-direction: column;
            align-items: stretch;
        }

        .ms-cari {
            width: 100%;
        }

        .ms-cari input {
            flex: 1;
            width: auto;
            min-width: 0;
        }

        .ms-tambah-k {
            flex-wrap: wrap;
        }

        .ms-tambah-k input {
            flex-basis: 100%;
        }

        .baris-form {
            flex-wrap: wrap;
        }

        .baris-form input {
            flex-basis: 100%;
        }

        .ms-table .num {
            text-align: left;
        }

        .ms-pager {
            justify-content: center;
        }
    }

/* Tab navigasi */
.ms-tabs{display:flex;gap:8px;margin:0 0 18px;flex-wrap:wrap;border-bottom:1px solid #e5edf2;padding-bottom:12px}
.ms-tabs a{padding:10px 17px;border-radius:9px;background:#f1f5f9;color:#43566d;text-decoration:none;font-weight:700;font-size:13px;transition:.2s}
.ms-tabs a:hover{background:#dff3f0;color:#087f83}
.ms-tabs a.is-active{background:#087f83;color:#fff}
/* Pencarian komoditas lintas sektor */
.ms-search-komoditas{margin:0 0 18px;padding:16px;border:1px solid #dcebe8;background:#f5fbf9;border-radius:12px}
.ms-search-komoditas-title{font-weight:700;color:#18324f;font-size:13px;margin:0 0 9px}
.ms-search-komoditas form{display:flex;gap:9px;flex-wrap:wrap}
.ms-search-komoditas input{flex:1 1 240px;min-width:0;height:43px;padding:0 13px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;font:inherit;font-size:13px}
.ms-search-komoditas input:focus{outline:none;border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.12)}
.ms-search-komoditas button{height:43px}
.ms-search-komoditas small{display:block;color:#64748b;margin-top:9px;line-height:1.6}
/* Analisis: seluruh style dibatasi agar tidak mengganggu CRUD */
.ms-analytics{padding:26px;min-width:0;color:#18324f}
.ms-analytics *{box-sizing:border-box}
.ms-analytics-head{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:22px}
.ms-analytics-head h2{font-size:20px;color:#18324f;margin:0 0 8px;line-height:1.35}
.ms-analytics-head p{font-size:13px;line-height:1.65;color:#718096;margin:0}
.ms-analytics-kicker{display:inline-flex;align-items:center;gap:7px;color:#087f83;font-size:11px;letter-spacing:.09em;text-transform:uppercase;font-weight:800;margin-bottom:10px}
.ms-analytics-kicker:before{content:'';width:7px;height:7px;border-radius:50%;background:#0d9488}
.ms-analytics-panel{border:1px solid #e4edf3;border-radius:14px;background:#f9fbfc;padding:18px;margin-bottom:18px}
.ms-analytics-panel-title{font-size:13px;font-weight:800;color:#18324f;margin:0 0 14px}
.ms-analytics-filters{display:grid;grid-template-columns:repeat(2,minmax(0,1fr)) auto;align-items:end;gap:12px;margin:0}
.ms-analytics-filters label{font-size:12px;font-weight:700;color:#52657a;display:flex;flex-direction:column;gap:8px;min-width:0}
.ms-analytics-filters select{width:100%;height:43px;border:1px solid #cbd5e1;border-radius:9px;padding:0 12px;background:#fff;color:#18324f;font:inherit;font-size:12px;outline:none;transition:border-color .2s,box-shadow .2s}
.ms-analytics-filters select:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.12)}
.ms-analytics-filters button{height:43px;min-width:94px;border-radius:9px}
.ms-analytics-context{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 18px;align-items:center}
.ms-analytics-chip{display:inline-flex;align-items:center;border:1px solid #d7e8e7;background:#effaf8;color:#087c73;border-radius:99px;padding:7px 11px;font-size:11px;font-weight:700;max-width:100%;overflow-wrap:anywhere}
.ms-analytics-context-caption{font-size:11px;color:#7a8b9c;margin-right:3px}
.ms-analytics .ms-hint{margin:0 0 22px;background:#f1f6f8;border-left:3px solid #0d9488;padding:12px 14px;border-radius:8px;color:#5b6d7c;font-size:12px;line-height:1.7}
.ms-analysis-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:22px}
.ms-analysis-stats article{position:relative;border:1px solid #e4edf3;border-radius:13px;padding:20px;background:#fff;display:flex;flex-direction:column;gap:8px;min-width:0;box-shadow:0 5px 18px rgba(24,50,79,.035)}
.ms-analysis-stats article:first-child{background:linear-gradient(135deg,#effaf8,#fff 70%);border-color:#ccece5}
.ms-analysis-stats span{font-size:12px;color:#718096;font-weight:700}
.ms-analysis-stats strong{font-size:clamp(19px,2.3vw,29px);line-height:1.25;color:#18324f;overflow-wrap:anywhere}
.ms-analysis-stats small{color:#087f83;font-size:12px;line-height:1.5}
.ms-analysis-ranking{border:1px solid #e4edf3;border-radius:13px;padding:22px;background:#fff}
.ms-analysis-ranking-header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:21px;flex-wrap:wrap}
.ms-analysis-ranking h3{font-size:16px;color:#18324f;margin:0 0 6px}
.ms-analysis-ranking-header p{font-size:12px;color:#718096;margin:0}
.ms-analysis-ranking-tag{border-radius:99px;background:#edf7f5;color:#087c73;font-size:11px;font-weight:700;padding:7px 11px}
.ms-analysis-item{margin:0 0 18px;padding-bottom:18px;border-bottom:1px solid #eef2f6}
.ms-analysis-item:last-child{margin-bottom:0;padding-bottom:0;border-bottom:0}
.ms-analysis-item-top{display:flex;justify-content:space-between;align-items:center;gap:12px;font-size:13px;color:#40566e;margin-bottom:10px}
.ms-analysis-name{display:flex;align-items:center;gap:10px;min-width:0;font-weight:700}
.ms-analysis-rank{width:28px;height:28px;flex:0 0 28px;display:inline-flex;align-items:center;justify-content:center;background:#edf4f7;border-radius:8px;color:#456177;font-size:12px}
.ms-analysis-item:first-of-type .ms-analysis-rank{background:#d9f3eb;color:#087f83}
.ms-analysis-item-top b{color:#18324f;font-size:13px;text-align:right;overflow-wrap:anywhere}
.ms-analysis-track{height:9px;background:#edf2f7;border-radius:99px;overflow:hidden;margin-bottom:7px}
.ms-analysis-track div{height:100%;background:linear-gradient(90deg,#0d9488,#2bb7a8);border-radius:99px}
.ms-analysis-item small{font-size:11px;color:#718096;margin-left:38px}
.ms-analysis-note{padding:16px;background:#f1f5f9;border-radius:9px;color:#64748b;font-size:13px;line-height:1.7}
.ms-analysis-footnote{margin:18px 0 0;font-size:12px;line-height:1.8;color:#718096;border-top:1px solid #edf2f6;padding-top:15px}
@media(max-width:950px){.ms-analytics-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.ms-analytics-filters button{width:100%}}
@media(max-width:700px){.ms-analysis-stats{grid-template-columns:1fr}.ms-analytics{padding:18px}.ms-analytics-head h2{font-size:18px}}
@media(max-width:520px){.ms-analytics-filters{grid-template-columns:1fr}.ms-analysis-item-top{align-items:flex-start;flex-direction:column}.ms-analysis-item small{margin-left:0}.ms-analytics-panel{padding:14px}}


/* Rincian potensi per Pemda: semua satuan tanpa peringkat lintas unit */
.ms-pemda-table-wrap{overflow-x:auto}
.ms-pemda-table{width:100%;border-collapse:collapse;font-size:13px;min-width:460px}
.ms-pemda-table th{text-align:left;color:#64748b;font-size:11px;letter-spacing:.03em;text-transform:uppercase;padding:12px 14px;border-bottom:1px solid #e2e8f0;background:#f8fafc}
.ms-pemda-table td{padding:14px;border-bottom:1px solid #edf2f7;vertical-align:top;color:#23344d}
.ms-pemda-table tbody tr:last-child td{border-bottom:0}
.ms-pemda-table-right{text-align:right!important;white-space:nowrap}
.ms-pemda-unit{display:flex;gap:8px;align-items:baseline;flex-wrap:wrap;margin-bottom:5px}
.ms-pemda-unit:last-child{margin-bottom:0}
.ms-pemda-unit span{color:#64748b;font-size:12px}
.ms-pemda-empty{text-align:center;color:#64748b!important;padding:24px!important}
.ms-select-satuan{height:39px;min-width:135px;max-width:210px;padding:7px 9px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:#1e293b;font:inherit;font-size:12px;box-sizing:border-box}
.ms-tambah-k{flex-wrap:wrap}
.ms-tambah-k .ms-select-satuan{flex:0 1 190px}
.baris-form .ms-select-satuan{flex:0 1 170px;height:35px}
@media(max-width:650px){.ms-tambah-k .ms-select-satuan,.baris-form .ms-select-satuan{max-width:none;flex:1 1 100%}}
</style>


{{-- =====================================================
     LAYOUT
===================================================== --}}

<div class="ms-tabs" role="navigation" aria-label="Menu master sektor">
    <a class="{{ $tab === 'master' ? 'is-active' : '' }}" href="{{ route('admin.sektor.index', ['sektor' => $idTerpilih]) }}">Data Master</a>
    <a class="{{ $tab === 'analisis' ? 'is-active' : '' }}" href="{{ route('admin.sektor.index', ['tab' => 'analisis']) }}">Analisis Potensi</a>
</div>
@if ($tab === 'master')
<div class="ms-grid">

    {{-- =====================================================
         SIDEBAR SEKTOR
    ===================================================== --}}

    <aside class="box ms-side" aria-label="Daftar sektor">

        <h2>Sektor</h2>

        <ul class="ms-list">

            {{-- SEMUA SEKTOR --}}

            <li>
                <a
                    href="{{ $tautan() }}"
                    @class(['aktif' => ! $terpilih])
                    @if (! $terpilih)
                        aria-current="page"
                    @endif
                >
                    <span>Semua sektor</span>

                    <b>{{ $totalKomoditas }}</b>
                </a>
            </li>

            {{-- DAFTAR SEKTOR --}}

            @foreach ($semuaSektor as $s)

                <li>
                    <a
                        href="{{ $tautan($s->id_sektor) }}"
                        @class(['aktif' => $idTerpilih == $s->id_sektor])
                        @if ($idTerpilih == $s->id_sektor)
                            aria-current="page"
                        @endif
                    >
                        <span>
                            {{ $s->nama_sektor }}
                        </span>

                        <b>{{ $s->komoditas_count }}</b>
                    </a>
                </li>

            @endforeach

        </ul>

        {{-- TAMBAH SEKTOR --}}

        <details
            class="ms-tambah"
            @if (old('nama_sektor')) open @endif
        >

            <summary>Tambah sektor</summary>

            <form
                method="POST"
                action="{{ route('admin.sektor.store') }}"
            >
                @csrf

                <div class="field">

                    <label for="nama_sektor_baru">
                        Nama Sektor
                    </label>

                    <input
                        type="text"
                        id="nama_sektor_baru"
                        name="nama_sektor"
                        maxlength="100"
                        required
                        value="{{ old('nama_sektor') }}"
                        placeholder="Contoh: Perikanan &amp; Kelautan"
                    >

                </div>

                <div class="field">

                    <label for="urutan_baru">
                        Urutan
                    </label>

                    <input
                        type="number"
                        id="urutan_baru"
                        name="urutan"
                        min="0"
                        max="65535"
                        value="{{ old('urutan', $urutanBerikut) }}"
                    >

                </div>

                <button type="submit" class="btn btn-new">
                    Tambah Sektor
                </button>

            </form>

        </details>

    </aside>


    {{-- =====================================================
         KONTEN KOMODITAS
    ===================================================== --}}

    <section class="box ms-main">

        {{-- HEADER --}}

        <div class="ms-head">

            <div>

                <h2>
                    {{ $terpilih?->nama_sektor ?? 'Semua sektor' }}
                </h2>

                <p class="muted">

                    {{ number_format($komoditas->total(), 0, ',', '.') }}
                    komoditas

                    @if ($q !== '')
                        cocok dengan "{{ $q }}"
                    @endif

                    @if (
                        $terpilih &&
                        (int) ($pakaiSektor[$terpilih->id_sektor] ?? 0) > 0
                    )
                        &middot;
                        sektor dipakai
                        {{ (int) $pakaiSektor[$terpilih->id_sektor] }}
                        pengajuan
                    @endif

                </p>

            </div>

            {{-- PENCARIAN --}}

            <form
                class="ms-cari"
                method="GET"
                action="{{ route('admin.sektor.index') }}"
                role="search"
            >

                @if ($terpilih)
                    <input
                        type="hidden"
                        name="sektor"
                        value="{{ $terpilih->id_sektor }}"
                    >
                @endif

                <input
                    type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Cari komoditas..."
                    aria-label="Cari komoditas"
                >

                <button type="submit" class="btn btn-soft">
                    Cari
                </button>

                @if ($q !== '')
                    <a
                        class="reset"
                        href="{{ $tautan($idTerpilih) }}"
                    >
                        Reset
                    </a>
                @endif

            </form>

        </div>


        {{-- =====================================================
             TOOLS SEKTOR TERPILIH
        ===================================================== --}}

        @if ($terpilih)

            <div class="ms-tools">

                {{-- TAMBAH KOMODITAS --}}

                <form
                    class="ms-tambah-k"
                    method="POST"
                    action="{{ route('admin.sektor.komoditas.store', $terpilih->id_sektor) }}"
                >
                    @csrf

                    <input
                        type="text"
                        name="nama_komoditas"
                        maxlength="100"
                        required
                        value="{{ old('nama_komoditas') }}"
                        placeholder="Komoditas baru untuk {{ $terpilih->nama_sektor }}"
                        aria-label="Komoditas baru"
                    >

                    <select name="satuan_default" aria-label="Satuan default komoditas baru" class="ms-select-satuan">
                        <option value="">Satuan default (opsional)</option>
                        @foreach ($pilihanSatuanDefault as $unit)
                            <option value="{{ $unit }}" @selected(old('satuan_default') === $unit)>{{ $unit }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-new">
                        <svg
                            viewBox="0 0 24 24"
                            width="17"
                            height="17"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 8v8M8 12h8"/>
                        </svg>

                        Tambah
                    </button>

                </form>


                {{-- UBAH / HAPUS SEKTOR --}}

                <details class="ms-ubah">

                    <summary>
                        Ubah / Hapus Sektor
                    </summary>

                    <div class="ms-ubah-isi">

                        {{-- EDIT SEKTOR --}}

                        <form
                            method="POST"
                            action="{{ route('admin.sektor.update', $terpilih->id_sektor) }}"
                        >
                            @csrf
                            @method('PUT')

                            <input
                                type="text"
                                name="nama_sektor"
                                maxlength="100"
                                required
                                value="{{ $terpilih->nama_sektor }}"
                                aria-label="Nama sektor"
                            >

                            <input
                                type="number"
                                name="urutan"
                                min="0"
                                max="65535"
                                value="{{ $terpilih->urutan }}"
                                aria-label="Urutan"
                                class="kecil"
                            >

                            <button
                                type="submit"
                                class="btn btn-soft"
                            >
                                Simpan
                            </button>

                        </form>

                        {{-- HAPUS SEKTOR --}}

                        <form
                            method="POST"
                            action="{{ route('admin.sektor.destroy', $terpilih->id_sektor) }}"
                            data-confirm="Hapus sektor {{ $terpilih->nama_sektor }} beserta {{ $terpilih->komoditas_count }} komoditasnya?"
                            onsubmit="return confirm('Yakin ingin menghapus sektor beserta komoditasnya?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-outline btn-hapus"
                            >
                                Hapus Sektor
                            </button>

                        </form>

                    </div>

                </details>

            </div>

        @else

            <p class="ms-hint">
                Pilih sektor di sebelah kiri untuk menambah
                komoditas atau mengubah data sektor.
            </p>

        @endif


        {{-- =====================================================
             TABEL KOMODITAS
        ===================================================== --}}

        <div class="table-box">

            <table class="table table-data ms-table">

                <thead>
                    <tr>
                        <th>Komoditas &amp; Satuan Default</th>

                        @unless ($terpilih)
                            <th>Sektor</th>
                        @endunless

                        <th class="num">Dipakai</th>

                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($komoditas as $k)

                        <tr>

                            {{-- EDIT NAMA KOMODITAS --}}

                            <td data-label="Komoditas">

                                <form
                                    class="baris-form"
                                    method="POST"
                                    action="{{ route('admin.komoditas.update', $k->id_komoditas) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="text"
                                        name="nama_komoditas"
                                        maxlength="100"
                                        required
                                        value="{{ $k->nama_komoditas }}"
                                        aria-label="Nama komoditas"
                                    >

                                    <select name="satuan_default" class="ms-select-satuan" aria-label="Satuan default {{ $k->nama_komoditas }}">
                                        <option value="">Tanpa default</option>
                                        @foreach ($pilihanSatuanDefault as $unit)
                                            <option value="{{ $unit }}" @selected($k->satuan_default === $unit)>{{ $unit }}</option>
                                        @endforeach
                                        @if ($k->satuan_default && ! $pilihanSatuanDefault->contains($k->satuan_default))
                                            <option value="{{ $k->satuan_default }}" selected disabled>{{ $k->satuan_default }} (nonaktif)</option>
                                        @endif
                                    </select>

                                    <button
                                        type="submit"
                                        class="btn btn-soft"
                                    >
                                        Simpan
                                    </button>

                                </form>

                            </td>

                            {{-- SEKTOR --}}

                            @unless ($terpilih)

                                <td data-label="Sektor" class="soft">

                                    <a
                                        class="link-teal"
                                        href="{{ $tautan($k->id_sektor) }}"
                                    >
                                        {{ $k->sektor->nama_sektor }}
                                    </a>

                                </td>

                            @endunless

                            {{-- JUMLAH PENGGUNAAN --}}

                            <td data-label="Dipakai" class="num">
                                {{ (int) ($pakaiKomoditas[$k->id_komoditas] ?? 0) }}
                            </td>

                            {{-- AKSI HAPUS --}}

                            <td class="td-aksi" data-label="Aksi">

                                <div class="acts">

                                    <form
                                        method="POST"
                                        action="{{ route('admin.komoditas.destroy', $k->id_komoditas) }}"
                                        data-confirm="Hapus komoditas {{ $k->nama_komoditas }}?"
                                        onsubmit="return confirm('Yakin ingin menghapus komoditas ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="act act-del"
                                            title="Hapus komoditas"
                                            aria-label="Hapus {{ $k->nama_komoditas }}"
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                width="18"
                                                height="18"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.9"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true"
                                            >
                                                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3"/>
                                            </svg>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="{{ $terpilih ? 3 : 4 }}"
                                class="empty"
                            >

                                {{ $q !== ''
                                    ? 'Tidak ada komoditas yang cocok.'
                                    : 'Belum ada komoditas.'
                                }}

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINATION
        ===================================================== --}}

        @if ($komoditas->lastPage() > 1)

            <nav
                class="pager-num ms-pager"
                aria-label="Halaman komoditas"
            >

                {{-- PREVIOUS --}}

                @if ($komoditas->onFirstPage())

                    <span
                        class="pg pg-nav is-disabled"
                        aria-hidden="true"
                    >
                        &lsaquo;
                    </span>

                @else

                    <a
                        class="pg pg-nav"
                        href="{{ $komoditas->previousPageUrl() }}"
                        aria-label="Halaman sebelumnya"
                    >
                        &lsaquo;
                    </a>

                @endif


                {{-- NOMOR HALAMAN --}}

                @for (
                    $h = max(1, $komoditas->currentPage() - 2);
                    $h <= min(
                        $komoditas->lastPage(),
                        $komoditas->currentPage() + 2
                    );
                    $h++
                )

                    @if ($h === $komoditas->currentPage())

                        <span
                            class="pg active"
                            aria-current="page"
                        >
                            {{ $h }}
                        </span>

                    @else

                        <a
                            class="pg"
                            href="{{ $komoditas->url($h) }}"
                            aria-label="Halaman {{ $h }}"
                        >
                            {{ $h }}
                        </a>

                    @endif

                @endfor


                {{-- NEXT --}}

                @if ($komoditas->hasMorePages())

                    <a
                        class="pg pg-nav"
                        href="{{ $komoditas->nextPageUrl() }}"
                        aria-label="Halaman berikutnya"
                    >
                        &rsaquo;
                    </a>

                @else

                    <span
                        class="pg pg-nav is-disabled"
                        aria-hidden="true"
                    >
                        &rsaquo;
                    </span>

                @endif

            </nav>

        @endif

    </section>

</div>
@else
<section class="box ms-analytics">
    <div class="ms-analytics-head">
        <div>
            <span class="ms-analytics-kicker">Ringkasan data terverifikasi</span>
            <h2>Analisis Potensi Komoditas per Pemda</h2>
            <p>Bandingkan jumlah komoditas yang tercatat pada tujuan pengajuan berstatus Verifikasi Final.</p>
        </div>
    </div>

    <div class="ms-search-komoditas">
        <p class="ms-search-komoditas-title">Cari komoditas langsung</p>
        <form method="GET" action="{{ route('admin.sektor.index') }}" role="search">
            <input type="hidden" name="tab" value="analisis">
            <input type="search" name="cari_komoditas" list="ms-saran-komoditas" placeholder="Ketik nama komoditas, misalnya mangga ciemas..." aria-label="Cari komoditas dari semua sektor" required maxlength="100" autocomplete="off">
            <datalist id="ms-saran-komoditas">
                @foreach ($semuaKomoditasCari as $item)
                    <option value="{{ $item->nama_komoditas }}">{{ $item->sektor?->nama_sektor }}</option>
                @endforeach
            </datalist>
            <button class="btn btn-soft" type="submit">Cari Komoditas</button>
        </form>
        <small>Pencarian berlaku untuk semua sektor. Ketik sebagian nama lalu pilih saran, atau tekan Cari Komoditas.</small>
        @if (session('cari_komoditas_info'))
            <p class="ms-hint" style="margin:10px 0 0">{{ session('cari_komoditas_info') }}</p>
        @endif
        @if (request()->filled('cari_komoditas') && ! $hasilCariKomoditas)
            <p class="ms-hint" style="margin:10px 0 0">Komoditas "{{ request('cari_komoditas') }}" tidak ditemukan di master.</p>
        @endif
    </div>

    <div class="ms-analytics-panel">
        <p class="ms-analytics-panel-title">Filter Analisis</p>
        <form method="GET" action="{{ route('admin.sektor.index') }}" class="ms-analytics-filters">
            <input type="hidden" name="tab" value="analisis">
            <label for="ms-analisis-sektor">Sektor
                <select id="ms-analisis-sektor" name="analisis_sektor" onchange="this.form.elements['analisis_komoditas'].value='';this.form.submit()">
                    @foreach ($semuaSektor as $s)
                        <option value="{{ $s->id_sektor }}" @selected($analisis['sektor']?->id_sektor == $s->id_sektor)>{{ $s->nama_sektor }}</option>
                    @endforeach
                </select>
            </label>
            <label for="ms-analisis-komoditas">Komoditas
                <select id="ms-analisis-komoditas" name="analisis_komoditas" onchange="this.form.submit()">
                    @foreach ($analisis['daftar_komoditas'] as $k)
                        <option value="{{ $k->id_komoditas }}" @selected($analisis['komoditas']?->id_komoditas == $k->id_komoditas)>{{ $k->nama_komoditas }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-soft" type="submit">Terapkan</button>
        </form>
    </div>

    <div class="ms-analytics-context" aria-label="Filter aktif">
        <span class="ms-analytics-context-caption">Menampilkan:</span>
        @if ($analisis['sektor']) <span class="ms-analytics-chip">{{ $analisis['sektor']->nama_sektor }}</span> @endif
        @if ($analisis['komoditas']) <span class="ms-analytics-chip">{{ $analisis['komoditas']->nama_komoditas }}</span> @endif
        <span class="ms-analytics-chip">Semua satuan</span>
    </div>

    <p class="ms-hint">Semua rincian komoditas dari pengajuan <strong>Verifikasi Final</strong> ditampilkan, termasuk data lama tanpa satuan. Total dihitung <strong>terpisah untuk setiap satuan</strong>; tidak ada penjumlahan lintas satuan.</p>

    @if ($analisis['ambigu'])
        <p class="ms-analysis-note">Nama komoditas ini terdapat pada beberapa sektor. Data tidak dijumlahkan agar tidak salah mengatribusikan sektor. Diperlukan pengaitan ID komoditas pada rincian tujuan.</p>
    @else
        <div class="ms-analysis-stats">
            <article>
                <span>Pemda Tercatat</span>
                <strong>{{ number_format($analisis['jumlah_pemda'], 0, ',', '.') }}</strong>
                <small>Kabupaten/kota asal pengajuan</small>
            </article>
            <article>
                <span>Total Entri</span>
                <strong>{{ number_format($analisis['jumlah_entri'], 0, ',', '.') }}</strong>
                <small>Entri dengan jumlah terisi</small>
            </article>
            <article>
                <span>Jenis Satuan Tercatat</span>
                <strong>{{ number_format($analisis['jumlah_satuan'], 0, ',', '.') }}</strong>
                <small>Termasuk satuan belum diketahui</small>
            </article>
        </div>

        <div class="ms-analysis-ranking" style="margin-bottom:18px">
            <div class="ms-analysis-ranking-header">
                <div>
                    <h3>Rincian Potensi Komoditas per Pemda</h3>
                    <p>Seluruh Pemda ditampilkan. Jumlah tetap dipisahkan berdasarkan satuan, tanpa peringkat lintas satuan.</p>
                </div>
                <span class="ms-analysis-ranking-tag">{{ $analisis['jumlah_pemda'] }} Pemda</span>
            </div>

            <div class="ms-pemda-table-wrap">
                <table class="ms-pemda-table">
                    <thead>
                        <tr>
                            <th>Pemda</th>
                            <th>Jumlah Tercatat</th>
                            <th class="ms-pemda-table-right">Entri</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($analisis['pemda_semua'] as $pemda)
                            <tr>
                                <td><strong>{{ $pemda['nama'] }}</strong></td>
                                <td>
                                    @foreach ($pemda['rincian'] as $rincian)
                                        <div class="ms-pemda-unit">
                                            <strong>{{ number_format($rincian['total'], 2, ',', '.') }}</strong>
                                            <span>{{ $rincian['satuan'] }}</span>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="ms-pemda-table-right">{{ number_format($pemda['entri'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="ms-pemda-empty">Belum ada Pemda dengan data jumlah komoditas final.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="ms-analysis-ranking">
            <div class="ms-analysis-ranking-header">
                <div>
                    <h3>Ringkasan berdasarkan Satuan</h3>
                    <p>Setiap total hanya menjumlahkan entri yang menggunakan satuan yang sama.</p>
                </div>
            </div>
            @forelse ($analisis['kelompok_satuan'] as $kelompok)
                <div class="ms-analysis-item">
                    <div class="ms-analysis-item-top">
                        <span class="ms-analysis-name">{{ $kelompok['satuan'] }}</span>
                        <b>{{ number_format($kelompok['total'], 2, ',', '.') }} {{ $kelompok['satuan'] }}</b>
                    </div>
                    <small>{{ $kelompok['jumlah_pemda'] }} Pemda · {{ $kelompok['jumlah_entri'] }} entri</small>
                </div>
            @empty
                <p class="ms-analysis-note">Belum ada jumlah komoditas yang tercatat pada pengajuan Verifikasi Final.</p>
            @endforelse
        </div>
    @endif
    <p class="ms-analysis-footnote"><strong>Catatan data:</strong> Angka berasal dari rincian komoditas tujuan pada pengajuan Verifikasi Final, bukan otomatis total produksi daerah. Data format baru dan format lama yang belum memiliki pengganti dibaca. Satuan berbeda tidak digabungkan. Pemda dikelompokkan berdasarkan kabupaten/kota asal pengajuan.</p>
</section>
@endif

@endsection
