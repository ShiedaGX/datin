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
</style>


{{-- =====================================================
     LAYOUT
===================================================== --}}

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
                        <th>Komoditas</th>

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

@endsection
