@extends('layouts.app')

@section('title', 'Surat')
@section('heading', 'Surat')
@section('subheading', 'Pengajuan & riwayat')
@section('search_action', route('surat.index'))
@section('search_placeholder', 'Cari surat atau nomor pengajuan')

@section('sidecard')
    <div class="side-card side-info">
        <span class="side-card-top side-info-top">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
            <span>Pengiriman surat</span>
        </span>
        <small>Buka permohonan Pemda yang sudah Anda setujui, lalu unggah Surat CHR untuk User INTRAM dari halaman detailnya.</small>
    </div>
@endsection

@section('content')
<nav class="rv-crumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> <strong>Surat</strong>
</nav>

<div class="pm-head">
    <div>
        <h2 class="pm-title">Pengajuan Surat</h2>
        <p class="pm-sub">Pilih permohonan Pemda yang sudah disetujui untuk mengunggah Surat CHR ke User INTRAM, lalu pantau status pengajuan Anda.</p>
    </div>
</div>

{{-- ===== Permohonan yang sudah disetujui ===== --}}
<section class="pm-box sr-hist sr-laporan">
    <div class="sr-hist-head">
        <h3>Permohonan Disetujui <span class="pm-count">{{ $laporan->total() }} laporan</span></h3>
        <p class="muted">Surat CHR dikirim per laporan agar User INTRAM tahu surat ini berasal dari permohonan Pemda yang mana.</p>
    </div>

    <form class="sr-hist-filter" method="GET" action="{{ route('surat.index') }}">
        <div class="input-ico sr-search">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="text" name="lq" value="{{ $lf['q'] }}" placeholder="Cari nomor laporan atau nama Pemda" autocomplete="off" aria-label="Cari permohonan disetujui">
        </div>
        <label class="sr-sel">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4"/></svg>
            <select name="lstatus" data-autosubmit aria-label="Filter status surat CHR">
                <option value="">Semua status CHR</option>
                @foreach ($laporanOpsi as $kunci => $label)
                    <option value="{{ $kunci }}" @selected($lf['status'] === $kunci)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="pm-table-wrap">
        <table class="table pm-table sr-table">
            <thead>
                <tr>
                    <th>Nomor laporan</th>
                    <th>Pemda / Wilayah</th>
                    <th>Disetujui pada</th>
                    <th>Status Surat CHR</th>
                    <th class="t-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporan as $l)
                    @php($c = $l->chr_terbaru)
                    <tr>
                        <td data-label="Nomor laporan"><strong>{{ $l->nomor_laporan }}</strong><small>Tahun pengajuan {{ $l->waktu_pengajuan->format('Y') }}</small></td>
                        <td data-label="Pemda / Wilayah"><strong>{{ $l->kabKotaAsal->nama_lengkap }}</strong><small>{{ $l->kabKotaAsal->wilayah->nama_wilayah }}</small></td>
                        <td data-label="Disetujui pada"><span>{{ \App\Support\Tanggal::panjang($l->tanggal_disetujui) }}</span><small>{{ \App\Support\Tanggal::jam($l->tanggal_disetujui) }}</small></td>
                        <td data-label="Status Surat CHR">
                            <span class="sr-st {{ $c ? $c->kelas_status : 'sr-belum' }}">{{ $c ? $c->status : 'Belum ada CHR' }}</span>
                            @if ($c)<small class="sr-st-note">{{ $c->nomor }}</small>@endif
                        </td>
                        <td class="td-aksi t-center">
                            @if ($l->perlu_chr)
                                <a class="btn-review" href="{{ route('surat.laporan', $l->id_rute_subsidi) }}">Upload CHR
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                            @else
                                <a class="btn-detail" href="{{ route('surat.laporan', $l->id_rute_subsidi) }}">Lihat Detail
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada permohonan yang disetujui.@if ($lf['q'] !== '' || $lf['status']) <a class="link-teal" href="{{ route('surat.index') }}">Reset filter</a>@endif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot sr-foot">
        <p class="muted">
            @if ($laporan->total() > 0)
                Menampilkan {{ $laporan->firstItem() }}&ndash;{{ $laporan->lastItem() }} dari {{ $laporan->total() }} laporan
            @else
                Tidak ada laporan
            @endif
        </p>
        @if ($laporan->lastPage() > 1)
            <nav class="pager-num" aria-label="Halaman permohonan">
                @if ($laporan->onFirstPage())
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&lsaquo;</span>
                @else
                    <a class="pg pg-nav" href="{{ $laporan->previousPageUrl() }}" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif
                @foreach ($halamanLaporan as $h)
                    @if ($h === '...')
                        <span class="gap">&hellip;</span>
                    @elseif ($h === $laporan->currentPage())
                        <span class="pg active" aria-current="page">{{ $h }}</span>
                    @else
                        <a class="pg" href="{{ $laporan->url($h) }}" aria-label="Halaman {{ $h }}">{{ $h }}</a>
                    @endif
                @endforeach
                @if ($laporan->hasMorePages())
                    <a class="pg pg-nav" href="{{ $laporan->nextPageUrl() }}" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&rsaquo;</span>
                @endif
            </nav>
        @endif
    </div>
</section>

{{-- ===== Riwayat surat ===== --}}
<section class="pm-box sr-hist">
    <div class="sr-hist-head">
        <h3>Riwayat Surat <span class="pm-count">{{ $riwayat->total() }} surat</span></h3>
        <p class="muted">Lihat berkas yang sudah diunggah dan status pengajuannya.</p>
    </div>

    <form class="sr-hist-filter" method="GET" action="{{ route('surat.index') }}">
        <div class="input-ico sr-search">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="text" name="q" value="{{ $filter['q'] }}" placeholder="Cari nama berkas, jenis, atau nomor pengajuan" autocomplete="off" aria-label="Cari surat">
        </div>
        <label class="sr-sel">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4"/></svg>
            <select name="status" data-autosubmit aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach ($statusOpsi as $kunci => $label)
                    <option value="{{ $kunci }}" @selected($filter['status'] === $kunci)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <span class="muted sr-urut">Terbaru lebih dulu</span>
    </form>

    <div class="pm-table-wrap">
        <table class="table pm-table sr-table">
            <thead>
                <tr>
                    <th>Nama / Jenis surat</th>
                    <th>Laporan Pemda</th>
                    <th>Tanggal unggah / kirim</th>
                    <th>Penerima</th>
                    <th>Status pengajuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                    @php($p = $r->pengajuan)
                    @php($lp = $p->rute)
                    <tr>
                        <td data-label="Nama / Jenis surat">
                            <div class="sr-name">
                                <i class="rv-file"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                                <div>
                                    <strong>{{ $r->nama_jenis }}</strong>
                                    <span>{{ $r->nama_file }}</span>
                                    <small>{{ $p->nomor }} &middot; {{ $r->ekstensi }} &middot; {{ $r->ukuran_teks }}</small>
                                </div>
                            </div>
                        </td>
                        <td data-label="Laporan Pemda">
                            @if ($lp)
                                <a class="sr-lapkait" href="{{ route('surat.laporan', $lp->id_rute_subsidi) }}"><strong>{{ $lp->nomor_laporan }}</strong><small>{{ $lp->kabKotaAsal->nama_lengkap }}</small></a>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                        <td data-label="Tanggal unggah / kirim">
                            <span>{{ \App\Support\Tanggal::panjang($p->dikirim_at ?? $r->updated_at) }}</span>
                            <small>{{ $p->sudah_dikirim ? 'Dikirim' : 'Diunggah' }} &middot; {{ \App\Support\Tanggal::jam($p->dikirim_at ?? $r->updated_at) }}</small>
                        </td>
                        <td data-label="Penerima">
                            <span>User INTRAM</span>
                            <small>{{ $p->sudah_dikirim ? 'Penerima pengajuan' : 'Belum dikirim' }}</small>
                        </td>
                        <td data-label="Status pengajuan">
                            <span class="sr-st {{ $p->kelas_status }}">{{ $p->status }}</span>
                            @if ($p->catatan_review)<small class="sr-st-note">{{ $p->catatan_review }}</small>@endif
                        </td>
                        <td class="td-aksi">
                            <div class="acts">
                                <a class="sr-lihat" target="_blank" rel="noopener" href="{{ route('surat.dokumen', $r->id_dokumen_surat) }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg> Lihat</a>
                                <a class="icon-act" href="{{ route('surat.dokumen', [$r->id_dokumen_surat, 'unduh' => 1]) }}" title="Unduh" aria-label="Unduh {{ $r->nama_file }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M8 11l4 4 4-4M5 20h14"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada surat.@if ($filter['q'] !== '' || $filter['status']) <a class="link-teal" href="{{ route('surat.index') }}">Reset filter</a>@endif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot sr-foot">
        <p class="muted">
            @if ($riwayat->total() > 0)
                Menampilkan {{ $riwayat->firstItem() }}&ndash;{{ $riwayat->lastItem() }} dari {{ $riwayat->total() }} surat
            @else
                Tidak ada surat
            @endif
        </p>
        @if ($riwayat->lastPage() > 1)
            <nav class="pager-num" aria-label="Halaman riwayat">
                @if ($riwayat->onFirstPage())
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&lsaquo;</span>
                @else
                    <a class="pg pg-nav" href="{{ $riwayat->previousPageUrl() }}" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif
                @foreach ($halaman as $h)
                    @if ($h === '...')
                        <span class="gap">&hellip;</span>
                    @elseif ($h === $riwayat->currentPage())
                        <span class="pg active" aria-current="page">{{ $h }}</span>
                    @else
                        <a class="pg" href="{{ $riwayat->url($h) }}" aria-label="Halaman {{ $h }}">{{ $h }}</a>
                    @endif
                @endforeach
                @if ($riwayat->hasMorePages())
                    <a class="pg pg-nav" href="{{ $riwayat->nextPageUrl() }}" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&rsaquo;</span>
                @endif
            </nav>
        @endif
    </div>
</section>
@endsection
