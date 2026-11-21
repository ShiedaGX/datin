@extends('layouts.app')

@section('title', 'Master Satuan Data')
@section('heading', 'Master Satuan Data')
@section('subheading', 'Kelola satuan yang digunakan dalam pengajuan subsidi transportasi')

@section('content')

<style>
    /* ===== Layout ===== */

    .ms-section {
        margin-bottom: 20px;
    }

    .ms-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
    }

    .ms-head h2 {
        margin: 0 0 6px;
        color: #18324f;
        font-size: 16px;
        font-weight: 700;
    }

    .ms-head p {
        margin: 0;
        font-size: 13px;
        line-height: 1.7;
    }

    .ms-counter {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 8px;
        background: #eaf8f5;
        color: #087c73;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* ===== Form ===== */

    .ms-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ms-field {
        flex: 1 1 300px;
        min-width: 0;
    }

    .ms-field label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .ms-required {
        color: #dc3545;
    }

    .ms-field input {
        display: block;
        width: 100%;
        height: 42px;
        padding: 10px 13px;
        border: 1px solid #d4dee8;
        border-radius: 8px;
        background: #fff;
        color: #1e293b;
        font: inherit;
        font-size: 13px;
        box-sizing: border-box;
        transition: border-color .2s, box-shadow .2s;
    }

    .ms-field input::placeholder {
        color: #94a3b8;
    }

    .ms-field input:focus {
        outline: none;
        border-color: #11a99d;
        box-shadow: 0 0 0 3px rgba(17, 169, 157, .10);
    }

    .ms-form .btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
        cursor: pointer;
    }

    .ms-help {
        margin: 8px 0 0;
        color: #8391a5;
        font-size: 12px;
        line-height: 1.6;
    }

    /* ===== Notifikasi kesalahan ===== */

    .ms-alert-error {
        padding: 12px 15px;
        margin-bottom: 18px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        color: #b42318;
        font-size: 13px;
        line-height: 1.6;
    }

    .ms-alert-error p {
        margin: 0;
    }

    .ms-alert-error p + p {
        margin-top: 5px;
    }

    /* ===== Tabel ===== */

    .table-satuan {
        width: 100%;
    }

    .table-satuan th,
    .table-satuan td {
        vertical-align: middle;
    }

    .table-satuan .ms-name {
        color: #1b344e;
        font-weight: 700;
    }

    .ms-col-no {
        width: 65px;
    }

    .ms-col-status {
        width: 135px;
    }

    .ms-col-usage {
        width: 190px;
    }

    .ms-col-action {
        width: 165px;
    }

    /* ===== Status ===== */

    .ms-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ms-status::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .ms-status.is-active {
        color: #15945c;
    }

    .ms-status.is-inactive {
        color: #bd792e;
    }

    /* ===== Penggunaan ===== */

    .ms-usage {
        font-size: 12px;
        line-height: 1.5;
    }

    .ms-usage.used {
        color: #64748b;
    }

    .ms-usage.unused {
        color: #0d9488;
    }

    /* ===== Aksi ===== */

    .table-satuan .acts {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .table-satuan .acts form {
        display: inline-flex;
        margin: 0;
    }

    .table-satuan .acts .act {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .ms-act-toggle {
        color: #b57b22;
    }

    .ms-act-enable {
        color: #0d9488;
    }

    .table-satuan .acts .act:disabled {
        opacity: .3;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ===== Edit inline ===== */

    .table-satuan .ms-edit-row[hidden] {
        display: none !important;
    }

    .table-satuan .ms-edit-row td {
        padding: 18px 20px;
        background: #f5faf9;
        border-bottom: 1px solid #d8eae6;
    }

    .ms-edit-content {
        max-width: 760px;
    }

    .ms-edit-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 15px;
        color: #173d49;
        font-size: 13px;
        font-weight: 700;
    }

    .ms-edit-title svg {
        color: #0d9488;
    }

    .ms-edit-form {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
    }

    .ms-edit-form .ms-field {
        flex: 1 1 250px;
    }

    .ms-edit-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ms-edit-buttons .btn {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    /* ===== Footer ===== */

    .ms-table-note {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #64748b;
        font-size: 12px;
    }

    .ms-table-note svg {
        flex-shrink: 0;
    }

    /* ===== Responsive ===== */

    @media (max-width: 768px) {
        .ms-head {
            flex-direction: column;
            gap: 10px;
        }

        .ms-form,
        .ms-edit-form {
            flex-direction: column;
            align-items: stretch;
        }

        .ms-field,
        .ms-edit-form .ms-field {
            width: 100%;
            flex: auto;
        }

        .ms-form .btn {
            width: 100%;
        }

        .ms-edit-buttons {
            width: 100%;
        }

        .ms-edit-buttons .btn {
            flex: 1;
        }
    }
</style>


{{-- =====================================================
     NOTIFIKASI
     Success sudah ditampilkan oleh layouts.app
===================================================== --}}

@if (session('error'))
    <div class="ms-alert-error" role="alert">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="ms-alert-error" role="alert">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif


{{-- =====================================================
     TAMBAH SATUAN DATA
===================================================== --}}

<section class="box ms-section">

    <div class="ms-head">
        <div>
            <h2>Tambah Satuan Data</h2>

            <p class="muted">
                Tambahkan satuan yang dapat dipilih oleh Pemda
                saat mengisi data pengajuan subsidi transportasi.
            </p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('admin.master-satuan.store') }}"
        class="ms-form"
    >
        @csrf

        <div class="ms-field">
            <label for="nama_satuan">
                Nama Satuan <span class="ms-required">*</span>
            </label>

            <input
                type="text"
                id="nama_satuan"
                name="nama_satuan"
                value="{{ old('nama_satuan') }}"
                maxlength="100"
                placeholder="Contoh: Kilogram, Ton, Liter"
                autocomplete="off"
                required
            >
        </div>

        <button type="submit" class="btn btn-new">
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

            Tambah Satuan
        </button>
    </form>

    <p class="ms-help">
        Gunakan nama satuan yang jelas dan konsisten
        agar tidak terjadi duplikasi data.
    </p>

</section>


{{-- =====================================================
     DAFTAR SATUAN DATA
===================================================== --}}

<section class="box box-table ms-section">

    <div class="ms-head" style="padding:20px 20px 0;margin-bottom:15px;">

        <div>
            <h2>Daftar Satuan Data</h2>

            <p class="muted">
                Kelola status dan nama satuan data yang telah terdaftar.
            </p>
        </div>

        <span class="ms-counter">
            <svg
                viewBox="0 0 24 24"
                width="16"
                height="16"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <rect x="5" y="4" width="14" height="17" rx="2"/>
                <path d="M9 9h6M9 13h6M9 17h4"/>
            </svg>

            {{ number_format($satuan->count(), 0, ',', '.') }} Satuan
        </span>

    </div>

    <div class="table-box">

        <table class="table table-data table-satuan">

            <thead>
                <tr>
                    <th class="ms-col-no">No</th>
                    <th>Nama Satuan</th>
                    <th class="ms-col-status">Status</th>
                    <th class="ms-col-usage">Penggunaan</th>
                    <th class="ms-col-action">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($satuan as $item)

                    @php
                        $digunakan = $item->sudah_digunakan;
                    @endphp

                    <tr>

                        {{-- NOMOR --}}

                        <td data-label="No">
                            {{ $loop->iteration }}
                        </td>

                        {{-- NAMA SATUAN --}}

                        <td data-label="Nama Satuan">
                            <strong class="ms-name">
                                {{ $item->nama_satuan }}
                            </strong>
                        </td>

                        {{-- STATUS --}}

                        <td data-label="Status">

                            @if ($item->status_aktif)
                                <span class="ms-status is-active">
                                    Aktif
                                </span>
                            @else
                                <span class="ms-status is-inactive">
                                    Nonaktif
                                </span>
                            @endif

                        </td>

                        {{-- PENGGUNAAN --}}

                        <td data-label="Penggunaan">

                            @if ($digunakan)
                                <span class="ms-usage used">
                                    Sudah digunakan
                                </span>
                            @else
                                <span class="ms-usage unused">
                                    Belum digunakan
                                </span>
                            @endif

                        </td>

                        {{-- AKSI --}}

                        <td class="td-aksi" data-label="Aksi">

                            <div class="acts">

                                {{-- EDIT --}}

                                <button
                                    type="button"
                                    class="act act-edit"
                                    onclick="toggleEdit({{ $item->id_satuan_data }})"
                                    title="Edit satuan"
                                    aria-label="Edit {{ $item->nama_satuan }}"
                                    aria-controls="edit-{{ $item->id_satuan_data }}"
                                    aria-expanded="false"
                                    id="edit-button-{{ $item->id_satuan_data }}"
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
                                </button>

                                {{-- AKTIFKAN / NONAKTIFKAN --}}

                                <form
                                    method="POST"
                                    action="{{ route('admin.master-satuan.toggle', $item) }}"
                                    onsubmit="return confirm(@js($item->status_aktif ? 'Nonaktifkan satuan ini?' : 'Aktifkan kembali satuan ini?'))"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="act {{ $item->status_aktif ? 'ms-act-toggle' : 'ms-act-enable' }}"
                                        title="{{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}"
                                        aria-label="{{ $item->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }} {{ $item->nama_satuan }}"
                                    >

                                        @if ($item->status_aktif)

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
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="M5.7 5.7 18.3 18.3"/>
                                            </svg>

                                        @else

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
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="m8 12 3 3 5-6"/>
                                            </svg>

                                        @endif

                                    </button>
                                </form>

                                {{-- HAPUS --}}

                                @if (! $digunakan)

                                    <form
                                        method="POST"
                                        action="{{ route('admin.master-satuan.destroy', $item) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus satuan ini secara permanen?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="act act-del"
                                            title="Hapus satuan"
                                            aria-label="Hapus {{ $item->nama_satuan }}"
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

                                @else

                                    <button
                                        type="button"
                                        class="act act-del"
                                        disabled
                                        title="Satuan sudah digunakan dan tidak dapat dihapus"
                                        aria-label="Tidak dapat menghapus {{ $item->nama_satuan }}"
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

                                @endif

                            </div>

                        </td>

                    </tr>

                    {{-- FORM EDIT INLINE --}}

                    <tr
                        id="edit-{{ $item->id_satuan_data }}"
                        class="ms-edit-row"
                        hidden
                    >
                        <td colspan="5">

                            <div class="ms-edit-content">

                                <h3 class="ms-edit-title">

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

                                    Edit Satuan Data

                                </h3>

                                <form
                                    method="POST"
                                    action="{{ route('admin.master-satuan.update', $item) }}"
                                    class="ms-edit-form"
                                >
                                    @csrf
                                    @method('PUT')

                                    <div class="ms-field">
                                        <label for="nama-edit-{{ $item->id_satuan_data }}">
                                            Nama Satuan
                                            <span class="ms-required">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            id="nama-edit-{{ $item->id_satuan_data }}"
                                            name="nama_satuan"
                                            value="{{ $item->nama_satuan }}"
                                            maxlength="100"
                                            required
                                        >
                                    </div>

                                    <div class="ms-edit-buttons">

                                        <button
                                            type="submit"
                                            class="btn btn-new"
                                        >
                                            Simpan Perubahan
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-outline"
                                            onclick="toggleEdit({{ $item->id_satuan_data }})"
                                        >
                                            Batal
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="empty">
                            Belum ada satuan data yang terdaftar.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- FOOTER --}}

    <div class="table-foot">

        <p class="muted">
            Menampilkan
            {{ number_format($satuan->count(), 0, ',', '.') }}
            data satuan
        </p>

        <div class="ms-table-note">

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
                <path d="M12 11v5M12 8h.01"/>
            </svg>

            Satuan yang sudah digunakan tidak dapat dihapus permanen.

        </div>

    </div>

</section>


{{-- =====================================================
     SCRIPT EDIT
===================================================== --}}

<script>
    function toggleEdit(id) {
        const row = document.getElementById('edit-' + id);
        const button = document.getElementById('edit-button-' + id);

        if (!row) {
            return;
        }

        const akanDibuka = row.hidden;

        document.querySelectorAll('.ms-edit-row').forEach(function (editRow) {
            editRow.hidden = true;
        });

        document.querySelectorAll('[id^="edit-button-"]').forEach(function (editButton) {
            editButton.setAttribute('aria-expanded', 'false');
        });

        row.hidden = !akanDibuka;

        if (button) {
            button.setAttribute(
                'aria-expanded',
                akanDibuka ? 'true' : 'false'
            );
        }

        if (akanDibuka) {
            const input = row.querySelector('input[name="nama_satuan"]');

            if (input) {
                input.focus();
                input.select();
            }
        }
    }
</script>

@endsection
