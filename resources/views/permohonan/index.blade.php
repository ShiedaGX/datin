@extends('layouts.app')

@section('title', 'Permohonan')
@section('heading', 'Laporan')
@section('subheading', 'Daftar Permohonan')
@section('search_action', route('permohonan.index'))
@section('search_placeholder', 'Cari laporan atau instansi')

@section('content')
<div class="pm-head">
    <div>
        <p class="pm-eyebrow">Laporan Pemda</p>
        <h2 class="pm-title">Daftar Permohonan</h2>
        <p class="pm-sub">Pantau pengajuan laporan pemerintah daerah dan tinjau laporan yang memerlukan persetujuan.</p>
    </div>
    <a class="btn btn-light" href="{{ route('permohonan.export', array_filter(['q' => $filter['q'], 'provinsi' => $filter['provinsi'], 'status' => $filter['status'], 'periode' => $filter['periode'], 'urut' => $filter['urut']])) }}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M8 11l4 4 4-4M5 20h14"/></svg>
        Ekspor daftar
    </a>
</div>

@if (! $punyaCakupan)
    <div class="alert alert-error" role="alert">Akun Anda belum memiliki cakupan moda, sehingga belum ada laporan yang dapat ditampilkan. Hubungi Super Admin.</div>
@endif

{{-- ===== Ringkasan ===== --}}
<section class="pm-kpis" aria-label="Ringkasan permohonan">
    <article class="pm-kpi">
        <div><span>Total Permohonan</span>
            <i class="pm-ico ico-gray"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h2"/></svg></i></div>
        <strong>{{ number_format($kpi['total'], 0, ',', '.') }}</strong>
        <small>{{ $labelPeriode }}</small>
    </article>
    <article class="pm-kpi">
        <div><span>Disetujui</span>
            <i class="pm-ico ico-green"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg></i></div>
        <strong>{{ number_format($kpi['disetujui'], 0, ',', '.') }}</strong>
        <small class="t-green">Selesai ditinjau</small>
    </article>
    <article class="pm-kpi">
        <div><span>Perlu revisi</span>
            <i class="pm-ico ico-mint"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12a8 8 0 108-8H7"/><path d="M7 1v6H1" transform="translate(3 -1)"/></svg></i></div>
        <strong>{{ number_format($kpi['revisi'], 0, ',', '.') }}</strong>
        <small class="t-green">Menunggu perbaikan Pemda</small>
    </article>
    <article class="pm-kpi">
        <div><span>Ditolak</span>
            <i class="pm-ico ico-red"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg></i></div>
        <strong>{{ number_format($kpi['ditolak'], 0, ',', '.') }}</strong>
        <small class="t-red">Tidak memenuhi persyaratan</small>
    </article>
</section>

{{-- ===== Filter ===== --}}
<form class="pm-box pm-filter" id="formFilter" method="GET" action="{{ route('permohonan.index') }}">
    <div class="pm-filter-grid">
        <div class="field">
            <label for="f-q">Cari laporan</label>
            <div class="input-ico">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input type="text" id="f-q" name="q" value="{{ $filter['q'] }}" placeholder="Nomor laporan atau nama Pemda" autocomplete="off">
            </div>
        </div>
        <div class="field">
            <label for="f-provinsi">Wilayah</label>
            <select id="f-provinsi" name="provinsi">
                <option value="">Semua provinsi</option>
                @foreach ($provinsi as $p)
                    <option value="{{ $p->id_provinsi }}" @selected($filter['provinsi'] === (int) $p->id_provinsi)>{{ $p->nama_provinsi }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="f-status">Status laporan</label>
            <select id="f-status" name="status">
                <option value="">Semua status</option>
                @foreach ($statusOpsi as $kunci => $label)
                    <option value="{{ $kunci }}" @selected($filter['status'] === $kunci)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="f-periode">Periode pengajuan</label>
            <select id="f-periode" name="periode">
                <option value="">Semua periode</option>
                @foreach ($periodeOpsi as $ym => $label)
                    <option value="{{ $ym }}" @selected($filter['periode'] === $ym)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="pm-filter-foot">
        <div class="pm-chips">
            <span class="muted">Filter aktif:</span>
            @forelse ($chips as $c)
                <span class="pm-chip">{{ $c['label'] }}<a href="{{ $c['url'] }}" aria-label="Hapus filter {{ $c['label'] }}">&times;</a></span>
            @empty
                <span class="muted">Tidak ada</span>
            @endforelse
        </div>
        <div class="pm-filter-act">
            <a class="pm-reset" href="{{ route('permohonan.index') }}">Reset filter</a>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M7 12h10M10 17h4"/></svg>
                Terapkan filter
            </button>
        </div>
    </div>
</form>

{{-- ===== Daftar ===== --}}
<section class="pm-box pm-list">
    <div class="pm-list-head">
        <h3>Laporan pemerintah daerah <span class="pm-count">{{ number_format($data->total(), 0, ',', '.') }} laporan</span></h3>
        <label class="pm-sort">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M4 6h10M4 12h7M4 18h4M17 6v12M14 15l3 3 3-3"/></svg>
            <select name="urut" form="formFilter" data-autosubmit aria-label="Urutkan laporan">
                <option value="terbaru" @selected($filter['urut'] === 'terbaru')>Pengajuan terbaru</option>
                <option value="terlama" @selected($filter['urut'] === 'terlama')>Pengajuan terlama</option>
            </select>
        </label>
    </div>

    <div class="pm-table-wrap">
        <table class="table pm-table">
            <thead>
                <tr>
                    <th>Nomor laporan</th>
                    <th>Pemda / Wilayah</th>
                    <th>Tanggal pengajuan</th>
                    <th>Status</th>
                    <th class="t-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $r)
                    <tr>
                        <td data-label="Nomor laporan"><strong>{{ $r->nomor_laporan }}</strong><small>Tahun pengajuan {{ $r->waktu_pengajuan->format('Y') }}</small></td>
                        <td data-label="Pemda / Wilayah"><strong>{{ $r->kabKotaAsal->nama_lengkap }}</strong><small>{{ $r->kabKotaAsal->wilayah->nama_wilayah }}</small></td>
                        <td data-label="Tanggal pengajuan"><span>{{ $r->tanggal_lengkap }}</span><small>{{ $r->jam_pengajuan }}</small></td>
                        <td data-label="Status"><span class="pm-status {{ $r->kelas_status_moda }}">{{ $r->label_status_moda }}</span></td>
                        <td class="td-aksi t-center">
                            @if ($r->sedang_menunggu)
                                <a class="btn-review" href="{{ route('permohonan.show', $r->id_rute_subsidi) }}">Review
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                            @else
                                <a class="btn-detail" href="{{ route('permohonan.show', $r->id_rute_subsidi) }}">Lihat Detail
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada laporan yang cocok.@if ($chips) <a class="link-teal" href="{{ route('permohonan.index') }}">Reset filter</a>@endif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pm-foot">
        <div class="pm-foot-l">
            <label>Baris per halaman
                <select name="per" form="formFilter" data-autosubmit aria-label="Baris per halaman">
                    @foreach ($perOpsi as $n)
                        <option value="{{ $n }}" @selected($filter['per'] === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </label>
            <span class="muted">
                @if ($data->total() > 0)
                    Menampilkan {{ $data->firstItem() }}&ndash;{{ $data->lastItem() }} dari {{ number_format($data->total(), 0, ',', '.') }} laporan
                @else
                    Tidak ada laporan
                @endif
            </span>
        </div>

        @if ($data->lastPage() > 1)
            <nav class="pm-pager" aria-label="Halaman">
                @if ($data->onFirstPage())
                    <span class="pm-nav is-disabled">&lsaquo; Sebelumnya</span>
                @else
                    <a class="pm-nav" href="{{ $data->previousPageUrl() }}">&lsaquo; Sebelumnya</a>
                @endif

                @foreach ($halaman as $h)
                    @if ($h === '...')
                        <span class="gap">&hellip;</span>
                    @elseif ($h === $data->currentPage())
                        <span class="pm-pg active" aria-current="page">{{ $h }}</span>
                    @else
                        <a class="pm-pg" href="{{ $data->url($h) }}" aria-label="Halaman {{ $h }}">{{ $h }}</a>
                    @endif
                @endforeach

                @if ($data->hasMorePages())
                    <a class="pm-nav" href="{{ $data->nextPageUrl() }}">Berikutnya &rsaquo;</a>
                @else
                    <span class="pm-nav is-disabled">Berikutnya &rsaquo;</span>
                @endif
            </nav>
        @endif
    </div>
</section>
@endsection
