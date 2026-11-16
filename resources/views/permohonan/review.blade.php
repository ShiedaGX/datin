@extends('layouts.app')

@section('title', 'Review Laporan')
@section('heading', 'Laporan')
@section('subheading', 'Persetujuan')
@section('search_action', route('permohonan.index'))
@section('search_placeholder', 'Cari laporan atau instansi')

@section('content')
<nav class="rv-crumb" aria-label="Breadcrumb">
    <a href="{{ route('permohonan.index') }}">Daftar Permohonan</a> <span>/</span> <strong>Review</strong>
</nav>

<div class="pm-head">
    <div>
        <h2 class="pm-title">Review Surat Laporan</h2>
        <p class="pm-sub">Periksa kelengkapan, kesesuaian data, dan berikan keputusan atas laporan yang diajukan.</p>
    </div>
    <div class="pm-head-act">
        <a class="btn btn-export" href="{{ route('permohonan.export-detail', $rute->id_rute_subsidi) }}">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6M9 9h2"/></svg>
            Ekspor Excel
        </a>
        <a class="btn btn-light" href="{{ route('permohonan.index') }}">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Kembali ke Daftar
        </a>
    </div>
</div>

<form class="rv-card" id="formKeputusan" method="POST" action="{{ route('permohonan.keputusan', $rute->id_rute_subsidi) }}">
    @csrf

    @error('keputusan')
        <div class="alert alert-error rv-alert" role="alert">{{ $message }}</div>
    @enderror

    <header class="rv-top">
        <i class="rv-ico"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 13l2 2 4-4"/></svg></i>
        <div class="rv-top-main">
            <div class="rv-no">
                <h3>{{ $rute->nomor_laporan }}</h3>
                <span class="pm-status {{ $rute->kelas_status_moda }}">{{ $rute->label_status_moda }}</span>
            </div>
            <p>{{ $pengaju?->instansi ?: 'Pemerintah Daerah ' . $rute->kabKotaAsal->nama_lengkap }}</p>
        </div>
        <div class="rv-top-r">
            <small>Diajukan pada</small>
            <strong>{{ $rute->tanggal_lengkap }}, {{ $rute->jam_pengajuan }}</strong>
        </div>
    </header>

    <div class="rv-body">
        <div class="rv-main">
            @include('permohonan._dokumen', ['no' => 1])

            @include('permohonan._data_pemda', ['no' => 2])

            @if ($rute->sedang_menunggu)
                {{-- 2. Checklist --}}
                <section>
                    <h3 class="rv-h"><span class="step-no">3</span> Checklist Persetujuan</h3>
                    <p class="muted rv-hs">Konfirmasikan hasil peninjauan sebelum menetapkan keputusan akhir.</p>
                    <div class="rv-checks">
                        @foreach ($checklist as $kunci => [$judul, $uraian])
                            <label class="rv-check">
                                <input type="checkbox" name="periksa[]" value="{{ $kunci }}" @checked(in_array($kunci, old('periksa', [])))>
                                <span class="cb"></span>
                                <span class="txt"><strong>{{ $judul }}</strong><small>{{ $uraian }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <p class="rv-count" id="cekHitung" aria-live="polite">0 dari {{ count($checklist) }} pemeriksaan telah dikonfirmasi</p>
                    @error('periksa')<p class="error">{{ $message }}</p>@enderror

                    <div class="field rv-catatan">
                        <label for="catatan">Catatan peninjauan</label>
                        <textarea id="catatan" name="catatan" rows="3" maxlength="1000" class="@error('catatan') is-invalid @enderror" placeholder="Wajib diisi saat Minta Revisi atau Tolak. Catatan ini terlihat oleh instansi pemohon.">{{ old('catatan') }}</textarea>
                        @error('catatan')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </section>
            @else
                <section>
                    <h3 class="rv-h"><span class="step-no">3</span> Hasil Peninjauan</h3>
                    <div class="rv-result {{ $rute->kelas_status_moda }}">
                        <strong>{{ $rute->label_status_moda }}</strong>
                        @if ($terakhir)
                            <p>Oleh {{ $terakhir->verifikator->full_name ?? '-' }} &bull; {{ \App\Support\Tanggal::panjang($terakhir->tanggal_verifikasi) }}, {{ \App\Support\Tanggal::jam($terakhir->tanggal_verifikasi) }}</p>
                            @if ($terakhir->catatan_verifikasi)
                                <p class="rv-note">{{ $terakhir->catatan_verifikasi }}</p>
                            @endif
                        @endif
                    </div>
                </section>
            @endif

            @if ($rute->rekomendasi_terbaru)
                @include('permohonan._rekomendasi', ['no' => 4])
            @endif
        </div>

        <aside class="rv-side">
            @include('permohonan._ringkasan')
        </aside>
    </div>

    @if ($rute->sedang_menunggu)
        <footer class="rv-decision">
            <div class="rv-dec-info">
                <i><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg></i>
                <div>
                    <strong>Tetapkan keputusan laporan</strong>
                    <p>Keputusan akan tercatat permanen pada riwayat persetujuan.</p>
                </div>
            </div>
            <div class="rv-dec-btns">
                <button type="submit" name="keputusan" value="revisi" id="btnRevisi" class="btn btn-outline">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12a8 8 0 108-8H6"/><path d="M9 1L6 4l3 3"/></svg>
                    Minta Revisi
                </button>
                <button type="submit" name="keputusan" value="tolak" id="btnTolak" class="btn btn-danger-soft">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    Tolak
                </button>
                <button type="submit" name="keputusan" value="setuju" id="btnSetuju" class="btn btn-primary" disabled>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg>
                    Setujui Laporan
                </button>
            </div>
        </footer>
    @endif
</form>
@endsection
