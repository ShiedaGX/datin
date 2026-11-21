@extends('layouts.app')

@section('title', 'Data Fasilitas Transportasi')
@section('heading', 'Data Fasilitas Transportasi')
@section('subheading', 'Data referensi bandar udara, terminal, pelabuhan, dan stasiun')
@section('search_action', route('fasilitas.index'))
@section('search_placeholder', 'Cari nama fasilitas...')

@section('content')

@php
    /*
     * Menyiapkan data dropdown bertingkat.
     * Diproses di PHP terlebih dahulu agar aman untuk Blade.
     */
    $dataKabKotaJs = $kabKotaFilter->map(function ($kab) {
        return [
            'id' => (string) $kab->id_kab_kota,
            'nama' => $kab->nama_lengkap,
            'provinsi' => (string) $kab->id_provinsi,
            'wilayah' => (string) $kab->id_wilayah,
        ];
    })->values()->all();

    $dataProvinsiJs = $provinsi->map(function ($p) {
        return [
            'id' => (string) $p->id_provinsi,
            'nama' => $p->nama_provinsi,
        ];
    })->values()->all();
@endphp

<style>
    /* =====================================================
       INFORMASI
    ===================================================== */

    .ft-info {
        padding: 14px 18px;
        margin-bottom: 18px;
        background: #eff8f7;
        border: 1px solid #cceae5;
        border-radius: 10px;
        color: #35645f;
        font-size: 13px;
        line-height: 1.7;
    }

    .ft-info p {
        margin: 0;
    }

    /* =====================================================
       FILTER
    ===================================================== */

    .ft-filter {
        padding: 22px;
        margin-bottom: 20px;
    }

    .ft-filter-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 20px;
    }

    .ft-filter-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #17324d;
    }

    .ft-filter-header p {
        margin: 6px 0 0;
        font-size: 12px;
        color: #8190a2;
        line-height: 1.6;
    }

    .ft-filter-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .ft-field {
        min-width: 0;
    }

    .ft-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 600;
        color: #425a73;
    }

    .ft-field input,
    .ft-field select {
        display: block;
        width: 100%;
        height: 42px;
        padding: 0 12px;
        box-sizing: border-box;
        border: 1px solid #d4dee8;
        border-radius: 8px;
        background: #fff;
        color: #253b53;
        font: inherit;
        font-size: 13px;
        outline: none;
    }

    .ft-field input:focus,
    .ft-field select:focus {
        border-color: #12aaa0;
        box-shadow: 0 0 0 3px rgba(18, 170, 160, .10);
    }

    .ft-field input::placeholder {
        color: #94a3b8;
    }

    .ft-filter-divider {
        height: 1px;
        margin: 20px 0;
        background: #e5ebf1;
    }

    .ft-filter-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 15px;
        font-size: 12px;
        font-weight: 700;
        color: #425a73;
    }

    .ft-filter-section-title svg {
        color: #0e938b;
    }

    .ft-filter-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 22px;
    }

    .ft-filter-actions .count {
        margin-right: auto;
        color: #8190a2;
        font-size: 12px;
    }

    .ft-filter-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
    }

    .ft-filter-actions .reset {
        white-space: nowrap;
    }

    /* =====================================================
       NOTIFIKASI
    ===================================================== */

    .ft-notice {
        margin-bottom: 18px;
        padding: 13px 16px;
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
        min-width: 190px;
    }

    .table-fasilitas .col-wilayah {
        min-width: 180px;
    }

    .table-fasilitas .col-koordinat {
        min-width: 150px;
    }

    .table-fasilitas .col-aksi {
        width: 105px;
        white-space: nowrap;
    }

    .ft-nama {
        display: block;
        color: #182f49;
        font-weight: 700;
        line-height: 1.5;
    }

    .ft-sumber {
        display: block;
        margin-top: 4px;
        color: #8190a2;
        font-size: 11px;
        line-height: 1.5;
    }

    .ft-lokasi {
        display: block;
        color: #253b53;
        font-weight: 600;
        line-height: 1.5;
    }

    .ft-provinsi {
        display: block;
        margin-top: 3px;
        color: #8190a2;
        font-size: 11px;
    }

    .ft-koordinat {
        color: #53657b;
        font-size: 12px;
        font-variant-numeric: tabular-nums;
    }

    .ft-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ft-status::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .ft-status.aktif {
        color: #15945c;
    }

    .ft-status.nonaktif {
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

    @media (max-width: 1100px) {
        .ft-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .ft-filter {
            padding: 16px;
        }

        .ft-filter-grid {
            grid-template-columns: 1fr;
            gap: 13px;
        }

        .ft-filter-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .ft-filter-actions .count {
            margin-right: 0;
        }

        .ft-filter-actions .reset {
            align-self: flex-start;
        }

        .ft-filter-actions .btn {
            width: 100%;
            box-sizing: border-box;
        }

        .ft-info {
            padding: 12px 14px;
        }
    }
</style>


{{-- =====================================================
     INFORMASI HALAMAN
===================================================== --}}

<div class="ft-info">
    <p>
        Fasilitas transportasi merupakan data referensi yang terpisah
        dari pengajuan Pemerintah Daerah. Informasi lokasi,
        kabupaten/kota, dan jenis moda digunakan untuk mendukung
        analisis kebutuhan konektivitas regional.
    </p>
</div>


{{-- =====================================================
     NOTIFIKASI
===================================================== --}}

@if (session('success'))
    <div class="ft-notice" role="status">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error" role="alert">
        {{ session('error') }}
    </div>
@endif


{{-- =====================================================
     FILTER FASILITAS
===================================================== --}}

<form
    class="box ft-filter"
    method="GET"
    action="{{ route('fasilitas.index') }}"
    id="ft-form"
>

    <div class="ft-filter-header">
        <div>
            <h2>Filter Data Fasilitas</h2>
            <p>
                Cari fasilitas berdasarkan nama, moda transportasi,
                sumber data, dan lokasi administrasi.
            </p>
        </div>
    </div>

    {{-- BARIS 1 --}}

    <div class="ft-filter-grid">

        {{-- NAMA --}}

        <div class="ft-field">
            <label for="ft-cari">
                Nama Fasilitas
            </label>

            <input
                type="search"
                id="ft-cari"
                name="cari"
                value="{{ $filter['cari'] }}"
                placeholder="Cari nama fasilitas..."
            >
        </div>

        {{-- MODA --}}

        <div class="ft-field">
            <label for="ft-moda">
                Moda Transportasi
            </label>

            <select
                id="ft-moda"
                name="moda"
                data-autosubmit-ft
            >
                <option value="">Semua Moda</option>

                <option value="1" @selected($filter['moda'] === '1')>
                    Darat
                </option>

                <option value="2" @selected($filter['moda'] === '2')>
                    Laut
                </option>

                <option value="3" @selected($filter['moda'] === '3')>
                    Udara
                </option>

                <option value="4" @selected($filter['moda'] === '4')>
                    Kereta Api
                </option>
            </select>
        </div>

        {{-- SUMBER DATA --}}

        <div class="ft-field">
            <label for="ft-sumber">
                Sumber Data
            </label>

            <select
                id="ft-sumber"
                name="sumber_data"
                data-autosubmit-ft
            >
                <option value="">Semua Sumber Data</option>

                @foreach ($sumberData as $sumber)
                    <option
                        value="{{ $sumber }}"
                        @selected($filter['sumber_data'] === $sumber)
                    >
                        {{ $sumber }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <div class="ft-filter-divider"></div>

    <div class="ft-filter-section-title">
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
            <path d="M3 10l9-6 9 6"/>
            <path d="M5 10v10h14V10"/>
            <path d="M9 20v-7h6v7"/>
        </svg>

        Lokasi Administrasi
    </div>

    {{-- BARIS 2 --}}

    <div class="ft-filter-grid">

        {{-- WILAYAH REGIONAL --}}

        <div class="ft-field">
            <label for="ft-wilayah">
                Wilayah Regional
            </label>

            <select
                id="ft-wilayah"
                name="wilayah"
            >
                <option value="">Semua Wilayah</option>

                @foreach ($wilayah as $w)
                    <option
                        value="{{ $w->id_wilayah }}"
                        @selected($filter['wilayah'] === (string) $w->id_wilayah)
                    >
                        {{ $w->nama_wilayah }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- PROVINSI --}}

        <div class="ft-field">
            <label for="ft-provinsi">
                Provinsi
            </label>

            <select
                id="ft-provinsi"
                name="provinsi"
            >
                <option value="">Semua Provinsi</option>

                @foreach ($provinsi as $p)
                    <option
                        value="{{ $p->id_provinsi }}"
                        @selected($filter['provinsi'] === (string) $p->id_provinsi)
                    >
                        {{ $p->nama_provinsi }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- KABUPATEN/KOTA --}}

        <div class="ft-field">
            <label for="ft-kab-kota">
                Kabupaten/Kota
            </label>

            <select
                id="ft-kab-kota"
                name="kab_kota"
            >
                <option value="">Semua Kabupaten/Kota</option>

                <option
                    value="belum"
                    @selected($filter['kab_kota'] === 'belum')
                >
                    Belum Ditentukan
                </option>

                @foreach ($kabKotaFilter as $kab)
                    <option
                        value="{{ $kab->id_kab_kota }}"
                        @selected($filter['kab_kota'] === (string) $kab->id_kab_kota)
                    >
                        {{ $kab->nama_lengkap }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    {{-- AKSI FILTER --}}

    <div class="ft-filter-actions">

        <span class="count">
            Menampilkan
            {{ number_format($fasilitas->count(), 0, ',', '.') }}
            dari
            {{ number_format($fasilitas->total(), 0, ',', '.') }}
            entri
        </span>

        @if ($adaFilter)
            <a
                href="{{ route('fasilitas.index') }}"
                class="reset"
            >
                Reset Filter
            </a>
        @endif

        <button
            type="submit"
            class="btn btn-outline"
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

            Terapkan Filter
        </button>

        <a
            href="{{ route('fasilitas.create') }}"
            class="btn btn-new"
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

    </div>

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

                        {{-- NAMA --}}

                        <td data-label="Fasilitas">

                            <strong class="ft-nama">
                                {{ $item->nama }}
                            </strong>

                            <span class="ft-sumber">
                                {{ $item->sumber_data ?: 'Sumber tidak diketahui' }}
                            </span>

                        </td>

                        {{-- MODA --}}

                        <td data-label="Moda" class="soft">
                            {{ $item->moda?->nama_moda ?: '-' }}
                        </td>

                        {{-- KABUPATEN/KOTA --}}

                        <td data-label="Kabupaten/Kota">

                            @if ($item->kabKota)

                                <strong class="ft-lokasi">
                                    {{ $item->kabKota->nama_lengkap }}
                                </strong>

                                <span class="ft-provinsi">
                                    {{ $item->kabKota->provinsi?->nama_provinsi ?? '-' }}
                                </span>

                            @else

                                <span class="muted">
                                    Belum ditetapkan
                                </span>

                            @endif

                        </td>

                        {{-- KOORDINAT --}}

                        <td data-label="Koordinat">

                            @if (
                                $item->latitude !== null &&
                                $item->longitude !== null
                            )

                                <span class="ft-koordinat">
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

                                <span class="ft-status aktif">
                                    Aktif
                                </span>

                            @else

                                <span class="ft-status nonaktif">
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

                            Tidak ada fasilitas yang sesuai
                            dengan pencarian atau filter.

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
         PAGINATION
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

            <nav class="pager-num" aria-label="Halaman fasilitas">

                {{-- PREVIOUS --}}

                @if ($fasilitas->onFirstPage())

                    <span class="pg pg-nav is-disabled" aria-hidden="true">
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

                {{-- FIRST PAGE --}}

                @if ($halamanAwal > 1)

                    <a class="pg" href="{{ $fasilitas->url(1) }}">
                        1
                    </a>

                    @if ($halamanAwal > 2)
                        <span class="gap">&hellip;</span>
                    @endif

                @endif

                {{-- PAGE NUMBERS --}}

                @for ($page = $halamanAwal; $page <= $halamanAkhir; $page++)

                    @if ($page === $fasilitas->currentPage())

                        <span class="pg active" aria-current="page">
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

                {{-- LAST PAGE --}}

                @if ($halamanAkhir < $fasilitas->lastPage())

                    @if ($halamanAkhir < $fasilitas->lastPage() - 1)
                        <span class="gap">&hellip;</span>
                    @endif

                    <a
                        class="pg"
                        href="{{ $fasilitas->url($fasilitas->lastPage()) }}"
                    >
                        {{ $fasilitas->lastPage() }}
                    </a>

                @endif

                {{-- NEXT --}}

                @if ($fasilitas->hasMorePages())

                    <a
                        class="pg pg-nav"
                        href="{{ $fasilitas->nextPageUrl() }}"
                        aria-label="Halaman berikutnya"
                    >
                        &rsaquo;
                    </a>

                @else

                    <span class="pg pg-nav is-disabled" aria-hidden="true">
                        &rsaquo;
                    </span>

                @endif

            </nav>

        @endif

    </div>

</section>


{{-- =====================================================
     FILTER BERTINGKAT
===================================================== --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('ft-form');

    const wilayahSelect = document.getElementById('ft-wilayah');
    const provinsiSelect = document.getElementById('ft-provinsi');
    const kabKotaSelect = document.getElementById('ft-kab-kota');

    if (!form || !wilayahSelect || !provinsiSelect || !kabKotaSelect) {
        return;
    }

    /*
     * Data berasal dari controller.
     */
    const daftarKabKota = @json($dataKabKotaJs);
    const daftarProvinsi = @json($dataProvinsiJs);

    /*
     * Menyimpan nilai filter awal.
     */
    const nilaiAwal = {
        provinsi: provinsiSelect.value,
        kabKota: kabKotaSelect.value
    };

    /*
     * Membuat option dropdown.
     */
    function tambahOption(select, value, label) {
        const option = document.createElement('option');

        option.value = value;
        option.textContent = label;

        select.appendChild(option);
    }

    /*
     * Memeriksa apakah pilihan masih tersedia.
     */
    function pilihanTersedia(select, value) {
        return Array.from(select.options).some(function (option) {
            return option.value === value;
        });
    }

    /*
     * Filter provinsi berdasarkan regional.
     */
    function perbaruiProvinsi(nilaiDipilih = '') {

        const wilayahId = wilayahSelect.value;

        provinsiSelect.innerHTML = '';

        tambahOption(
            provinsiSelect,
            '',
            'Semua Provinsi'
        );

        const provinsiTersedia = new Set();

        daftarKabKota.forEach(function (kab) {

            if (wilayahId && kab.wilayah !== wilayahId) {
                return;
            }

            provinsiTersedia.add(kab.provinsi);
        });

        daftarProvinsi.forEach(function (provinsi) {

            if (!provinsiTersedia.has(provinsi.id)) {
                return;
            }

            tambahOption(
                provinsiSelect,
                provinsi.id,
                provinsi.nama
            );
        });

        provinsiSelect.value = pilihanTersedia(
            provinsiSelect,
            nilaiDipilih
        ) ? nilaiDipilih : '';
    }

    /*
     * Filter kabupaten/kota berdasarkan
     * regional dan provinsi.
     */
    function perbaruiKabKota(nilaiDipilih = '') {

        const wilayahId = wilayahSelect.value;
        const provinsiId = provinsiSelect.value;

        kabKotaSelect.innerHTML = '';

        tambahOption(
            kabKotaSelect,
            '',
            'Semua Kabupaten/Kota'
        );

        /*
         * Fasilitas tanpa wilayah hanya dapat dipilih
         * ketika filter regional dan provinsi kosong.
         */
        if (!wilayahId && !provinsiId) {
            tambahOption(
                kabKotaSelect,
                'belum',
                'Belum Ditentukan'
            );
        }

        daftarKabKota.forEach(function (kab) {

            if (wilayahId && kab.wilayah !== wilayahId) {
                return;
            }

            if (provinsiId && kab.provinsi !== provinsiId) {
                return;
            }

            tambahOption(
                kabKotaSelect,
                kab.id,
                kab.nama
            );
        });

        kabKotaSelect.value = pilihanTersedia(
            kabKotaSelect,
            nilaiDipilih
        ) ? nilaiDipilih : '';
    }

    /*
     * Inisialisasi ketika halaman dibuka.
     */
    perbaruiProvinsi(nilaiAwal.provinsi);
    perbaruiKabKota(nilaiAwal.kabKota);

    /*
     * Regional berubah.
     */
    wilayahSelect.addEventListener('change', function () {

        perbaruiProvinsi('');
        perbaruiKabKota('');

        form.requestSubmit();
    });

    /*
     * Provinsi berubah.
     */
    provinsiSelect.addEventListener('change', function () {

        perbaruiKabKota('');

        form.requestSubmit();
    });

    /*
     * Kabupaten/kota berubah.
     */
    kabKotaSelect.addEventListener('change', function () {

        form.requestSubmit();
    });

    /*
     * Moda dan sumber data berubah.
     */
    document.querySelectorAll('[data-autosubmit-ft]')
        .forEach(function (select) {

            select.addEventListener('change', function () {

                form.requestSubmit();
            });
        });

});
</script>

@endsection
