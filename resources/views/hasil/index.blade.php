@extends('layouts.app')

@section('title', 'Hasil Unggahan')
@section('heading', 'Hasil Unggahan')
@section('search_action', route('hasil.index'))
@section('search_placeholder', 'Cari nomor laporan atau nama Pemda')

@section('content')
<nav class="rv-crumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> <strong>Hasil Unggahan</strong>
</nav>

<div class="pm-head">
    <div>
        <h2 class="pm-title">Hasil Unggahan</h2>
        <p class="pm-sub">Lihat unggahan Pemda dan Surat CHR moda untuk setiap laporan, lalu unggah Surat Rekomendasi Teknis setelah Surat CHR disetujui.</p>
    </div>
</div>

<section class="pm-box sr-hist">
    <div class="sr-hist-head">
        <h3>Laporan Pemda <span class="pm-count">{{ $data->total() }} laporan</span></h3>
        <p class="muted">Hanya laporan yang sudah disetujui oleh user moda.</p>
    </div>

    <form class="sr-hist-filter" method="GET" action="{{ route('hasil.index') }}">
        <div class="input-ico sr-search">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input type="text" name="q" value="{{ $filter['q'] }}" placeholder="Cari nomor laporan atau nama Pemda" autocomplete="off" aria-label="Cari laporan">
        </div>
        <label class="sr-sel">
            <select name="moda" data-autosubmit aria-label="Filter moda">
                <option value="">Semua moda</option>
                @foreach ($modaOpsi as $m)
                    <option value="{{ $m->id_moda }}" @selected($filter['moda'] === (int) $m->id_moda)>{{ $m->nama_moda }}</option>
                @endforeach
            </select>
        </label>
        <label class="sr-sel">
            <select name="status" data-autosubmit aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach ($statusOpsi as $kunci => $label)
                    <option value="{{ $kunci }}" @selected($filter['status'] === $kunci)>{{ $label }}</option>
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
                    <th>Moda</th>
                    <th>Surat CHR</th>
                    <th>Surat Rekomendasi Teknis</th>
                    <th class="t-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $l)
                    @php($chr = $l->chr_terbaru)
                    @php([$rtLabel, $rtKelas] = $l->status_rekomendasi)
                    <tr>
                        <td data-label="Nomor laporan"><strong>{{ $l->nomor_laporan }}</strong><small>Diajukan {{ \App\Support\Tanggal::pendek($l->waktu_pengajuan) }}</small></td>
                        <td data-label="Pemda / Wilayah"><strong>{{ $l->kabKotaAsal->nama_lengkap }}</strong><small>{{ $l->kabKotaAsal->wilayah->nama_wilayah }}</small></td>
                        <td data-label="Moda">{{ $l->moda->nama_moda ?? '-' }}</td>
                        <td data-label="Surat CHR">
                            @if ($chr)
                                <span class="sr-st {{ $chr->kelas_status }}">{{ $chr->status }}</span><small class="sr-st-note">{{ $chr->nomor }}</small>
                            @else
                                <span class="sr-st sr-belum">Belum ada CHR</span>
                            @endif
                        </td>
                        <td data-label="Surat Rekomendasi Teknis"><span class="sr-st {{ $rtKelas }}">{{ $rtLabel }}</span></td>
                        <td class="td-aksi t-center">
                            @if ($bisaUnggah && $rtLabel === 'Menunggu Rekomendasi Teknis')
                                <a class="btn-review" href="{{ route('hasil.show', $l->id_rute_subsidi) }}">Upload
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                            @else
                                <a class="btn-detail" href="{{ route('hasil.show', $l->id_rute_subsidi) }}">Buka
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada laporan yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot sr-foot">
        <p class="muted">
            @if ($data->total() > 0)
                Menampilkan {{ $data->firstItem() }}&ndash;{{ $data->lastItem() }} dari {{ $data->total() }} laporan
            @else
                Tidak ada laporan
            @endif
        </p>
        @if ($data->lastPage() > 1)
            <nav class="pager-num" aria-label="Halaman">
                @if ($data->onFirstPage())
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&lsaquo;</span>
                @else
                    <a class="pg pg-nav" href="{{ $data->previousPageUrl() }}" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif
                @foreach ($halaman as $h)
                    @if ($h === '...')
                        <span class="gap">&hellip;</span>
                    @elseif ($h === $data->currentPage())
                        <span class="pg active" aria-current="page">{{ $h }}</span>
                    @else
                        <a class="pg" href="{{ $data->url($h) }}" aria-label="Halaman {{ $h }}">{{ $h }}</a>
                    @endif
                @endforeach
                @if ($data->hasMorePages())
                    <a class="pg pg-nav" href="{{ $data->nextPageUrl() }}" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&rsaquo;</span>
                @endif
            </nav>
        @endif
    </div>
</section>
@endsection
