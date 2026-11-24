@extends('layouts.app')

@section('title', 'Approval Surat CHR')
@section('heading', 'Approval')
@section('subheading', 'Surat CHR Moda')
@section('search_action', route('approval.index'))
@section('search_placeholder', 'Cari nomor pengajuan atau pengirim')

@section('content')
<nav class="rv-crumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> <a href="{{ route('approval.index') }}">Approval</a> <span>/</span> <strong>Surat CHR Moda</strong>

<nav class="rv-crumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('approval.index') }}">Approval</a>
    <span>/</span>
    <strong>Surat CHR Moda</strong>
</nav>

<div class="pm-head">
    <div>
        <h2 class="pm-title">Approval Surat CHR Moda</h2>
        <p class="pm-sub">Periksa Surat Catatan Hasil Review dari user moda dan tetapkan keputusan sebagai User INTRAM.</p>
    </div>
</div>

{{-- ===== Ringkasan ===== --}}
<section class="pm-kpis" aria-label="Ringkasan pengajuan">
    <article class="pm-kpi">
        <div><span>Pengajuan masuk</span>
            <i class="pm-ico ico-mint"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13l2-8h12l2 8M4 13v6h16v-6M4 13h5l1 2h4l1-2h5"/></svg></i></div>
        <strong>{{ $kpi['masuk'] }}</strong>
        <small>Semua pengajuan Surat CHR</small>
    </article>
    <article class="pm-kpi">
        <div><span>Perlu ditinjau</span>
            <i class="pm-ico ico-mint"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 13l2 2 4-4"/></svg></i></div>
        <strong>{{ $kpi['terkirim'] + $kpi['review'] }}</strong>
        <small>{{ $kpi['terkirim'] }} Terkirim &middot; {{ $kpi['review'] }} Dalam Review</small>
    </article>
    <article class="pm-kpi">
        <div><span>Disetujui</span>
            <i class="pm-ico ico-green"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg></i></div>
        <strong>{{ $kpi['disetujui'] }}</strong>
        <small>Keputusan akhir telah tercatat</small>
    </article>
    <article class="pm-kpi">
        <div><span>Perlu Perbaikan</span>
            <i class="pm-ico ico-mint"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12a8 8 0 108-8H7"/><path d="M7 1v6H1" transform="translate(3 -1)"/></svg></i></div>
        <strong>{{ $kpi['perbaikan'] }}</strong>
        <small>Menunggu perbaikan pengirim{{ $kpi['ditolak'] ? ' · ' . $kpi['ditolak'] . ' Ditolak' : '' }}</small>
    </article>
</section>

{{-- ===== Daftar pengajuan ===== --}}
<section class="pm-box ap-list">
    <div class="ap-list-head">
        <div>
            <h3>Daftar Pengajuan Surat CHR</h3>
            <p class="muted">Surat Catatan Hasil Review adalah dokumen utama; lampiran bersifat opsional.</p>
        <p class="pm-sub">
            Periksa Surat Catatan Hasil Review dari user moda
            dan tetapkan keputusan sebagai User INTRAM.
        </p>
    </div>
</div>

{{-- Ringkasan --}}
<section class="pm-kpis" aria-label="Ringkasan pengajuan">

    <article class="pm-kpi">
        <div>
            <span>Pengajuan masuk</span>
            <i class="pm-ico ico-mint">
                <svg viewBox="0 0 24 24" width="20" height="20"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M4 13l2-8h12l2 8M4 13v6h16v-6M4 13h5l1 2h4l1-2h5"/>
                </svg>
            </i>
        </div>
        <strong>{{ $kpi['masuk'] }}</strong>
        <small>Semua pengajuan Surat CHR</small>
    </article>

    <article class="pm-kpi">
        <div>
            <span>Perlu ditinjau</span>
            <i class="pm-ico ico-mint">
                <svg viewBox="0 0 24 24" width="20" height="20"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <rect x="5" y="4" width="14" height="17" rx="2"/>
                    <path d="M9 4V3h6v1M9 13l2 2 4-4"/>
                </svg>
            </i>
        </div>
        <strong>{{ $kpi['terkirim'] + $kpi['review'] }}</strong>
        <small>
            {{ $kpi['terkirim'] }} Terkirim
            &middot;
            {{ $kpi['review'] }} Dalam Review
        </small>
    </article>

    <article class="pm-kpi">
        <div>
            <span>Disetujui</span>
            <i class="pm-ico ico-green">
                <svg viewBox="0 0 24 24" width="20" height="20"
                     fill="none" stroke="currentColor"
                     stroke-width="1.9"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M8 12l3 3 5-6"/>
                </svg>
            </i>
        </div>
        <strong>{{ $kpi['disetujui'] }}</strong>
        <small>Keputusan akhir telah tercatat</small>
    </article>

    <article class="pm-kpi">
        <div>
            <span>Perlu Perbaikan</span>
            <i class="pm-ico ico-mint">
                <svg viewBox="0 0 24 24" width="20" height="20"
                     fill="none" stroke="currentColor"
                     stroke-width="1.9"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M4 12a8 8 0 108-8H7"/>
                    <path d="M7 1v6H1" transform="translate(3 -1)"/>
                </svg>
            </i>
        </div>
        <strong>{{ $kpi['perbaikan'] }}</strong>
        <small>
            Menunggu perbaikan pengirim
            {{ $kpi['ditolak'] ? ' · ' . $kpi['ditolak'] . ' Ditolak' : '' }}
        </small>
    </article>

</section>

{{-- Daftar pengajuan --}}
<section class="pm-box ap-list">

    <div class="ap-list-head">
        <div>
            <h3>Daftar Pengajuan Surat CHR</h3>
            <p class="muted">
                Surat Catatan Hasil Review adalah dokumen utama;
                lampiran bersifat opsional.
            </p>
        </div>
        <span class="muted">{{ $data->total() }} pengajuan</span>
    </div>

    <form class="ap-filter" method="GET" action="{{ route('approval.index') }}">
        <div class="field ap-f-q">
            <label class="sr-only" for="ap-q">Cari</label>
            <div class="input-ico">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input type="text" id="ap-q" name="q" value="{{ $filter['q'] }}" placeholder="Cari nomor pengajuan atau pengirim" autocomplete="off">
            </div>
        </div>
        <div class="field">
            <label for="ap-moda">Moda</label>
            <select id="ap-moda" name="moda" data-autosubmit>
                <option value="">Semua moda</option>
                @foreach ($modaOpsi as $m)
                    <option value="{{ $m->id_moda }}" @selected($filter['moda'] === (int) $m->id_moda)>{{ $m->nama_moda }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="ap-status">Status</label>
            <select id="ap-status" name="status" data-autosubmit>
                <option value="">Semua status</option>
                @foreach ($statusOpsi as $kunci => $label)
                    <option value="{{ $kunci }}" @selected($filter['status'] === $kunci)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="ap-periode">Periode</label>
            <select id="ap-periode" name="periode" data-autosubmit>
                <option value="">Semua periode</option>
                @foreach ($periodeOpsi as $ym => $label)
                    <option value="{{ $ym }}" @selected($filter['periode'] === $ym)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <noscript><button type="submit" class="btn btn-outline">Terapkan</button></noscript>
    </form>
    <p class="ap-note muted">
        Moda tersedia: {{ $modaOpsi->pluck('nama_moda')->implode(', ') }} &middot; Urutan: terbaru lebih dulu
        @foreach ($chips as $c)<span class="pm-chip">{{ $c['label'] }}<a href="{{ $c['url'] }}" aria-label="Hapus filter {{ $c['label'] }}">&times;</a></span>@endforeach
    <form class="ap-filter"
          method="GET"
          action="{{ route('approval.index') }}">

        <div class="field ap-f-q">
            <label class="sr-only" for="ap-q">Cari</label>

            <div class="input-ico">
                <svg viewBox="0 0 24 24" width="18" height="18"
                     fill="none" stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M20 20l-3.5-3.5"/>
                </svg>

                <input type="text"
                       id="ap-q"
                       name="q"
                       value="{{ $filter['q'] }}"
                       placeholder="Cari nomor pengajuan atau pengirim"
                       autocomplete="off">
            </div>
        </div>

        <div class="field">
            <label for="ap-moda">Moda</label>

            <select id="ap-moda" name="moda" data-autosubmit>
                <option value="">Semua moda</option>

                @foreach ($modaOpsi as $m)
                    <option value="{{ $m->id_moda }}"
                        @selected($filter['moda'] === (int) $m->id_moda)>
                        {{ $m->nama_moda }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="ap-status">Status</label>

            <select id="ap-status" name="status" data-autosubmit>
                <option value="">Semua status</option>

                @foreach ($statusOpsi as $kunci => $label)
                    <option value="{{ $kunci }}"
                        @selected($filter['status'] === $kunci)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="ap-periode">Periode</label>

            <select id="ap-periode" name="periode" data-autosubmit>
                <option value="">Semua periode</option>

                @foreach ($periodeOpsi as $ym => $label)
                    <option value="{{ $ym }}"
                        @selected($filter['periode'] === $ym)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <noscript>
            <button type="submit" class="btn btn-outline">
                Terapkan
            </button>
        </noscript>

    </form>

    <p class="ap-note muted">
        Moda tersedia:
        {{ $modaOpsi->pluck('nama_moda')->implode(', ') }}
        &middot; Urutan: terbaru lebih dulu

        @foreach ($chips as $c)
            <span class="pm-chip">
                {{ $c['label'] }}
                <a href="{{ $c['url'] }}"
                   aria-label="Hapus filter {{ $c['label'] }}">
                    &times;
                </a>
            </span>
        @endforeach
    </p>

    <div class="pm-table-wrap">
        <table class="table pm-table ap-table">

            <thead>
                <tr>
                    <th>Nomor pengajuan</th>
                    <th>Pengirim</th>
                    <th>Moda</th>
                    <th>Tanggal kirim</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $p)
                    @php($pilih = $dipilih && (int) $dipilih->id_pengajuan_surat === (int) $p->id_pengajuan_surat)
                    <tr class="{{ $pilih ? 'is-pilih' : '' }}">
                        <td data-label="Nomor pengajuan">
                            <strong>{{ $p->nomor }}</strong>
                            @if ($pilih)<small class="t-teal">Pengajuan terpilih</small>@else<small>Surat Catatan Hasil Review</small>@endif
                        </td>
                        <td data-label="Pengirim">{{ $p->user->full_name ?? '-' }}</td>
                        <td data-label="Moda">{{ $p->moda->nama_moda ?? '-' }}</td>
                        <td data-label="Tanggal kirim">{{ \App\Support\Tanggal::pendek($p->dikirim_at) }}</td>
                        <td data-label="Status"><span class="sr-st {{ $p->kelas_status }}">{{ $p->status }}</span></td>
                        <td class="td-aksi">
                            @if ($p->status === \App\Support\SuratOptions::TERKIRIM)
                                <form method="POST" action="{{ route('approval.mulai', $p->id_pengajuan_surat) }}">
                                    @csrf
                                    <button type="submit" class="btn-tinjau is-utama">Tinjau</button>
                                </form>
                            @elseif ($p->status === \App\Support\SuratOptions::DALAM_REVIEW)
                                <a class="btn-tinjau {{ $pilih ? 'is-utama' : '' }}" href="{{ request()->fullUrlWithQuery(['pilih' => $p->id_pengajuan_surat]) }}#detail">Tinjau</a>
                            @else
                                <a class="btn-tinjau" href="{{ request()->fullUrlWithQuery(['pilih' => $p->id_pengajuan_surat]) }}#detail">Detail</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada pengajuan Surat CHR.@if ($chips) <a class="link-teal" href="{{ route('approval.index') }}">Reset filter</a>@endif</td></tr>
                @endforelse

            <tbody>

                @forelse ($data as $p)

                    @php
                        $pilih = $dipilih
                            && (int) $dipilih->id_pengajuan_surat
                                === (int) $p->id_pengajuan_surat;
                    @endphp

                    <tr class="{{ $pilih ? 'is-pilih' : '' }}">

                        <td data-label="Nomor pengajuan">
                            <strong>{{ $p->nomor }}</strong>

                            @if ($pilih)
                                <small class="t-teal">
                                    Pengajuan terpilih
                                </small>
                            @else
                                <small>
                                    Surat Catatan Hasil Review
                                </small>
                            @endif
                        </td>

                        <td data-label="Pengirim">
                            {{ $p->user->full_name ?? '-' }}
                        </td>

                        <td data-label="Moda">
                            {{ $p->moda->nama_moda ?? '-' }}
                        </td>

                        <td data-label="Tanggal kirim">
                            {{ \App\Support\Tanggal::pendek($p->dikirim_at) }}
                        </td>

                        <td data-label="Status">
                            <span class="sr-st {{ $p->kelas_status }}">
                                {{ $p->status }}
                            </span>
                        </td>

                        <td class="td-aksi">

                            @if ($p->status === \App\Support\SuratOptions::TERKIRIM)

                                <form method="POST"
                                      action="{{ route('approval.mulai', $p->id_pengajuan_surat) }}">
                                    @csrf

                                    <button type="submit"
                                            class="btn-tinjau is-utama">
                                        Tinjau
                                    </button>
                                </form>

                            @elseif ($p->status === \App\Support\SuratOptions::DALAM_REVIEW)

                                <a class="btn-tinjau {{ $pilih ? 'is-utama' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['pilih' => $p->id_pengajuan_surat]) }}#detail">
                                    Tinjau
                                </a>

                            @else

                                <a class="btn-tinjau"
                                   href="{{ request()->fullUrlWithQuery(['pilih' => $p->id_pengajuan_surat]) }}#detail">
                                    Detail
                                </a>

                            @endif

                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="empty">
                            Belum ada pengajuan Surat CHR.

                            @if ($chips)
                                <a class="link-teal"
                                   href="{{ route('approval.index') }}">
                                    Reset filter
                                </a>
                            @endif
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    <div class="table-foot ap-foot">
        <p class="muted">
            @if ($data->total() > 0)
                Menampilkan {{ $data->firstItem() }}&ndash;{{ $data->lastItem() }} dari {{ $data->total() }} pengajuan

        <p class="muted">
            @if ($data->total() > 0)
                Menampilkan
                {{ $data->firstItem() }}&ndash;{{ $data->lastItem() }}
                dari {{ $data->total() }} pengajuan
            @else
                Tidak ada pengajuan
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

{{-- ===== Detail pengajuan terpilih ===== --}}
@if ($dipilih)
<div id="detail"></div>
@php($mulai = $dipilih->peninjauan->firstWhere('aksi', \App\Support\SuratOptions::AKSI_MULAI))
<form class="rv-card" id="formKeputusan" method="POST" action="{{ route('approval.keputusan', $dipilih->id_pengajuan_surat) }}">
    @csrf

    @error('keputusan')
        <div class="alert alert-error rv-alert" role="alert">{{ $message }}</div>
    @enderror

    <header class="rv-top">
        <i class="rv-ico"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 13l2 2 4-4"/></svg></i>
        <div class="rv-top-main">
            <div class="rv-no">
                <h3>{{ $dipilih->nomor }}</h3>
                <span class="sr-st {{ $dipilih->kelas_status }}">{{ $dipilih->status }}</span>
            </div>
            <p>Surat Catatan Hasil Review &middot; Moda {{ $dipilih->moda->nama_moda ?? '-' }}</p>
        </div>
        <div class="rv-top-r">
            <small>Review oleh User INTRAM</small>
            <strong>{{ $mulai ? \App\Support\Tanggal::pendek($mulai->created_at) . ' · ' . \App\Support\Tanggal::jam($mulai->created_at) : 'Belum dimulai' }}</strong>
        </div>
    </header>

    <div class="rv-body">
        <div class="rv-main">
            {{-- 1. Dokumen Surat CHR --}}
            <section>
                <h3 class="rv-h"><span class="step-no">1</span> Dokumen Surat CHR</h3>
                <p class="muted rv-hs">Periksa dokumen utama dan lampiran pendukung yang disertakan pengirim.</p>
                <ul class="rv-docs">
                    @forelse ($dipilih->dokumen as $d)
                        <li class="rv-doc">
                            <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                            <div class="rv-doc-main">
                                <strong>{{ $d->nama_jenis }}</strong>
                                <span>{{ $d->nama_file }}</span>
                                <small>@if ($d->nomor_surat)No. {{ $d->nomor_surat }} &bull; @endif{{ $d->ekstensi }} &bull; {{ $d->ukuran_teks }} &bull; Diunggah {{ \App\Support\Tanggal::pendek($d->updated_at) }}, {{ \App\Support\Tanggal::jam($d->updated_at) }}</small>
                            </div>
                            <div class="rv-doc-act rv-doc-act-teks">
                                <a class="sr-lihat" target="_blank" rel="noopener" href="{{ route('surat.dokumen', $d->id_dokumen_surat) }}">Lihat</a>
                                <a class="sr-lihat" href="{{ route('surat.dokumen', [$d->id_dokumen_surat, 'unduh' => 1]) }}">Unduh</a>
                            </div>
                        </li>
                    @empty
                        <li class="rv-doc"><span class="muted">Belum ada berkas.</span></li>
                    @endforelse
                </ul>
                <h4 class="ap-sub">Catatan pengiriman</h4>
                <div class="ap-catatan">{{ $dipilih->catatan_pengiriman ?: 'Tidak ada catatan dari pengirim.' }}</div>
            </section>

            @if ($rute ?? null)
                {{-- 2-3. Laporan Pemda yang dirujuk --}}
                @include('permohonan._data_pemda', ['no' => 2])

                @include('permohonan._dokumen', ['no' => 3, 'mode' => 'intram', 'judul' => 'Dokumen Laporan Pemda', 'deskripsi' => 'Surat yang diunggah Pemda pada laporan yang dirujuk Surat CHR ini.'])
            @endif

            @if ($bisaPutuskan)
                {{-- 4. Checklist --}}
                <section>
                    <h3 class="rv-h"><span class="step-no">4</span> Checklist Pemeriksaan CHR</h3>
                    <p class="muted rv-hs">Konfirmasikan hasil pemeriksaan sebelum menetapkan keputusan akhir.</p>
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
                </section>

                <section>
                    <h3 class="rv-h"><span class="step-no">5</span> Catatan Peninjau</h3>
                    <p class="muted rv-hs">Catatan wajib diisi untuk keputusan Minta Perbaikan atau Tolak.</p>
                    <textarea id="catatan" name="catatan" rows="4" maxlength="1000" class="ap-ta @error('catatan') is-invalid @enderror" placeholder="Tulis hasil peninjauan Anda...">{{ old('catatan') }}</textarea>
                    @error('catatan')<p class="error">{{ $message }}</p>@enderror
                </section>
            @else
                <section>
                    <h3 class="rv-h"><span class="step-no">4</span> Hasil Peninjauan</h3>
                    @php($akhir = $dipilih->peninjauan->sortByDesc('id_peninjauan_surat')->first())
                    <div class="rv-result {{ $dipilih->status === 'Disetujui' ? 's-disetujui' : 's-ditolak' }}">
                        <strong>{{ $dipilih->status }}</strong>
                        @if ($akhir)<p>Oleh {{ $akhir->user->full_name ?? '-' }} &bull; {{ \App\Support\Tanggal::panjang($akhir->created_at) }}, {{ \App\Support\Tanggal::jam($akhir->created_at) }}</p>@endif
                        @if ($dipilih->catatan_review)<p class="rv-note">{{ $dipilih->catatan_review }}</p>@endif
                    </div>
                </section>
            @endif
        </div>

        <aside class="rv-side">
            <section class="rv-panel">
                <h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg> Ringkasan Pengajuan</h3>
                <dl>
                    <dt>Pengirim</dt><dd>{{ $dipilih->user->full_name ?? '-' }}</dd>
                    <dt>Unit pengirim</dt><dd>{{ $dipilih->user->instansi ?: '-' }}</dd>
                    <dt>Penerima</dt><dd>User INTRAM</dd>
                    <dt>Nomor surat</dt><dd>{{ $dipilih->dokumen->firstWhere('jenis', 'chr')->nomor_surat ?? '-' }}</dd>
                    <dt>Nomor pengajuan</dt><dd>{{ $dipilih->nomor }}</dd>
                    <dt>Tanggal dikirim</dt><dd>{{ \App\Support\Tanggal::panjang($dipilih->dikirim_at) }} &middot; {{ \App\Support\Tanggal::jam($dipilih->dikirim_at) }}</dd>
                    <dt>Moda / Tahun</dt><dd>{{ $dipilih->moda->nama_moda ?? '-' }} / {{ $dipilih->dikirim_at->format('Y') }}</dd>
                    <dt>Berkas</dt><dd>{{ $dipilih->dokumen->count() }} berkas</dd>
                </dl>
            </section>

            @if ($rute ?? null)
            <section class="rv-panel">
                <h3>Rujukan Laporan Pemda</h3>
                <a class="ap-ruj" href="{{ route('hasil.show', $rute->id_rute_subsidi) }}">{{ $rute->nomor_laporan }}</a>
                <p class="ap-ruj-pemda">{{ $pengaju?->instansi ?: 'Pemerintah Daerah ' . $rute->kabKotaAsal->nama_lengkap }}</p>
                <span class="pm-status {{ $rute->kelas_status_moda }}">{{ $rute->label_status_moda }}</span>
                <p class="muted ap-ruj-ket">Laporan Pemda telah disetujui oleh user moda. Keputusan Surat CHR oleh INTRAM {{ $bisaPutuskan ? 'belum ditetapkan' : 'sudah ditetapkan' }}.</p>
                <a class="btn btn-light ap-ruj-btn" href="{{ route('hasil.show', $rute->id_rute_subsidi) }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>
                    Lihat hasil unggahan
                </a>
            </section>
            @else
            <section class="rv-panel">
                <h3>Rujukan Laporan Pemda</h3>
                <p class="muted">Surat ini tidak terhubung ke laporan Pemda (dibuat sebelum fitur penautan laporan).</p>
            </section>
            @endif

            <section class="rv-panel">
                <h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 109-9 9 9 0 00-7 3.4M3 4v4h4M12 7v5l3 2"/></svg> Riwayat Peninjauan</h3>
                <ol class="rv-timeline">
                    @foreach ($riwayatSurat as $e)
                        <li class="done">
                            <strong>{{ $e['judul'] }}</strong>
                            <span>{{ $e['oleh'] }}</span>
                            @if ($e['catatan'])<p class="rv-note">{{ $e['catatan'] }}</p>@endif
                            @if ($e['waktu'])<small>{{ \App\Support\Tanggal::pendek($e['waktu']) }} &bull; {{ \App\Support\Tanggal::jam($e['waktu']) }}</small>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>

    <footer class="rv-decision">
        <div class="rv-dec-info">
            <div>
                <strong>{{ $bisaPutuskan ? 'Tetapkan keputusan Surat CHR' : 'Keputusan Surat CHR: ' . $dipilih->status }}</strong>
                <p>{{ $bisaPutuskan ? 'Pengajuan ini masih ' . $dipilih->status . '. Keputusan akan tercatat pada riwayat.' : 'Keputusan sudah tercatat pada riwayat dan tidak dapat diubah.' }}</p>
            </div>
        </div>
        <div class="rv-dec-btns">
            @if ($bisaPutuskan)
                <button type="submit" name="keputusan" value="tolak" class="btn btn-danger-soft" data-wajib-catatan>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg> Tolak
                </button>
                <button type="submit" name="keputusan" value="perbaikan" class="btn btn-outline" data-wajib-catatan>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12a8 8 0 108-8H6"/><path d="M9 1L6 4l3 3"/></svg> Minta Perbaikan
                </button>
                <button type="submit" name="keputusan" value="setuju" id="btnSetuju" class="btn btn-primary" disabled>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg> Setujui
                </button>
            @elseif ($dipilih->status === \App\Support\SuratOptions::DISETUJUI && auth()->user()->bisa('rekomendasi_teknis.unggah') && ($rute ?? null))
                <a class="btn btn-primary" href="{{ route('hasil.show', $rute->id_rute_subsidi) }}">Unggah Surat Rekomendasi Teknis</a>
            @endif
        </div>
    </footer>
</form>

{{-- ===== Minta revisi ke Pemda (moda tambahan) ===== --}}
@if ($rute ?? null)
<section class="rv-card ap-revisi">
    <div class="ap-revisi-head">
        <i class="rv-ico"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12a8 8 0 108-8H6"/><path d="M9 1L6 4l3 3"/></svg></i>
        <div>
            <h3>Minta Revisi ke Pemda</h3>
            <p class="muted">Gunakan bila rute/tujuan Pemda memerlukan lebih dari satu moda. Pemda diminta mengajukan lagi untuk moda tambahan; pengajuan moda {{ $dipilih->moda->nama_moda ?? 'ini' }} dan Surat CHR-nya <strong>tidak berubah</strong>. Permintaan tampil pada status Pemda.</p>
        </div>
    </div>

    @if ($permintaan->isNotEmpty())
        <ul class="ap-minta">
            @foreach ($permintaan as $pr)
                <li>
                    <strong>Moda {{ $pr->moda->nama_moda }}</strong>
                    <span class="sr-st {{ $pr->terbuka ? 'sr-dalam-review' : 'sr-disetujui' }}">{{ $pr->terbuka ? 'Menunggu Pemda' : 'Dipenuhi' }}</span>
                    <small>{{ \App\Support\Tanggal::pendek($pr->created_at) }}@if ($pr->ruteBaru) &middot; {{ $pr->ruteBaru->nomor_laporan }}@endif &middot; {{ $pr->catatan }}</small>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($modaTambahan->isNotEmpty())
        <form method="POST" action="{{ route('approval.revisi', $dipilih->id_pengajuan_surat) }}" class="ap-revisi-form">
            @csrf
            <fieldset class="field">
                <legend>Moda tambahan yang diminta</legend>
                <div class="chips">
                    @foreach ($modaTambahan as $m)
                        <label class="chip {{ $m['diminta'] ? 'is-off' : '' }}">
                            <input type="checkbox" name="moda[]" value="{{ $m['id'] }}" @disabled($m['diminta']) @checked(in_array($m['id'], old('moda', [])))>
                            <span>{{ $m['nama'] }}{{ $m['diminta'] ? ' (sudah diminta)' : '' }}</span>
                        </label>
                    @endforeach
                </div>
                @error('moda', 'revisi')<p class="error">{{ $message }}</p>@enderror
            </fieldset>
            <div class="field">
                <label for="catatan-revisi">Catatan untuk Pemda</label>
                <textarea id="catatan-revisi" name="catatan" rows="3" maxlength="1000" class="ap-ta @error('catatan', 'revisi') is-invalid @enderror" placeholder="Jelaskan rute/tujuan yang memerlukan moda tambahan...">{{ old('catatan') }}</textarea>
                @error('catatan', 'revisi')<p class="error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-outline" data-konfirmasi="Kirim permintaan revisi ke Pemda? Permintaan akan tampil pada status Pemda.">Kirim permintaan revisi</button>
        </form>
    @else
        <p class="muted">Semua moda sudah diajukan oleh Pemda ini.</p>
    @endif
</section>
@endif
@endif

        @if ($data->lastPage() > 1)

            <nav class="pager-num" aria-label="Halaman">

                @if ($data->onFirstPage())
                    <span class="pg pg-nav is-disabled"
                          aria-hidden="true">
                        &lsaquo;
                    </span>
                @else
                    <a class="pg pg-nav"
                       href="{{ $data->previousPageUrl() }}"
                       aria-label="Halaman sebelumnya">
                        &lsaquo;
                    </a>
                @endif

                @foreach ($halaman as $h)

                    @if ($h === '...')
                        <span class="gap">&hellip;</span>

                    @elseif ($h === $data->currentPage())
                        <span class="pg active"
                              aria-current="page">
                            {{ $h }}
                        </span>

                    @else
                        <a class="pg"
                           href="{{ $data->url($h) }}"
                           aria-label="Halaman {{ $h }}">
                            {{ $h }}
                        </a>
                    @endif

                @endforeach

                @if ($data->hasMorePages())
                    <a class="pg pg-nav"
                       href="{{ $data->nextPageUrl() }}"
                       aria-label="Halaman berikutnya">
                        &rsaquo;
                    </a>
                @else
                    <span class="pg pg-nav is-disabled"
                          aria-hidden="true">
                        &rsaquo;
                    </span>
                @endif

            </nav>

        @endif

    </div>

</section>

{{-- Detail pengajuan --}}
@if ($dipilih)

<div id="detail"></div>

@php
    $mulai = $dipilih->peninjauan->firstWhere(
        'aksi',
        \App\Support\SuratOptions::AKSI_MULAI
    );
@endphp

<form class="rv-card"
      id="formKeputusan"
      method="POST"
      action="{{ route('approval.keputusan', $dipilih->id_pengajuan_surat) }}">

    @csrf

    @error('keputusan')
        <div class="alert alert-error rv-alert" role="alert">
            {{ $message }}
        </div>
    @enderror

    <header class="rv-top">

        <i class="rv-ico">
            <svg viewBox="0 0 24 24" width="26" height="26"
                 fill="none" stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 aria-hidden="true">
                <rect x="5" y="4" width="14" height="17" rx="2"/>
                <path d="M9 4V3h6v1M9 13l2 2 4-4"/>
            </svg>
        </i>

        <div class="rv-top-main">

            <div class="rv-no">
                <h3>{{ $dipilih->nomor }}</h3>

                <span class="sr-st {{ $dipilih->kelas_status }}">
                    {{ $dipilih->status }}
                </span>
            </div>

            <p>
                Surat Catatan Hasil Review
                &middot;
                Moda {{ $dipilih->moda->nama_moda ?? '-' }}
            </p>

        </div>

        <div class="rv-top-r">
            <small>Review oleh User INTRAM</small>

            <strong>
                {{ $mulai
                    ? \App\Support\Tanggal::pendek($mulai->created_at)
                        . ' · '
                        . \App\Support\Tanggal::jam($mulai->created_at)
                    : 'Belum dimulai' }}
            </strong>
        </div>

    </header>

    <div class="rv-body">

        <div class="rv-main">

            {{-- 1. Dokumen Surat CHR --}}
            <section>

                <h3 class="rv-h">
                    <span class="step-no">1</span>
                    Dokumen Surat CHR
                </h3>

                <p class="muted rv-hs">
                    Periksa dokumen utama dan lampiran pendukung
                    yang disertakan pengirim.
                </p>

                <ul class="rv-docs">

                    @forelse ($dipilih->dokumen as $d)

                        <li class="rv-doc">

                            <i class="rv-file">
                                <svg viewBox="0 0 24 24"
                                     width="22" height="22"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     stroke-linecap="round"
                                     stroke-linejoin="round"
                                     aria-hidden="true">
                                    <path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/>
                                    <path d="M14 3v5h5M9 13h6M9 17h6"/>
                                </svg>
                            </i>

                            <div class="rv-doc-main">

                                <strong>{{ $d->nama_jenis }}</strong>

                                <span>{{ $d->nama_file }}</span>

                                <small>
                                    @if ($d->nomor_surat)
                                        No. {{ $d->nomor_surat }}
                                        &bull;
                                    @endif

                                    {{ $d->ekstensi }}
                                    &bull;
                                    {{ $d->ukuran_teks }}
                                    &bull;
                                    Diunggah
                                    {{ \App\Support\Tanggal::pendek($d->updated_at) }},
                                    {{ \App\Support\Tanggal::jam($d->updated_at) }}
                                </small>

                            </div>

                            <div class="rv-doc-act rv-doc-act-teks">

                                <a class="sr-lihat"
                                   target="_blank"
                                   rel="noopener"
                                   href="{{ route('surat.dokumen', $d->id_dokumen_surat) }}">
                                    Lihat
                                </a>

                                <a class="sr-lihat"
                                   href="{{ route('surat.dokumen', [$d->id_dokumen_surat, 'unduh' => 1]) }}">
                                    Unduh
                                </a>

                            </div>

                        </li>

                    @empty

                        <li class="rv-doc">
                            <span class="muted">
                                Belum ada berkas.
                            </span>
                        </li>

                    @endforelse

                </ul>

                <h4 class="ap-sub">Catatan pengiriman</h4>

                <div class="ap-catatan">
                    {{ $dipilih->catatan_pengiriman
                        ?: 'Tidak ada catatan dari pengirim.' }}
                </div>

            </section>

            {{-- 2-3. Data dan dokumen Pemda --}}
            @if ($rute ?? null)

                @include('permohonan._data_pemda', [
                    'no' => 2
                ])

                @include('permohonan._dokumen', [
                    'no' => 3,
                    'mode' => 'intram',
                    'judul' => 'Dokumen Laporan Pemda',
                    'deskripsi' => 'Surat yang diunggah Pemda pada laporan yang dirujuk Surat CHR ini.'
                ])

            @endif

            @if ($bisaPutuskan)

                {{-- 4. Checklist --}}
                <section>

                    <h3 class="rv-h">
                        <span class="step-no">4</span>
                        Checklist Pemeriksaan CHR
                    </h3>

                    <p class="muted rv-hs">
                        Konfirmasikan hasil pemeriksaan sebelum
                        menetapkan keputusan akhir.
                    </p>

                    <div class="rv-checks">

                        @foreach ($checklist as $kunci => [$judul, $uraian])

                            <label class="rv-check">

                                <input type="checkbox"
                                       name="periksa[]"
                                       value="{{ $kunci }}"
                                       @checked(in_array($kunci, (array) old('periksa', [])))>

                                <span class="cb"></span>

                                <span class="txt">
                                    <strong>{{ $judul }}</strong>
                                    <small>{{ $uraian }}</small>
                                </span>

                            </label>

                        @endforeach

                    </div>

                    <p class="rv-count"
                       id="cekHitung"
                       aria-live="polite">
                        0 dari {{ count($checklist) }}
                        pemeriksaan telah dikonfirmasi
                    </p>

                    @error('periksa')
                        <p class="error">{{ $message }}</p>
                    @enderror

                </section>

                {{-- 5. Catatan --}}
                <section>

                    <h3 class="rv-h">
                        <span class="step-no">5</span>
                        Catatan Peninjau
                    </h3>

                    <p class="muted rv-hs">
                        Catatan wajib diisi untuk keputusan
                        Minta Perbaikan atau Tolak.
                    </p>

                    <textarea id="catatan"
                              name="catatan"
                              rows="4"
                              maxlength="1000"
                              class="ap-ta @error('catatan') is-invalid @enderror"
                              placeholder="Tulis hasil peninjauan Anda...">{{ $errors->revisi->any() ? '' : old('catatan') }}</textarea>

                    @error('catatan')
                        <p class="error">{{ $message }}</p>
                    @enderror

                </section>

            @else

                {{-- Hasil keputusan --}}
                <section>

                    <h3 class="rv-h">
                        <span class="step-no">4</span>
                        Hasil Peninjauan
                    </h3>

                    @php
                        $akhir = $dipilih->peninjauan
                            ->whereIn('aksi', [
                                \App\Support\SuratOptions::AKSI_SETUJU,
                                \App\Support\SuratOptions::AKSI_PERBAIKI,
                                \App\Support\SuratOptions::AKSI_TOLAK,
                            ])
                            ->sortByDesc('id_peninjauan_surat')
                            ->first();
                    @endphp

                    <div class="rv-result {{ $dipilih->status === \App\Support\SuratOptions::DISETUJUI ? 's-disetujui' : 's-ditolak' }}">

                        <strong>{{ $dipilih->status }}</strong>

                        @if ($akhir)
                            <p>
                                Oleh {{ $akhir->user->full_name ?? '-' }}
                                &bull;
                                {{ \App\Support\Tanggal::panjang($akhir->created_at) }},
                                {{ \App\Support\Tanggal::jam($akhir->created_at) }}
                            </p>
                        @endif

                        @if ($dipilih->catatan_review)
                            <p class="rv-note">
                                {{ $dipilih->catatan_review }}
                            </p>
                        @endif

                    </div>

                </section>

            @endif

        </div>

        {{-- Sidebar detail --}}
        <aside class="rv-side">

            <section class="rv-panel">

                <h3>
                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/>
                        <path d="M14 3v5h5M9 13h6M9 17h6"/>
                    </svg>
                    Ringkasan Pengajuan
                </h3>

                <dl>

                    <dt>Pengirim</dt>
                    <dd>{{ $dipilih->user->full_name ?? '-' }}</dd>

                    <dt>Unit pengirim</dt>
                    <dd>{{ $dipilih->user?->instansi ?: '-' }}</dd>

                    <dt>Penerima</dt>
                    <dd>User INTRAM</dd>

                    <dt>Nomor surat</dt>
                    <dd>
                        {{ $dipilih->dokumen->firstWhere('jenis', 'chr')->nomor_surat ?? '-' }}
                    </dd>

                    <dt>Nomor pengajuan</dt>
                    <dd>{{ $dipilih->nomor }}</dd>

                    <dt>Tanggal dikirim</dt>
                    <dd>
                        @if ($dipilih->dikirim_at)
                            {{ \App\Support\Tanggal::panjang($dipilih->dikirim_at) }}
                            &middot;
                            {{ \App\Support\Tanggal::jam($dipilih->dikirim_at) }}
                        @else
                            -
                        @endif
                    </dd>

                    <dt>Moda / Tahun</dt>
                    <dd>
                        {{ $dipilih->moda->nama_moda ?? '-' }}
                        /
                        {{ $dipilih->dikirim_at?->format('Y') ?? '-' }}
                    </dd>

                    <dt>Berkas</dt>
                    <dd>{{ $dipilih->dokumen->count() }} berkas</dd>

                </dl>

            </section>

            @if ($rute ?? null)

                <section class="rv-panel">

                    <h3>Rujukan Laporan Pemda</h3>

                    <a class="ap-ruj"
                       href="{{ route('hasil.show', $rute->id_rute_subsidi) }}">
                        {{ $rute->nomor_laporan }}
                    </a>

                    <p class="ap-ruj-pemda">
                        {{ $pengaju?->instansi
                            ?: 'Pemerintah Daerah '
                                . $rute->kabKotaAsal->nama_lengkap }}
                    </p>

                    <span class="pm-status {{ $rute->kelas_status_moda }}">
                        {{ $rute->label_status_moda }}
                    </span>

                    <p class="muted ap-ruj-ket">
                        Laporan Pemda telah disetujui oleh user moda.
                        Keputusan Surat CHR oleh INTRAM
                        {{ $bisaPutuskan
                            ? 'belum ditetapkan'
                            : 'sudah ditetapkan' }}.
                    </p>

                    <a class="btn btn-light ap-ruj-btn"
                       href="{{ route('hasil.show', $rute->id_rute_subsidi) }}">

                        <svg viewBox="0 0 24 24" width="18" height="18"
                             fill="none" stroke="currentColor"
                             stroke-width="1.9"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M7 17L17 7M8 7h9v9"/>
                        </svg>

                        Lihat hasil unggahan

                    </a>

                </section>

            @else

                <section class="rv-panel">

                    <h3>Rujukan Laporan Pemda</h3>

                    <p class="muted">
                        Surat ini tidak terhubung ke laporan Pemda
                        (dibuat sebelum fitur penautan laporan).
                    </p>

                </section>

            @endif

            <section class="rv-panel">

                <h3>
                    <svg viewBox="0 0 24 24" width="20" height="20"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M3 12a9 9 0 109-9 9 9 0 00-7 3.4M3 4v4h4M12 7v5l3 2"/>
                    </svg>

                    Riwayat Peninjauan
                </h3>

                <ol class="rv-timeline">

                    @foreach ($riwayatSurat as $e)

                        <li class="done">

                            <strong>{{ $e['judul'] }}</strong>

                            <span>{{ $e['oleh'] }}</span>

                            @if ($e['catatan'])
                                <p class="rv-note">
                                    {{ $e['catatan'] }}
                                </p>
                            @endif

                            @if ($e['waktu'])
                                <small>
                                    {{ \App\Support\Tanggal::pendek($e['waktu']) }}
                                    &bull;
                                    {{ \App\Support\Tanggal::jam($e['waktu']) }}
                                </small>
                            @endif

                        </li>

                    @endforeach

                </ol>

            </section>

        </aside>

    </div>

    {{-- Tombol keputusan --}}
    <footer class="rv-decision">

        <div class="rv-dec-info">

            <div>

                <strong>
                    {{ $bisaPutuskan
                        ? 'Tetapkan keputusan Surat CHR'
                        : 'Keputusan Surat CHR: ' . $dipilih->status }}
                </strong>

                <p>
                    {{ $bisaPutuskan
                        ? 'Pengajuan ini masih ' . $dipilih->status . '. Keputusan akan tercatat pada riwayat.'
                        : 'Keputusan sudah tercatat pada riwayat dan tidak dapat diubah.' }}
                </p>

            </div>

        </div>

        <div class="rv-dec-btns">

            @if ($bisaPutuskan)

                <button type="submit"
                        name="keputusan"
                        value="tolak"
                        class="btn btn-danger-soft"
                        data-wajib-catatan>

                    <svg viewBox="0 0 24 24" width="18" height="18"
                         fill="none" stroke="currentColor"
                         stroke-width="2.2"
                         stroke-linecap="round"
                         aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>

                    Tolak

                </button>

                <button type="submit"
                        name="keputusan"
                        value="perbaikan"
                        class="btn btn-outline"
                        data-wajib-catatan>

                    <svg viewBox="0 0 24 24" width="18" height="18"
                         fill="none" stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M4 12a8 8 0 108-8H6"/>
                        <path d="M9 1L6 4l3 3"/>
                    </svg>

                    Minta Perbaikan

                </button>

                <button type="submit"
                        name="keputusan"
                        value="setuju"
                        id="btnSetuju"
                        class="btn btn-primary"
                        disabled>

                    <svg viewBox="0 0 24 24" width="18" height="18"
                         fill="none" stroke="currentColor"
                         stroke-width="2.4"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M5 12l5 5 9-10"/>
                    </svg>

                    Setujui

                </button>

            @elseif (
                $dipilih->status === \App\Support\SuratOptions::DISETUJUI
                && auth()->user()->bisa('rekomendasi_teknis.unggah')
                && ($rute ?? null)
            )

                <a class="btn btn-primary"
                   href="{{ route('hasil.show', $rute->id_rute_subsidi) }}">
                    Unggah Surat Rekomendasi Teknis
                </a>

            @endif

        </div>

    </footer>

</form>

{{-- Permintaan revisi Pemda --}}
@if ($rute ?? null)

<section class="rv-card ap-revisi">

    <div class="ap-revisi-head">

        <i class="rv-ico">

            <svg viewBox="0 0 24 24" width="24" height="24"
                 fill="none" stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 aria-hidden="true">
                <path d="M4 12a8 8 0 108-8H6"/>
                <path d="M9 1L6 4l3 3"/>
            </svg>

        </i>

        <div>

            <h3>Minta Revisi ke Pemda</h3>

            <p class="muted">
                Gunakan bila rute/tujuan Pemda memerlukan
                lebih dari satu moda.

                Pemda diminta mengajukan lagi untuk moda tambahan;
                pengajuan moda
                {{ $dipilih->moda->nama_moda ?? 'ini' }}
                dan Surat CHR-nya
                <strong>tidak berubah</strong>.

                Permintaan tampil pada status Pemda.
            </p>

        </div>

    </div>

    @if ($permintaan->isNotEmpty())

        <ul class="ap-minta">

            @foreach ($permintaan as $pr)

                <li>

                    <strong>
                        Moda {{ $pr->moda->nama_moda ?? '-' }}
                    </strong>

                    <span class="sr-st {{ $pr->terbuka ? 'sr-dalam-review' : 'sr-disetujui' }}">
                        {{ $pr->terbuka
                            ? 'Menunggu Pemda'
                            : 'Dipenuhi' }}
                    </span>

                    <small>

                        {{ \App\Support\Tanggal::pendek($pr->created_at) }}

                        @if ($pr->ruteBaru)
                            &middot;
                            {{ $pr->ruteBaru->nomor_laporan }}
                        @endif

                        &middot;

                        {{ $pr->catatan }}

                    </small>

                </li>

            @endforeach

        </ul>

    @endif

    @if ($modaTambahan->isNotEmpty())

        <form method="POST"
              action="{{ route('approval.revisi', $dipilih->id_pengajuan_surat) }}"
              class="ap-revisi-form">

            @csrf

            <fieldset class="field">

                <legend>Moda tambahan yang diminta</legend>

                <div class="chips">

                    @foreach ($modaTambahan as $m)

                        <label class="chip {{ $m['diminta'] ? 'is-off' : '' }}">

                            <input type="checkbox"
                                   name="moda[]"
                                   value="{{ $m['id'] }}"
                                   @disabled($m['diminta'])
                                   @checked(!$m['diminta'] && in_array($m['id'], (array) old('moda', [])))>

                            <span>
                                {{ $m['nama'] }}
                                {{ $m['diminta'] ? ' (sudah diminta)' : '' }}
                            </span>

                        </label>

                    @endforeach

                </div>

                @error('moda', 'revisi')
                    <p class="error">{{ $message }}</p>
                @enderror

                @error('moda.*', 'revisi')
                    <p class="error">{{ $message }}</p>
                @enderror

            </fieldset>

            <div class="field">

                <label for="catatan-revisi">
                    Catatan untuk Pemda
                </label>

                <textarea id="catatan-revisi"
                          name="catatan"
                          rows="3"
                          maxlength="1000"
                          required
                          class="ap-ta @error('catatan', 'revisi') is-invalid @enderror"
                          placeholder="Jelaskan rute/tujuan yang memerlukan moda tambahan...">{{ $errors->revisi->any() ? old('catatan') : '' }}</textarea>

                @error('catatan', 'revisi')
                    <p class="error">{{ $message }}</p>
                @enderror

            </div>

            <button type="submit"
                    class="btn btn-outline"
                    data-konfirmasi="Kirim permintaan revisi ke Pemda? Permintaan akan tampil pada status Pemda."
                    @disabled($modaTambahan->every(fn ($m) => $m['diminta']))>
                Kirim permintaan revisi
            </button>

        </form>

    @else

        <p class="muted">
            Semua moda sudah diajukan oleh Pemda ini.
        </p>

    @endif

</section>

@endif

@endif

@endsection
