@extends('layouts.app')

@section('title', $fasilitas ? 'Edit Fasilitas' : 'Tambah Fasilitas')

@section('heading', $fasilitas ? 'Edit Fasilitas Transportasi' : 'Tambah Fasilitas Transportasi')

@section('subheading', 'Kelola data referensi fasilitas transportasi')

@section('content')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/tom-select@2.7.0/dist/css/tom-select.css"
/>

<style>
    /* =========================================
       LAYOUT UTAMA
    ========================================= */

    .facility-form-page {
        max-width: 1120px;
        margin: 0 auto;
    }

    .facility-form-page .box {
        background: #fff;
        border: 1px solid #dce5ee;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(15, 35, 65, .025);
    }

    .facility-form-card {
        padding: 26px;
    }

    .facility-form-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e8edf3;
    }

    .facility-form-header h2 {
        margin: 0 0 6px;
        color: #132b49;
        font-size: 19px;
        font-weight: 700;
        line-height: 1.4;
    }

    .facility-form-header p {
        margin: 0;
        color: #738399;
        font-size: 13px;
        line-height: 1.6;
    }

    .facility-form-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #e7f7f4;
        color: #087e73;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* =========================================
       FORM SECTION
    ========================================= */

    .facility-section {
        margin-bottom: 28px;
    }

    .facility-section-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 20px;
    }

    .facility-section-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 9px;
        background: #e8f7f5;
        color: #0d9488;
        font-size: 14px;
        font-weight: 800;
    }

    .facility-section-title {
        margin: 0;
        color: #173557;
        font-size: 16px;
        font-weight: 700;
    }

    .facility-section-desc {
        margin: 5px 0 0;
        color: #75859a;
        font-size: 12px;
        line-height: 1.6;
    }

    .facility-divider {
        height: 1px;
        background: #e8edf3;
        margin: 25px 0;
    }

    /* =========================================
       INPUT
    ========================================= */

    .facility-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .facility-field {
        min-width: 0;
        margin-bottom: 18px;
    }

    .facility-label {
        display: block;
        margin-bottom: 8px;
        color: #34465e;
        font-size: 13px;
        font-weight: 650;
    }

    .facility-required {
        color: #dc2626;
    }

    .facility-input,
    .facility-select {
        width: 100%;
        height: 43px;
        padding: 0 13px;
        border: 1px solid #d1dbe6;
        border-radius: 8px;
        background: #fff;
        box-sizing: border-box;
        color: #20324c;
        font-family: inherit;
        font-size: 13px;
        transition: border-color .2s, box-shadow .2s;
    }

    .facility-input:focus,
    .facility-select:focus {
        outline: none;
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, .10);
    }

    .facility-input::placeholder {
        color: #a3b0c0;
    }

    .facility-help {
        margin: 7px 0 0;
        color: #8290a3;
        font-size: 12px;
        line-height: 1.6;
    }

    .facility-error {
        margin-top: 6px;
        color: #b42318;
        font-size: 12px;
    }

    /* =========================================
       TOM SELECT
    ========================================= */

    .facility-form-page .ts-wrapper {
        width: 100%;
    }

    .facility-form-page .ts-control {
        min-height: 43px;
        padding: 10px 13px;
        border: 1px solid #d1dbe6;
        border-radius: 8px;
        box-shadow: none;
        font-size: 13px;
        align-items: center;
        color: #20324c;
    }

    .facility-form-page .ts-wrapper.focus .ts-control {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, .10);
    }

    .facility-form-page .ts-dropdown {
        border: 1px solid #dce5ee;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(15, 35, 65, .10);
        font-size: 13px;
    }

    .facility-form-page .ts-dropdown .option {
        padding: 10px 13px;
    }

    .facility-form-page .ts-dropdown .active {
        background: #e7f7f4;
        color: #087e73;
    }

    /* =========================================
       INFORMASI
    ========================================= */

    .facility-info {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 14px 16px;
        background: #eff8f7;
        border: 1px solid #cfece6;
        border-radius: 9px;
        margin-bottom: 26px;
        color: #32645f;
        font-size: 12px;
        line-height: 1.7;
    }

    .facility-info svg {
        flex-shrink: 0;
        color: #0d9488;
        margin-top: 1px;
    }

    /* =========================================
       PETA
    ========================================= */

    .facility-map-card {
        border: 1px solid #dce5ee;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .facility-map-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 17px;
        background: #f8fafc;
        border-bottom: 1px solid #e5ebf2;
    }

    .facility-map-header h3 {
        margin: 0 0 4px;
        color: #173557;
        font-size: 13px;
        font-weight: 700;
    }

    .facility-map-header p {
        margin: 0;
        color: #8290a3;
        font-size: 12px;
    }

    .facility-map-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        background: #e7f7f4;
        border-radius: 6px;
        color: #0d9488;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    #facility-map {
        width: 100%;
        height: 385px;
        background: #e6eef4;
        z-index: 1;
    }

    .facility-map-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 12px 16px;
        background: #fff;
        border-top: 1px solid #e5ebf2;
    }

    .facility-map-status {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #7b8b9e;
        font-size: 12px;
    }

    .facility-map-status.is-set {
        color: #087e73;
        font-weight: 600;
    }

    .facility-map-reset {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: #fff;
        border: 1px solid #d1dbe6;
        border-radius: 7px;
        color: #34465e;
        font-family: inherit;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .facility-map-reset:hover {
        background: #f1f5f9;
    }

    /* =========================================
       FOOTER BUTTONS
    ========================================= */

    .facility-form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        padding-top: 22px;
        border-top: 1px solid #e8edf3;
    }

    .facility-form-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        box-sizing: border-box;
    }

    .facility-btn-cancel {
        background: #fff;
        color: #64748b;
        border: 1px solid #d1dbe6;
    }

    .facility-btn-cancel:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .facility-btn-save {
        background: #0d9488;
        color: #fff;
        border: 1px solid #0d9488;
    }

    .facility-btn-save:hover {
        background: #087e73;
        border-color: #087e73;
        color: #fff;
    }

    /* =========================================
       RESPONSIVE
    ========================================= */

    @media (max-width: 750px) {
        .facility-form-card {
            padding: 18px;
        }

        .facility-form-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .facility-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }

        #facility-map {
            height: 310px;
        }

        .facility-map-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .facility-form-actions {
            flex-wrap: wrap;
        }

        .facility-form-actions .btn {
            flex: 1;
        }
    }
</style>


<div class="facility-form-page">

    <section class="box facility-form-card">

        {{-- =========================================
             HEADER
        ========================================= --}}

        <div class="facility-form-header">

            <div>

                <h2>
                    {{ $fasilitas ? 'Perbarui Informasi Fasilitas' : 'Informasi Fasilitas Baru' }}
                </h2>

                <p>
                    Kelola identitas, wilayah administratif,
                    dan titik lokasi fasilitas transportasi.
                </p>

            </div>

            <span class="facility-form-badge">

                <svg
                    viewBox="0 0 24 24"
                    width="15"
                    height="15"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M12 3l8 4v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>

                Data Referensi

            </span>

        </div>


        {{-- =========================================
             INFORMASI
        ========================================= --}}

        <div class="facility-info">

            <svg
                viewBox="0 0 24 24"
                width="19"
                height="19"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 11v5M12 8h.01"/>
            </svg>

            <div>
                Fasilitas merupakan data referensi yang terpisah dari
                pengajuan Pemda.

                Kabupaten/kota digunakan untuk menghubungkan fasilitas
                dengan master wilayah DATIN.

                Koordinat harus menunjukkan lokasi fasilitas sebenarnya,
                bukan titik pusat kabupaten/kota.
            </div>

        </div>


        {{-- =========================================
             FORM
        ========================================= --}}

        <form
            method="POST"
            action="{{ $fasilitas ? route('fasilitas.update', $fasilitas) : route('fasilitas.store') }}"
            id="facility-form"
        >

            @csrf

            @if($fasilitas)
                @method('PUT')
            @endif


            {{-- =========================================
                 SECTION 1
            ========================================= --}}

            <div class="facility-section">

                <div class="facility-section-head">

                    <span class="facility-section-number">
                        01
                    </span>

                    <div>

                        <h3 class="facility-section-title">
                            Identitas Fasilitas
                        </h3>

                        <p class="facility-section-desc">
                            Informasi dasar fasilitas transportasi
                            yang akan digunakan sebagai data referensi.
                        </p>

                    </div>

                </div>


                <div class="facility-grid">

                    {{-- MODA --}}

                    <div class="facility-field">

                        <label
                            class="facility-label"
                            for="ft-moda"
                        >
                            Jenis Moda
                            <span class="facility-required">*</span>
                        </label>

                        <select
                            class="facility-select"
                            name="id_moda"
                            id="ft-moda"
                            required
                        >

                            <option value="">
                                Pilih moda transportasi
                            </option>

                            @foreach([
                                1 => 'Darat (Terminal)',
                                2 => 'Laut (Pelabuhan)',
                                3 => 'Udara (Bandar Udara)',
                                4 => 'Kereta Api (Stasiun)',
                            ] as $id => $nama)

                                <option
                                    value="{{ $id }}"
                                    @selected(
                                        (string) old('id_moda', $fasilitas?->id_moda)
                                        === (string) $id
                                    )
                                >
                                    {{ $nama }}
                                </option>

                            @endforeach

                        </select>

                        @error('id_moda')
                            <div class="facility-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- NAMA --}}

                    <div class="facility-field">

                        <label
                            class="facility-label"
                            for="ft-nama"
                        >
                            Nama Fasilitas
                            <span class="facility-required">*</span>
                        </label>

                        <input
                            class="facility-input"
                            type="text"
                            name="nama"
                            id="ft-nama"
                            maxlength="150"
                            required
                            value="{{ old('nama', $fasilitas?->nama) }}"
                            placeholder="Contoh: Terminal Dungingi"
                        >

                        @error('nama')
                            <div class="facility-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>


                {{-- KABUPATEN/KOTA --}}

                <div class="facility-field">

                    <label
                        class="facility-label"
                        for="ft-kab"
                    >
                        Kabupaten/Kota Terkait
                    </label>

                    <select
                        id="ft-kab"
                        name="id_kab_kota"
                    >

                        <option value="">
                            Cari atau pilih kabupaten/kota...
                        </option>

                        @foreach($kabKota as $kab)

                            <option
                                value="{{ $kab->id_kab_kota }}"
                                data-lat="{{ $kab->latitude }}"
                                data-lon="{{ $kab->longitude }}"
                                @selected(
                                    (string) old(
                                        'id_kab_kota',
                                        $fasilitas?->id_kab_kota
                                    ) === (string) $kab->id_kab_kota
                                )
                            >
                                {{ $kab->nama_lengkap }}
                                — {{ $kab->provinsi?->nama_provinsi }}
                            </option>

                        @endforeach

                    </select>

                    <p class="facility-help">
                        Cari wilayah dengan mengetik nama kabupaten/kota.
                        Pemilihan wilayah akan memusatkan peta,
                        tetapi tidak otomatis menentukan lokasi fasilitas.
                    </p>

                    @error('id_kab_kota')
                        <div class="facility-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>


            <div class="facility-divider"></div>


            {{-- =========================================
                 SECTION 2
            ========================================= --}}

            <div class="facility-section">

                <div class="facility-section-head">

                    <span class="facility-section-number">
                        02
                    </span>

                    <div>

                        <h3 class="facility-section-title">
                            Lokasi Geospasial
                        </h3>

                        <p class="facility-section-desc">
                            Tentukan titik fasilitas melalui peta interaktif
                            atau masukkan koordinat secara manual.
                        </p>

                    </div>

                </div>


                {{-- PETA --}}

                <div class="facility-map-card">

                    <div class="facility-map-header">

                        <div>

                            <h3>
                                Pilih Lokasi Fasilitas
                            </h3>

                            <p>
                                Klik peta atau geser pin untuk mengatur koordinat.
                            </p>

                        </div>

                        <span class="facility-map-label">

                            <svg
                                viewBox="0 0 24 24"
                                width="14"
                                height="14"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1116 0z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>

                            Peta Interaktif

                        </span>

                    </div>


                    <div id="facility-map"></div>


                    <div class="facility-map-footer">

                        <span
                            class="facility-map-status"
                            id="facility-map-status"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                width="15"
                                height="15"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1116 0z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>

                            <span id="facility-map-status-text">
                                Belum ada titik fasilitas dipilih
                            </span>

                        </span>


                        <button
                            type="button"
                            class="facility-map-reset"
                            id="facility-reset-map"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                width="14"
                                height="14"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M3 11a9 9 0 111.5 5"/>
                                <path d="M3 4v7h7"/>
                            </svg>

                            Reset Tampilan

                        </button>

                    </div>

                </div>


                {{-- KOORDINAT --}}

                <div class="facility-grid">

                    <div class="facility-field">

                        <label
                            class="facility-label"
                            for="ft-lat"
                        >
                            Latitude
                            <span class="facility-required">*</span>
                        </label>

                        <input
                            class="facility-input"
                            id="ft-lat"
                            type="number"
                            step="any"
                            min="-90"
                            max="90"
                            name="latitude"
                            required
                            value="{{ old('latitude', $fasilitas?->latitude) }}"
                            placeholder="Contoh: -6.1256000"
                        >

                        <p class="facility-help">
                            Rentang -90 hingga 90.
                            Angka negatif menunjukkan lintang selatan.
                        </p>

                        @error('latitude')
                            <div class="facility-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="facility-field">

                        <label
                            class="facility-label"
                            for="ft-lon"
                        >
                            Longitude
                            <span class="facility-required">*</span>
                        </label>

                        <input
                            class="facility-input"
                            id="ft-lon"
                            type="number"
                            step="any"
                            min="-180"
                            max="180"
                            name="longitude"
                            required
                            value="{{ old('longitude', $fasilitas?->longitude) }}"
                            placeholder="Contoh: 106.6559000"
                        >

                        <p class="facility-help">
                            Rentang -180 hingga 180.
                            Indonesia umumnya berada di bujur timur.
                        </p>

                        @error('longitude')
                            <div class="facility-error">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>


            <div class="facility-divider"></div>


            {{-- =========================================
                 SECTION 3
            ========================================= --}}

            <div class="facility-section">

                <div class="facility-section-head">

                    <span class="facility-section-number">
                        03
                    </span>

                    <div>

                        <h3 class="facility-section-title">
                            Status Fasilitas
                        </h3>

                        <p class="facility-section-desc">
                            Atur apakah fasilitas dapat digunakan
                            sebagai referensi dalam sistem.
                        </p>

                    </div>

                </div>


                <div class="facility-field">

                    <label
                        class="facility-label"
                        for="ft-aktif"
                    >
                        Status
                    </label>

                    <select
                        class="facility-select"
                        name="aktif"
                        id="ft-aktif"
                        required
                    >

                        <option
                            value="1"
                            @selected(
                                (int) old(
                                    'aktif',
                                    $fasilitas?->aktif ?? 1
                                ) === 1
                            )
                        >
                            Aktif
                        </option>

                        <option
                            value="0"
                            @selected(
                                (int) old(
                                    'aktif',
                                    $fasilitas?->aktif ?? 1
                                ) === 0
                            )
                        >
                            Nonaktif
                        </option>

                    </select>

                    <p class="facility-help">
                        Fasilitas aktif dapat digunakan sebagai referensi
                        pada Peta Geospasial DATIN.
                    </p>

                    @error('aktif')
                        <div class="facility-error">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>


            {{-- =========================================
                 TOMBOL AKSI
            ========================================= --}}

            <div class="facility-form-actions">

                <a
                    href="{{ route('fasilitas.index') }}"
                    class="btn facility-btn-cancel"
                >
                    Batal
                </a>


                <button
                    type="submit"
                    class="btn facility-btn-save"
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
                        <path d="M5 3h12l4 4v14H3V3h2z"/>
                        <path d="M7 3v6h10V3M7 21v-8h10v8"/>
                    </svg>

                    {{ $fasilitas ? 'Simpan Perubahan' : 'Simpan Fasilitas' }}

                </button>

            </div>

        </form>

    </section>

</div>


{{-- =========================================
     JAVASCRIPT
========================================= --}}

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.7.0/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const latInput = document.getElementById('ft-lat');
    const lonInput = document.getElementById('ft-lon');
    const kabSelect = document.getElementById('ft-kab');
    const statusElement = document.getElementById('facility-map-status');
    const statusText = document.getElementById('facility-map-status-text');
    const resetButton = document.getElementById('facility-reset-map');
    const form = document.getElementById('facility-form');

    const indonesiaCenter = [-2.5, 118];
    const indonesiaZoom = 5;

    /*
    |--------------------------------------------------------------------------
    | VALIDASI KOORDINAT
    |--------------------------------------------------------------------------
    */

    function getCoordinates() {
        if (
            latInput.value.trim() === '' ||
            lonInput.value.trim() === ''
        ) {
            return null;
        }

        const lat = Number(latInput.value);
        const lon = Number(lonInput.value);

        if (
            !Number.isFinite(lat) ||
            !Number.isFinite(lon) ||
            lat < -90 ||
            lat > 90 ||
            lon < -180 ||
            lon > 180
        ) {
            return null;
        }

        return { lat, lon };
    }

    /*
    |--------------------------------------------------------------------------
    | TAMPILAN STATUS
    |--------------------------------------------------------------------------
    */

    function updateStatus(lat, lon) {
        statusText.textContent =
            'Titik: ' +
            lat.toFixed(7) +
            ', ' +
            lon.toFixed(7);

        statusElement.classList.add('is-set');
    }

    function showInvalidStatus() {
        statusText.textContent =
            'Masukkan koordinat latitude dan longitude yang valid';

        statusElement.classList.remove('is-set');
    }

    /*
    |--------------------------------------------------------------------------
    | INISIALISASI PETA
    |--------------------------------------------------------------------------
    */

    if (typeof L === 'undefined') {
        statusText.textContent =
            'Peta gagal dimuat. Koordinat dapat diisi manual.';

        return;
    }

    const initialCoordinates = getCoordinates();

    const map = L.map('facility-map', {
        scrollWheelZoom: false
    }).setView(
        initialCoordinates
            ? [
                initialCoordinates.lat,
                initialCoordinates.lon
            ]
            : indonesiaCenter,

        initialCoordinates
            ? 13
            : indonesiaZoom
    );

    /*
    |--------------------------------------------------------------------------
    | PETA DASAR
    |--------------------------------------------------------------------------
    */

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(map);

    /*
    |--------------------------------------------------------------------------
    | MARKER
    |--------------------------------------------------------------------------
    */

    let marker = null;

    function setMarker(lat, lon, moveMap = true) {
        const position = [lat, lon];

        if (!marker) {
            marker = L.marker(position, {
                draggable: true,
                autoPan: true
            }).addTo(map);

            marker.on('dragend', function () {
                const point = marker.getLatLng();

                setCoordinates(
                    point.lat,
                    point.lng,
                    false
                );
            });
        } else {
            marker.setLatLng(position);
        }

        if (moveMap) {
            map.setView(
                position,
                Math.max(map.getZoom(), 13)
            );
        }

        updateStatus(lat, lon);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE KOORDINAT DARI PETA
    |--------------------------------------------------------------------------
    */

    function setCoordinates(lat, lon, moveMap = true) {
        latInput.value = lat.toFixed(7);
        lonInput.value = lon.toFixed(7);

        setMarker(lat, lon, moveMap);
    }

    /*
    |--------------------------------------------------------------------------
    | KLIK PETA
    |--------------------------------------------------------------------------
    */

    map.on('click', function (event) {
        setCoordinates(
            event.latlng.lat,
            event.latlng.lng,
            false
        );
    });

    /*
    |--------------------------------------------------------------------------
    | INPUT KOORDINAT MANUAL
    |--------------------------------------------------------------------------
    */

    function updateFromInput() {
        const coordinates = getCoordinates();

        if (!coordinates) {
            showInvalidStatus();
            return;
        }

        setMarker(
            coordinates.lat,
            coordinates.lon,
            true
        );
    }

    latInput.addEventListener('change', updateFromInput);
    lonInput.addEventListener('change', updateFromInput);

    latInput.addEventListener('input', function () {
        if (getCoordinates()) {
            updateFromInput();
        }
    });

    lonInput.addEventListener('input', function () {
        if (getCoordinates()) {
            updateFromInput();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | TAMPILKAN LOKASI SAAT EDIT
    |--------------------------------------------------------------------------
    */

    if (initialCoordinates) {
        setMarker(
            initialCoordinates.lat,
            initialCoordinates.lon,
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FOKUS KE KABUPATEN/KOTA
    |--------------------------------------------------------------------------
    |
    | Titik kabupaten/kota hanya untuk mengarahkan peta.
    | Koordinat fasilitas tidak otomatis berubah.
    |
    */

    function focusKabupaten(id) {
        if (!id) {
            return;
        }

        const option = Array.from(kabSelect.options)
            .find(item => item.value === String(id));

        if (!option) {
            return;
        }

        const latText = option.dataset.lat;
        const lonText = option.dataset.lon;

        if (!latText || !lonText) {
            statusText.textContent =
                'Koordinat pusat wilayah belum tersedia';
            return;
        }

        const lat = Number(latText);
        const lon = Number(lonText);

        if (
            !Number.isFinite(lat) ||
            !Number.isFinite(lon) ||
            lat < -90 ||
            lat > 90 ||
            lon < -180 ||
            lon > 180
        ) {
            return;
        }

        map.setView([lat, lon], 11);
    }

    /*
    |--------------------------------------------------------------------------
    | PENCARIAN KABUPATEN/KOTA
    |--------------------------------------------------------------------------
    */

    if (typeof TomSelect !== 'undefined') {
        new TomSelect('#ft-kab', {
            create: false,
            allowEmptyOption: true,
            maxOptions: 100,
            placeholder: 'Cari nama kabupaten/kota...',
            searchField: [
                {
                    field: 'text',
                    weight: 1
                }
            ],
            onChange: function (value) {
                focusKabupaten(value);
            }
        });
    } else {
        kabSelect.addEventListener(
            'change',
            function () {
                focusKabupaten(this.value);
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET TAMPILAN PETA
    |--------------------------------------------------------------------------
    */

    resetButton.addEventListener('click', function () {
        map.setView(
            indonesiaCenter,
            indonesiaZoom
        );
    });

    /*
    |--------------------------------------------------------------------------
    | PERBAIKAN UKURAN PETA
    |--------------------------------------------------------------------------
    */

    window.setTimeout(function () {
        map.invalidateSize();
    }, 200);

    /*
    |--------------------------------------------------------------------------
    | VALIDASI SEBELUM SIMPAN
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {
        if (!getCoordinates()) {
            event.preventDefault();

            alert(
                'Latitude dan longitude wajib diisi dengan koordinat yang valid.'
            );

            latInput.focus();
        }
    });
});
</script>

@endsection
