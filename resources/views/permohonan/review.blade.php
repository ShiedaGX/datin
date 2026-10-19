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
            {{-- 1. Dokumen --}}
            <section>
                <h3 class="rv-h"><span class="step-no">1</span> Dokumen Surat</h3>
                <p class="muted rv-hs">Buka setiap dokumen untuk memastikan berkas dapat dibaca dan sesuai dengan jenis surat.</p>
                <ul class="rv-docs">
                    @forelse ($berkas as $b)
                        <li class="rv-doc">
                            <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                            <div class="rv-doc-main">
                                <strong>{{ $b['model']->jenis_dokumen }}</strong>
                                <span>{{ $b['model']->nama_file }}</span>
                                <small>{{ $b['jenis'] }}@if ($b['ukuran']) &bull; {{ $b['ukuran'] }}@endif &bull; Diunggah {{ \App\Support\Tanggal::pendek($b['model']->tanggal_upload) }}</small>
                            </div>
                            <div class="rv-doc-act">
                                <a class="icon-act" target="_blank" rel="noopener" href="{{ route('permohonan.dokumen', [$rute->id_rute_subsidi, $b['model']->id_dokumen_pengajuan]) }}" title="Lihat dokumen" aria-label="Lihat {{ $b['model']->jenis_dokumen }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a class="icon-act" href="{{ route('permohonan.dokumen', [$rute->id_rute_subsidi, $b['model']->id_dokumen_pengajuan, 'unduh' => 1]) }}" title="Unduh dokumen" aria-label="Unduh {{ $b['model']->jenis_dokumen }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M8 11l4 4 4-4M5 20h14"/></svg>
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="rv-doc"><span class="muted">Belum ada dokumen pada laporan ini.</span></li>
                    @endforelse
                </ul>
            </section>

            {{-- 2. Data yang diinput Pemda --}}
            <section>
                <h3 class="rv-h"><span class="step-no">2</span> Data Pengajuan Pemda</h3>
                <p class="muted rv-hs">Data yang diinput Pemda pada formulir pengajuan. Cocokkan dengan isi dokumen surat.</p>
                <div class="rv-data">
                    <dl class="rv-grid">
                        <div><dt>Provinsi</dt><dd>{{ $rute->kabKotaAsal->provinsi->nama_provinsi }}</dd></div>
                        <div><dt>Kabupaten / Kota</dt><dd>{{ $rute->kabKotaAsal->nama_lengkap }}</dd></div>
                        <div><dt>Wilayah regional</dt><dd>{{ $rute->kabKotaAsal->wilayah->nama_wilayah }}</dd></div>
                        <div><dt>Moda transportasi</dt><dd>{{ $rute->moda->nama_moda ?? '-' }}</dd></div>
                        <div><dt>Klasifikasi wilayah</dt><dd><span class="badge badge-{{ \Illuminate\Support\Str::slug($pemda->klasifikasi_label) }}">{{ $pemda->klasifikasi_label }}</span></dd></div>
                    </dl>
                    <div class="rv-block">
                        <h4>Keterangan daerah</h4>
                        @forelse ($pemda->keterangan_daftar as $butir)<span class="tag">{{ $butir }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                    <div class="rv-block">
                        <h4>Potensi unggulan daerah</h4>
                        @if (count($pemda->potensi_daftar))
                            <ol class="rv-list">
                                @foreach ($pemda->potensi_daftar as $butir)<li>{{ $butir }}</li>@endforeach
                            </ol>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </div>
                    <div class="rv-block">
                        <h4>Sektor potensi ({{ $pemda->jumlah_sektor }})</h4>
                        @forelse ($pemda->sektor_terdeteksi as $sektor)<span class="tag">{{ $sektor }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                    <div class="rv-block">
                        <h4>Kebutuhan konektivitas / rute tujuan @if ($pemda->jumlah_tujuan !== null)({{ $pemda->jumlah_tujuan }} tujuan)@endif</h4>
                        @forelse ($pemda->tujuan_daftar as $tujuan)<span class="tag tag-alt">{{ $tujuan }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                </div>
            </section>

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
        </div>

        <aside class="rv-side">
            <section class="rv-panel">
                <h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg> Ringkasan Pengajuan</h3>
                <dl>
                    <dt>Pemohon / Instansi</dt>
                    <dd>{{ $pengaju?->instansi ?: 'Pemerintah Daerah ' . $rute->kabKotaAsal->nama_lengkap }}</dd>
                    <dt>Wilayah</dt>
                    <dd>{{ $rute->kabKotaAsal->nama_lengkap }}, Provinsi {{ $rute->kabKotaAsal->provinsi->nama_provinsi }}</dd>
                    <dt>Tanggal pengajuan</dt>
                    <dd>{{ $rute->tanggal_lengkap }}</dd>
                    <dt>Nomor laporan</dt>
                    <dd>{{ $rute->nomor_laporan }}</dd>
                    <dt>Moda transportasi</dt>
                    <dd>{{ $rute->moda->nama_moda ?? '-' }}</dd>
                </dl>
                @if ($pengaju)
                    <div class="rv-user">
                        <i><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg></i>
                        <div><strong>{{ $pengaju->full_name }}</strong><small>{{ mb_strtolower($pengaju->role->nama_role ?? 'pemda') }}</small></div>
                        <a href="mailto:{{ $pengaju->email }}" title="Kirim email" aria-label="Kirim email ke {{ $pengaju->full_name }}">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                        </a>
                    </div>
                @endif
            </section>

            <section class="rv-panel">
                <h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 109-9 9 9 0 00-7 3.4M3 4v4h4M12 7v5l3 2"/></svg> Riwayat Persetujuan</h3>
                <ol class="rv-timeline">
                    <li class="done">
                        <strong>Laporan diajukan</strong>
                        <span>Oleh {{ $pengaju->full_name ?? '-' }}@if ($pengaju && $pengaju->instansi), {{ $pengaju->instansi }}@endif</span>
                        <small>{{ \App\Support\Tanggal::pendek($rute->waktu_pengajuan) }} &bull; {{ $rute->jam_pengajuan }}</small>
                    </li>
                    @foreach ($riwayat as $v)
                        <li class="done s-{{ \App\Support\StatusPengajuan::grup($v->status_verifikasi) }}">
                            <strong>{{ $v->tahap_verifikasi }}: {{ $v->status_verifikasi }}</strong>
                            <span>Oleh {{ $v->verifikator->full_name ?? '-' }}</span>
                            @if ($v->catatan_verifikasi)<p class="rv-note">{{ $v->catatan_verifikasi }}</p>@endif
                            <small>{{ \App\Support\Tanggal::pendek($v->tanggal_verifikasi) }} &bull; {{ \App\Support\Tanggal::jam($v->tanggal_verifikasi) }}</small>
                        </li>
                    @endforeach
                    @if ($rute->sedang_menunggu)
                        <li class="now">
                            <strong>Peninjauan laporan <em class="now-badge">Saat ini</em></strong>
                            <span>Sedang ditinjau</span>
                            <small>Menunggu keputusan Moda {{ $rute->moda->nama_moda ?? '' }}</small>
                        </li>
                    @endif
                </ol>
            </section>
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
