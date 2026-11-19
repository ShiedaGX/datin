@extends('layouts.app')

@section('title', 'Master Sektor & Komoditas')
@section('heading', 'Master Sektor & Komoditas')

@section('content')
@php
    $idTerpilih = $terpilih?->id_sektor;
    $tautan = fn ($id = null, array $extra = []) => route('admin.sektor.index', array_filter(['sektor' => $id] + $extra, fn ($v) => $v !== null && $v !== ''));
@endphp

<div class="ms-grid">
    {{-- ===== Kiri: filter sektor ===== --}}
    <aside class="box ms-side" aria-label="Daftar sektor">
        <h2>Sektor</h2>
        <ul class="ms-list">
            <li>
                <a href="{{ $tautan() }}" @class(['aktif' => ! $terpilih]) @if (! $terpilih) aria-current="page" @endif>
                    <span>Semua sektor</span><b>{{ $totalKomoditas }}</b>
                </a>
            </li>
            @foreach ($semuaSektor as $s)
                <li>
                    <a href="{{ $tautan($s->id_sektor) }}" @class(['aktif' => $idTerpilih === $s->id_sektor]) @if ($idTerpilih === $s->id_sektor) aria-current="page" @endif>
                        <span>{{ $s->nama_sektor }}</span><b>{{ $s->komoditas_count }}</b>
                    </a>
                </li>
            @endforeach
        </ul>

        <details class="ms-tambah" @if (old('nama_sektor')) open @endif>
            <summary>+ Tambah sektor</summary>
            <form method="POST" action="{{ route('admin.sektor.store') }}">
                @csrf
                <div class="field">
                    <label for="nama_sektor_baru">Nama sektor</label>
                    <input type="text" id="nama_sektor_baru" name="nama_sektor" maxlength="100" required value="{{ old('nama_sektor') }}" placeholder="mis. Perikanan &amp; Kelautan">
                </div>
                <div class="field">
                    <label for="urutan_baru">Urutan</label>
                    <input type="number" id="urutan_baru" name="urutan" min="0" max="65535" value="{{ $urutanBerikut }}">
                </div>
                <button type="submit" class="btn btn-primary">Tambah Sektor</button>
            </form>
        </details>
    </aside>

    {{-- ===== Kanan: komoditas ===== --}}
    <section class="box ms-main">
        <div class="ms-head">
            <div>
                <h2>{{ $terpilih?->nama_sektor ?? 'Semua sektor' }}</h2>
                <p class="muted">
                    {{ number_format($komoditas->total(), 0, ',', '.') }} komoditas@if ($q !== '') cocok dengan "{{ $q }}"@endif
                    @if ($terpilih && (int) ($pakaiSektor[$terpilih->id_sektor] ?? 0) > 0) &middot; sektor dipakai {{ (int) $pakaiSektor[$terpilih->id_sektor] }} pengajuan @endif
                </p>
            </div>
            <form class="ms-cari" method="GET" action="{{ route('admin.sektor.index') }}" role="search">
                @if ($terpilih)<input type="hidden" name="sektor" value="{{ $terpilih->id_sektor }}">@endif
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari komoditas..." aria-label="Cari komoditas">
                <button type="submit" class="btn btn-soft">Cari</button>
                @if ($q !== '')<a class="reset" href="{{ $tautan($idTerpilih) }}">Reset</a>@endif
            </form>
        </div>

        @if ($terpilih)
            <div class="ms-tools">
                <form class="ms-tambah-k" method="POST" action="{{ route('admin.sektor.komoditas.store', $terpilih->id_sektor) }}">
                    @csrf
                    <input type="text" name="nama_komoditas" maxlength="100" required placeholder="Komoditas baru untuk {{ $terpilih->nama_sektor }}" aria-label="Komoditas baru">
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </form>
                <details class="ms-ubah">
                    <summary>Ubah / hapus sektor</summary>
                    <div class="ms-ubah-isi">
                        <form method="POST" action="{{ route('admin.sektor.update', $terpilih->id_sektor) }}">
                            @csrf
                            @method('PUT')
                            <input type="text" name="nama_sektor" maxlength="100" required value="{{ $terpilih->nama_sektor }}" aria-label="Nama sektor">
                            <input type="number" name="urutan" min="0" max="65535" value="{{ $terpilih->urutan }}" aria-label="Urutan" class="kecil">
                            <button type="submit" class="btn btn-soft">Simpan</button>
                        </form>
                        <form method="POST" action="{{ route('admin.sektor.destroy', $terpilih->id_sektor) }}" data-confirm="Hapus sektor {{ $terpilih->nama_sektor }} beserta {{ $terpilih->komoditas_count }} komoditasnya?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-hapus">Hapus Sektor</button>
                        </form>
                    </div>
                </details>
            </div>
        @else
            <p class="ms-hint">Pilih sektor di kiri untuk menambah komoditas atau mengubah sektor.</p>
        @endif

        <div class="table-box">
            <table class="table table-data ms-table">
                <thead>
                    <tr>
                        <th>Komoditas</th>
                        @unless ($terpilih)<th>Sektor</th>@endunless
                        <th class="num">Dipakai</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($komoditas as $k)
                        <tr>
                            <td data-label="Komoditas">
                                <form class="baris-form" method="POST" action="{{ route('admin.komoditas.update', $k->id_komoditas) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nama_komoditas" maxlength="100" required value="{{ $k->nama_komoditas }}" aria-label="Nama komoditas">
                                    <button type="submit" class="btn btn-soft">Simpan</button>
                                </form>
                            </td>
                            @unless ($terpilih)<td data-label="Sektor" class="soft"><a class="link-teal" href="{{ $tautan($k->id_sektor) }}">{{ $k->sektor->nama_sektor }}</a></td>@endunless
                            <td data-label="Dipakai" class="num">{{ (int) ($pakaiKomoditas[$k->id_komoditas] ?? 0) }}</td>
                            <td class="td-aksi">
                                <form method="POST" action="{{ route('admin.komoditas.destroy', $k->id_komoditas) }}" data-confirm="Hapus komoditas {{ $k->nama_komoditas }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-hapus">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $terpilih ? 3 : 4 }}" class="empty">{{ $q !== '' ? 'Tidak ada komoditas yang cocok.' : 'Belum ada komoditas.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($komoditas->lastPage() > 1)
            <nav class="pager-num ms-pager" aria-label="Halaman">
                @if ($komoditas->onFirstPage())
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&lsaquo;</span>
                @else
                    <a class="pg pg-nav" href="{{ $komoditas->previousPageUrl() }}" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif
                @for ($h = max(1, $komoditas->currentPage() - 2); $h <= min($komoditas->lastPage(), $komoditas->currentPage() + 2); $h++)
                    @if ($h === $komoditas->currentPage())
                        <span class="pg active" aria-current="page">{{ $h }}</span>
                    @else
                        <a class="pg" href="{{ $komoditas->url($h) }}" aria-label="Halaman {{ $h }}">{{ $h }}</a>
                    @endif
                @endfor
                @if ($komoditas->hasMorePages())
                    <a class="pg pg-nav" href="{{ $komoditas->nextPageUrl() }}" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&rsaquo;</span>
                @endif
            </nav>
        @endif
    </section>
</div>
@endsection
