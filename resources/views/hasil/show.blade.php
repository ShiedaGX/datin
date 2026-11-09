@extends('layouts.app')

@section('title', 'Hasil Unggahan ' . $rute->nomor_laporan)
@section('heading', 'Hasil Unggahan')
@section('subheading', $rute->nomor_laporan)
@section('search_action', route('hasil.index'))
@section('search_placeholder', 'Cari nomor laporan atau nama Pemda')

@section('content')
<nav class="rv-crumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> <a href="{{ route('hasil.index') }}">Hasil Unggahan</a> <span>/</span> <strong>{{ $rute->nomor_laporan }}</strong>
</nav>

<div class="pm-head">
    <div>
        <h2 class="pm-title">Hasil Unggahan {{ $rute->nomor_laporan }}</h2>
        <p class="pm-sub">Unggahan Pemda dan Surat CHR moda untuk laporan ini. Surat Rekomendasi Teknis yang Anda unggah akan tampil pada Pemda dan Moda.</p>
    </div>
    <div class="pm-head-act">
        <a class="btn btn-light" href="{{ route('hasil.index') }}">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Kembali ke Daftar
        </a>
    </div>
</div>

{{-- ===== Unggahan Pemda ===== --}}
<div class="rv-card">
    <header class="rv-top">
        <i class="rv-ico"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 13l2 2 4-4"/></svg></i>
        <div class="rv-top-main">
            <div class="rv-no">
                <h3>{{ $rute->nomor_laporan }}</h3>
                <span class="pm-status {{ $rute->kelas_status_moda }}">{{ $rute->label_status_moda }}</span>
            </div>
            <p>{{ $pengaju?->instansi ?: 'Pemerintah Daerah ' . $rute->kabKotaAsal->nama_lengkap }} &middot; Moda {{ $rute->moda->nama_moda ?? '-' }}</p>
        </div>
        <div class="rv-top-r">
            <small>Diajukan pada</small>
            <strong>{{ $rute->tanggal_lengkap }}, {{ $rute->jam_pengajuan }}</strong>
        </div>
    </header>
    <div class="rv-body">
        <div class="rv-main">
            @include('permohonan._dokumen', ['no' => 1, 'mode' => 'intram', 'judul' => 'Dokumen Laporan Pemda', 'deskripsi' => 'Surat yang diunggah Pemda pada laporan ini.'])

            @include('permohonan._data_pemda', ['no' => 2])
        </div>
        <aside class="rv-side">
            @include('permohonan._ringkasan')
        </aside>
    </div>
</div>

{{-- ===== Surat CHR moda ===== --}}
<section class="rv-card ap-blok">
    <div class="ap-blok-head">
        <h3>Surat CHR dari Moda</h3>
        @if ($chrTerakhir)
            <span class="sr-st {{ $chrTerakhir->kelas_status }}">{{ $chrTerakhir->status }}</span>
        @endif
    </div>
    @if ($chrTerakhir)
        <p class="muted">{{ $chrTerakhir->nomor }} &middot; dikirim oleh {{ $chrTerakhir->user->full_name ?? '-' }}@if ($chrTerakhir->dikirim_at) pada {{ \App\Support\Tanggal::panjang($chrTerakhir->dikirim_at) }} @endif
            @if ($chrTerakhir->status !== \App\Support\SuratOptions::DRAF)&middot; <a class="link-teal" href="{{ route('approval.index', ['pilih' => $chrTerakhir->id_pengajuan_surat]) }}#detail">Buka di Approval</a>@endif</p>
        @if ($chrTerakhir->status !== \App\Support\SuratOptions::DRAF)
            <ul class="rv-docs">
                @foreach ($chrTerakhir->dokumen as $d)
                    <li class="rv-doc">
                        <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                        <div class="rv-doc-main">
                            <strong>{{ $d->nama_jenis }}</strong>
                            <span>{{ $d->nama_file }}</span>
                            <small>@if ($d->nomor_surat)No. {{ $d->nomor_surat }} &bull; @endif{{ $d->ekstensi }} &bull; {{ $d->ukuran_teks }}</small>
                        </div>
                        <div class="rv-doc-act rv-doc-act-teks">
                            <a class="sr-lihat" target="_blank" rel="noopener" href="{{ route('surat.dokumen', $d->id_dokumen_surat) }}">Lihat</a>
                            <a class="sr-lihat" href="{{ route('surat.dokumen', [$d->id_dokumen_surat, 'unduh' => 1]) }}">Unduh</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="rv-kosong">Surat CHR masih berupa draf di pihak moda.</div>
        @endif
    @else
        <div class="rv-kosong">Moda belum mengirim Surat CHR untuk laporan ini.</div>
    @endif
</section>

{{-- ===== Surat Rekomendasi Teknis ===== --}}
<section class="rv-card ap-blok">
    <div class="ap-blok-head">
        <h3>Surat Rekomendasi Teknis</h3>
        @if ($rt)<span class="sr-st sr-disetujui">Terunggah</span>@endif
    </div>

    @if ($rt)
        <ul class="rv-docs">
            <li class="rv-doc">
                <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                <div class="rv-doc-main">
                    <strong>Surat Rekomendasi Teknis</strong>
                    <span>{{ $rt->nama_file }}</span>
                    <small>@if ($rt->nomor_surat)No. {{ $rt->nomor_surat }} &bull; @endif{{ $rt->ekstensi }} &bull; {{ $rt->ukuran_teks }} &bull; Diunggah {{ \App\Support\Tanggal::panjang($rt->updated_at) }}, {{ \App\Support\Tanggal::jam($rt->updated_at) }} oleh {{ $rt->user->full_name ?? 'User INTRAM' }}</small>
                </div>
                <div class="rv-doc-act rv-doc-act-teks">
                    <a class="sr-lihat" target="_blank" rel="noopener" href="{{ route('rekomendasi.dokumen', $rt->id_rekomendasi_teknis) }}">Lihat</a>
                    <a class="sr-lihat" href="{{ route('rekomendasi.dokumen', [$rt->id_rekomendasi_teknis, 'unduh' => 1]) }}">Unduh</a>
                </div>
            </li>
        </ul>
        @if ($rt->catatan)<p class="rv-note">{{ $rt->catatan }}</p>@endif
    @endif

    @if (! $chr)
        <div class="rv-kosong">Surat Rekomendasi Teknis dapat diunggah setelah Surat CHR laporan ini disetujui.</div>
    @elseif ($bisaUnggah)
        <form method="POST" action="{{ route('hasil.simpan', $rute->id_rute_subsidi) }}" enctype="multipart/form-data" class="ap-rt-form">
            @csrf
            <p class="muted">{{ $rt ? 'Unggah berkas baru untuk mengganti Surat Rekomendasi Teknis yang sudah ada.' : 'Surat Rekomendasi Teknis akan tampil pada Pemda pemilik laporan dan pada Moda ' . ($rute->moda->nama_moda ?? '') . '.' }}</p>

            <div class="field">
                <label for="rt_nomor">Nomor surat <span class="wajib">*</span></label>
                <input type="text" id="rt_nomor" name="nomor_surat" value="{{ old('nomor_surat') }}" maxlength="100" required placeholder="Contoh: 123/REK/INTRAM/2026" autocomplete="off" class="@error('nomor_surat') is-invalid @enderror">
                @error('nomor_surat')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="rt_name">Berkas Surat Rekomendasi Teknis <span class="wajib">*</span></label>
                <div class="upload-box @error('berkas') is-invalid @enderror">
                    <input type="text" id="rt_name" class="upload-name" data-for="berkas" readonly placeholder="Pilih berkas PDF (maks. {{ $maksMb }} MB)">
                    <input type="file" id="berkas" name="berkas" accept="application/pdf,.pdf" data-upload data-maks-mb="{{ $maksMb }}" data-pdf hidden>
                    <button type="button" class="btn-upload" data-target="berkas">Upload</button>
                </div>
                @error('berkas')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="rt_catatan">Catatan <small class="muted">(opsional)</small></label>
                <textarea id="rt_catatan" name="catatan" rows="3" maxlength="1000" class="ap-ta" placeholder="Pesan untuk Pemda dan Moda...">{{ old('catatan') }}</textarea>
                @error('catatan')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-primary">{{ $rt ? 'Ganti Surat Rekomendasi Teknis' : 'Unggah Surat Rekomendasi Teknis' }}</button>
            </div>
        </form>
    @elseif (! $rt)
        <div class="rv-kosong">Akun Anda tidak memiliki hak akses untuk mengunggah Surat Rekomendasi Teknis.</div>
    @endif
</section>
@endsection
