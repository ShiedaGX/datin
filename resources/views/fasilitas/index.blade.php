@extends('layouts.app')

@section('title', 'Data Fasilitas Transportasi')
@section('heading', 'Data Fasilitas Transportasi')
@section('subheading', 'Data referensi bandar udara, terminal, pelabuhan, dan stasiun')
@section('search_action', route('fasilitas.index'))
@section('search_placeholder', 'Cari nama fasilitas...')

@section('content')

<style>
    /* =====================================================
       PENYESUAIAN KHUSUS HALAMAN FASILITAS
       Komponen utama mengikuti style halaman Data User
    ===================================================== */

    .fasilitas-info {
        padding: 14px 18px;
        margin-bottom: 18px;
        background: #eff8f7;
        border: 1px solid #cceae5;
        border-radius: 10px;
        color: #35645f;
        font-size: 13px;
        line-height: 1.7;
    }

    .fasilitas-info p {
        margin: 0;
    }

    .fasilitas-filter {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .fasilitas-filter .filter {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .fasilitas-filter .filter span {
        white-space: nowrap;
    }

    .fasilitas-filter .filter select {
        min-width: 150px;
        max-width: 100%;
    }

    .fasilitas-filter .filter-source select {
        min-width: 210px;
    }

    .fasilitas-filter .filter-search {
        flex: 1 1 220px;
    }

    .fasilitas-filter .filter-search input {
        width: 100%;
        min-width: 0;
        padding: 10px 12px;
        border: 1px solid #d4dee8;
        border-radius: 8px;
        background: #fff;
        color: #253b53;
        font: inherit;
        font-size: 13px;
        box-sizing: border-box;
    }

    .fasilitas-filter .filter-search input:focus {
        outline: none;
        border-color: #12aaa0;
        box-shadow: 0 0 0 3px rgba(18, 170, 160, .10);
    }

    .fasilitas-filter .filter-search input::placeholder {
        color: #94a3b8;
    }

    .fasilitas-filter .fasilitas-cari {
        white-space: nowrap;
        cursor: pointer;
    }

    .fasilitas-filter .count {
        white-space: nowrap;
    }

    .fasilitas-filter .btn-new {
        white-space: nowrap;
        margin-left: auto;
    }

    .fasilitas-notice {
        margin-bottom: 18px;
        padding: 12px 16px;
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        border-radius: 9px;
        color: #166534;
        font-size: 13px;
    }

    /* =====================================================
       TABEL
    ===================================================== */

    .table-fasilitas {
        width: 100%;
    }

    .table-fasilitas th,
    .table-fasilitas td {
        vertical-align: middle;
    }

    .table-fasilitas .col-nama {
        min-width: 210px;
    }

    .table-fasilitas .col-wilayah {
        min-width: 160px;
    }

    .table-fasilitas .col-koordinat {
        min-width: 155px;
    }

    .table-fasilitas .col-aksi {
        width: 105px;
        white-space: nowrap;
    }

    .fasilitas-nama {
        display: block;
        color: #182f49;
        font-weight: 700;
        line-height: 1.5;
    }

    .fasilitas-sumber {
        display: block;
        margin-top: 4px;
        color: #8190a2;
        font-size: 11px;
        line-height: 1.5;
    }

    .fasilitas-wilayah {
        line-height: 1.6;
    }

    .fasilitas-koordinat {
        color: #53657b;
        font-size: 12px;
        font-variant-numeric: tabular-nums;
    }

    .fasilitas-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .fasilitas-status::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .fasilitas-status.is-active {
        color: #15945c;
    }

    .fasilitas-status.is-inactive {
        color: #c07828;
    }

    .table-fasilitas .acts {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-fasilitas .acts form {
        display: inline-flex;
        margin: 0;
    }

    .table-fasilitas .acts .act {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    /* =====================================================
       RESPONSIVE
    ===================================================== */

    @media (max-width: 1200px) {
        .fasilitas-filter .count {
            order: 5;
        }
    }

    @media (max-width: 900px) {
        .fasilitas-filter .filter-search {
            flex-basis: 100%;
        }

        .fasilitas-filter .btn-new {
            margin-left: 0;
        }
    }

    @media (max-width: 650px) {
        .fasilitas-filter {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .fasilitas-filter .filter-search {
            grid-column: 1 / -1;
        }

        .fasilitas-filter .filter {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 5px;
        }

        .fasilitas-filter .filter select,
        .fasilitas-filter .filter-source select {
            width: 100%;
            min-width: 0;
        }

        .fasilitas-filter .fasilitas-cari {
            grid-column: 1 / -1;
        }

        .fasilitas-filter .count {
            grid-column: 1 / -1;
            order: initial;
            white-space: normal;
        }

        .fasilitas-filter .reset {
            grid-column: 1 / -1;
            justify-self: start;
        }

        .fasilitas-filter .btn-new {
            grid-column: 1 / -1;
            justify-content: center;
            width: 100%;
            box-sizing: border-box;
        }

        .fasilitas-info {
            padding: 12px 14px;
        }
    }
</style>

{{-- =====================================================
     INFORMASI HALAMAN
===================================================== --}}

<div class="fasilitas-info">
    <p>
        Fasilitas transportasi merupakan data referensi yang terpisah dari
        pengajuan Pemerintah Daerah. Informasi lokasi, kabupaten/kota,
        dan jenis moda digunakan sebagai pendukung analisis
        kebutuhan konektivitas.
    </p>
</div>

{{-- =====================================================
     NOTIFIKASI
===================================================== --}}

@if (session('success'))
    <div class="fasilitas-notice" role="status">
        {{ session('success') }}
    </div>
@endif

{{-- =====================================================
     FILTER DAN AKSI
===================================================== --}}

@php
    $adaFilter = request()->filled('cari')
        || request()->filled('moda')
        || request()->filled('sumber_data');

    $jumlahFasilitas = $fasilitas->total();
@endphp

<form
    class="box filter-bar fasilitas-filter"
    method="GET"
    action="{{ route('fasilitas.index') }}"
>

    {{-- PENCARIAN --}}

    <div class="filter-search">
        <input
            type="search"
            name="cari"
            value="{{ request('cari') }}"
            placeholder="Cari nama fasilitas..."
            aria-label="Cari nama fasilitas"
        >
    </div>

    {{-- FILTER MODA --}}

    <label class="filter">
        <span>Moda:</span>

        <select
            name="moda"
            aria-label="Filter moda transportasi"
            onchange="this.form.requestSubmit()"
        >
            <option value="">Semua Moda</option>

            <option value="1" @selected(request('moda') == '1')>
                Darat
            </option>

            <option value="2" @selected(request('moda') == '2')>
                Laut
            </option>

            <option value="3" @selected(request('moda') == '3')>
                Udara
            </option>

            <option value="4" @selected(request('moda') == '4')>
                Kereta Api
            </option>
        </select>
    </label>

    {{-- FILTER SUMBER DATA --}}

    <label class="filter filter-source">
        <span>Sumber:</span>

        <select
            name="sumber_data"
            aria-label="Filter sumber data"
            onchange="this.form.requestSubmit()"
        >
            <option value="">Semua Sumber Data</option>

            @foreach ($sumberData as $sumber)
                <option
                    value="{{ $sumber }}"
                    @selected(request('sumber_data') === $sumber)
                >
                    {{ $sumber }}
                </option>
            @endforeach
        </select>
    </label>

    {{-- TOMBOL CARI --}}

    <button
        type="submit"
        class="btn btn-outline fasilitas-cari"
    >
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
            <circle cx="10.8" cy="10.8" r="6.8"/>
            <path d="m16 16 5 5"/>
        </svg>

        Cari
    </button>

    {{-- INFORMASI JUMLAH DATA --}}

    <span class="count">
        Menampilkan
        {{ number_format($fasilitas->count(), 0, ',', '.') }}
        dari
        {{ number_format($jumlahFasilitas, 0, ',', '.') }}
        entri
    </span>

    {{-- RESET FILTER --}}

    @if ($adaFilter)
        <a
            class="reset"
            href="{{ route('fasilitas.index') }}"
        >
            Reset
        </a>
    @endif

    {{-- TAMBAH FASILITAS --}}

    <a
        class="btn btn-new"
        href="{{ route('fasilitas.create') }}"
    >
        <svg
            viewBox="0 0 24 24"
            width="20"
            height="20"
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

        Tambah Fasilitas
    </a>

</form>

{{-- =====================================================
     TABEL FASILITAS
===================================================== --}}

<section class="box box-table">

    <div class="table-box">

        <table class="table table-data table-fasilitas">

            <thead>
                <tr>
                    <th>No</th>
                    <th class="col-nama">Fasilitas</th>
                    <th>Moda</th>
                    <th class="col-wilayah">Kabupaten/Kota</th>
                    <th class="col-koordinat">Koordinat</th>
                    <th>Status</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($fasilitas as $item)

                    <tr>

                        {{-- NOMOR --}}

                        <td data-label="No">
                            {{ $fasilitas->firstItem() + $loop->index }}
                        </td>

                        {{-- NAMA FASILITAS DAN SUMBER DATA --}}

                        <td data-label="Fasilitas">

                            <strong class="fasilitas-nama">
                                {{ $item->nama }}
                            </strong>

                            <span class="fasilitas-sumber">
                                {{ $item->sumber_data ?: 'Sumber tidak diketahui' }}
                            </span>

                        </td>

                        {{-- MODA --}}

                        <td data-label="Moda" class="soft">
                            {{ $item->moda?->nama_moda ?: '-' }}
                        </td>

                        {{-- KABUPATEN/KOTA --}}

                        <td
                            data-label="Kabupaten/Kota"
                            class="fasilitas-wilayah"
                        >
                            @if ($item->kabKota)
                                {{ $item->kabKota->nama_lengkap }}
                            @else
                                <span class="muted">
                                    Belum ditetapkan
                                </span>
                            @endif
                        </td>

                        {{-- KOORDINAT --}}

                        <td data-label="Koordinat">

                            @if (
                                $item->latitude !== null
                                && $item->longitude !== null
                            )

                                <span class="fasilitas-koordinat">
                                    {{ $item->latitude }},
                                    {{ $item->longitude }}
                                </span>

                            @else

                                <span class="muted">-</span>

                            @endif

                        </td>

                        {{-- STATUS --}}

                        <td data-label="Status">

                            @if ($item->aktif)

                                <span class="fasilitas-status is-active">
                                    Aktif
                                </span>

                            @else

                                <span class="fasilitas-status is-inactive">
                                    Nonaktif
                                </span>

                            @endif

                        </td>

                        {{-- AKSI --}}

                        <td class="td-aksi" data-label="Aksi">

                            <div class="acts">

                                {{-- EDIT --}}

                                <a
                                    class="act act-edit"
                                    href="{{ route('fasilitas.edit', $item) }}"
                                    title="Edit fasilitas"
                                    aria-label="Edit {{ $item->nama }}"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        width="18"
                                        height="18"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/>
                                        <path d="M13.5 6.5l4 4"/>
                                    </svg>
                                </a>

                                {{-- NONAKTIFKAN --}}

                                @if ($item->aktif)

                                    <form
                                        method="POST"
                                        action="{{ route('fasilitas.destroy', $item) }}"
                                        data-confirm="Nonaktifkan fasilitas {{ $item->nama }}?"
                                        onsubmit="return confirm('Nonaktifkan fasilitas ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="act act-del"
                                            title="Nonaktifkan fasilitas"
                                            aria-label="Nonaktifkan {{ $item->nama }}"
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
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="M5.7 5.7 18.3 18.3"/>
                                            </svg>
                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="empty">

                            Tidak ada fasilitas yang sesuai dengan
                            pencarian atau filter.

                            @if ($adaFilter)
                                <a
                                    class="link-teal"
                                    href="{{ route('fasilitas.index') }}"
                                >
                                    Reset filter
                                </a>
                            @endif

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- =====================================================
         FOOTER TABEL DAN PAGINATION
    ===================================================== --}}

    <div class="table-foot">

        <p class="muted">

            @if ($fasilitas->total() > 0)

                Menampilkan
                {{ $fasilitas->firstItem() }}–{{ $fasilitas->lastItem() }}
                dari
                {{ number_format($fasilitas->total(), 0, ',', '.') }}
                data fasilitas

            @else

                Tidak ada data fasilitas

            @endif

        </p>

        @if ($fasilitas->lastPage() > 1)

            @php
                $halamanAwal = max(
                    1,
                    $fasilitas->currentPage() - 2
                );

                $halamanAkhir = min(
                    $fasilitas->lastPage(),
                    $fasilitas->currentPage() + 2
                );
            @endphp

            <nav
                class="pager-num"
                aria-label="Halaman fasilitas"
            >

                {{-- SEBELUMNYA --}}

                @if ($fasilitas->onFirstPage())

                    <span
                        class="pg pg-nav is-disabled"
                        aria-hidden="true"
                    >
                        &lsaquo;
                    </span>

                @else

                    <a
                        class="pg pg-nav"
                        href="{{ $fasilitas->previousPageUrl() }}"
                        aria-label="Halaman sebelumnya"
                    >
                        &lsaquo;
                    </a>

                @endif

                {{-- HALAMAN PERTAMA --}}

                @if ($halamanAwal > 1)

                    <a
                        class="pg"
                        href="{{ $fasilitas->url(1) }}"
                        aria-label="Halaman 1"
                    >
                        1
                    </a>

                    @if ($halamanAwal > 2)
                        <span class="gap">&hellip;</span>
                    @endif

                @endif

                {{-- NOMOR HALAMAN --}}

                @for ($page = $halamanAwal; $page <= $halamanAkhir; $page++)

                    @if ($page === $fasilitas->currentPage())

                        <span
                            class="pg active"
                            aria-current="page"
                        >
                            {{ $page }}
                        </span>

                    @else

                        <a
                            class="pg"
                            href="{{ $fasilitas->url($page) }}"
                            aria-label="Halaman {{ $page }}"
                        >
                            {{ $page }}
                        </a>

                    @endif

                @endfor

                {{-- HALAMAN TERAKHIR --}}

                @if ($halamanAkhir < $fasilitas->lastPage())

                    @if ($halamanAkhir < $fasilitas->lastPage() - 1)
                        <span class="gap">&hellip;</span>
                    @endif

                    <a
                        class="pg"
                        href="{{ $fasilitas->url($fasilitas->lastPage()) }}"
                        aria-label="Halaman {{ $fasilitas->lastPage() }}"
                    >
                        {{ $fasilitas->lastPage() }}
                    </a>

                @endif

                {{-- BERIKUTNYA --}}

                @if ($fasilitas->hasMorePages())

                    <a
                        class="pg pg-nav"
                        href="{{ $fasilitas->nextPageUrl() }}"
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

    </div>

</section>

@endsection
