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
        <small>Unggah {{ $jumlahWajib }} surat wajib dan, bila diperlukan, {{ $jumlahOpsional }} surat tambahan untuk User INTRAM.</small>
    </div>
@endsection

@section('content')
<nav class="rv-crumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a> <span>/</span> <strong>Surat</strong>
</nav>

<div class="pm-head">
    <div>
        <h2 class="pm-title">Pengajuan Surat</h2>
        <p class="pm-sub">Unggah surat untuk User INTRAM dan pantau status pengajuan Anda.</p>
    </div>
</div>

@if ($bisaUnggah)
<form class="rv-card sr-form" id="formSurat" method="POST" action="{{ route('surat.simpan') }}" enctype="multipart/form-data" data-maks-mb="{{ $maksMb }}">
    @csrf

    @error('berkas')
        <div class="alert alert-error rv-alert" role="alert">{{ $message }}</div>
    @enderror
    @error('berkas.*')
        <div class="alert alert-error rv-alert" role="alert">{{ $message }}</div>
    @enderror

    <header class="rv-top">
        <i class="rv-ico"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6M17 3v4M15 5h4"/></svg></i>
        <div class="rv-top-main">
            <div class="rv-no">
                <h3>Pengajuan baru</h3>
                <span class="sr-st sr-draf">Draf</span>
            </div>
            <p>{{ $draf ? $draf->nomor : 'Nomor pengajuan dibuat otomatis saat draf disimpan' }}</p>
        </div>
        <div class="rv-top-r">
            <small>Ditujukan kepada</small>
            <strong class="sr-to"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg> User INTRAM</strong>
        </div>
    </header>

    <div class="rv-body">
        <div class="rv-main">
            <section>
                <h3 class="rv-h">Dokumen Surat</h3>
                <p class="muted sr-hs">Unggah {{ $jumlahWajib }} surat wajib. Surat tambahan bersifat opsional. Ketentuan: PDF, maks. {{ $maksMb }} MB per berkas.</p>

                <div class="sr-slots">
                    @foreach ($slot as $s)
                        @php($d = $s['dokumen'])
                        <div class="sr-slot" data-slot="{{ $s['kode'] }}" data-wajib="{{ $s['wajib'] ? 1 : 0 }}" @if ($d) data-ada-nama="{{ $d->nama_file }}" data-ada-ukuran="{{ $d->ukuran }}" @endif>
                            <div class="sr-slot-head">
                                <strong>{{ $s['nama'] }}</strong>
                                <span class="sr-tag {{ $s['wajib'] ? 'is-wajib' : 'is-opsional' }}">{{ $s['wajib'] ? 'WAJIB' : 'OPSIONAL' }}</span>
                                <span class="sr-state {{ $d ? 'is-ok' : '' }}" data-state>{{ $d ? 'Terunggah' : 'Belum diunggah' }}</span>
                            </div>

                            <div class="sr-file sr-file-ada" @if (! $d) hidden @endif>
                                <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                                <div class="sr-file-main">
                                    <strong>{{ $d->nama_file ?? '' }}</strong>
                                    <small>{{ $d ? $d->ekstensi . ' · ' . $d->ukuran_teks . ' · Diunggah ' . \App\Support\Tanggal::pendek($d->updated_at) : '' }}</small>
                                </div>
                                <div class="sr-file-act">
                                    <button type="button" class="sr-btn" data-ganti>
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12a8 8 0 0114-5.3L20 8M20 4v4h-4M20 12a8 8 0 01-14 5.3L4 16M4 20v-4h4"/></svg>
                                        Ganti
                                    </button>
                                    @if ($d)
                                        <button type="submit" form="hapus-{{ $d->id_dokumen_surat }}" class="sr-btn sr-btn-del" title="Hapus berkas" aria-label="Hapus {{ $s['nama'] }}">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="sr-file sr-file-baru" hidden>
                                <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                                <div class="sr-file-main">
                                    <strong data-nama></strong>
                                    <small data-meta></small>
                                </div>
                                <div class="sr-file-act">
                                    <button type="button" class="sr-btn" data-ganti>Ganti</button>
                                    <button type="button" class="sr-btn sr-btn-del" data-batal title="Batalkan pilihan" aria-label="Batalkan pilihan berkas">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="sr-drop" @if ($d) hidden @endif>
                                <i class="sr-drop-ico"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg></i>
                                <div class="sr-drop-txt">
                                    <strong>Tarik berkas ke sini atau pilih berkas</strong>
                                    <small>PDF · Maks. {{ $maksMb }} MB{{ $s['wajib'] ? '' : ' · Boleh dikosongkan' }}</small>
                                </div>
                                <button type="button" class="sr-btn sr-btn-pilih" data-pilih>Pilih berkas</button>
                            </div>

                            <input type="file" name="berkas[{{ $s['kode'] }}]" accept="application/pdf,.pdf" hidden>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="sr-catatan">
                <label for="srCatatan" class="rv-h sr-lbl">Catatan pengiriman <small>(opsional)</small></label>
                <div class="sr-ta">
                    <textarea id="srCatatan" name="catatan" rows="3" maxlength="1000" placeholder="Tulis pesan untuk User INTRAM...">{{ old('catatan', $draf->catatan_pengiriman ?? '') }}</textarea>
                    <span class="sr-counter" id="srCounter">0 / 1.000 karakter</span>
                </div>
            </section>
        </div>

        <aside class="rv-side">
            <section class="rv-panel">
                <h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 13l2 2 4-4"/></svg> Ringkasan Pengajuan</h3>
                <dl>
                    <dt>Penerima</dt>
                    <dd>User INTRAM</dd>
                    <dt>Dokumen terlampir</dt>
                    <dd id="srCount">{{ $terlampir }} surat &middot; Total {{ \App\Support\SuratOptions::formatUkuran($totalUkuran) }}</dd>
                </dl>
                <ul class="sr-sum" id="srList">
                    @foreach ($slot as $s)
                        <li data-slot="{{ $s['kode'] }}" class="{{ $s['dokumen'] ? 'ok' : '' }}">
                            <i></i>
                            <div><strong>{{ $s['nama'] }}</strong><small data-ket>{{ $s['dokumen'] ? '' : 'Belum diunggah · ' . ($s['wajib'] ? 'Wajib' : 'Opsional') }}</small></div>
                        </li>
                    @endforeach
                </ul>
                <div class="sr-progress">
                    <strong id="srBarLabel">Surat wajib lengkap</strong>
                    <div class="sr-bar"><i id="srBar"></i></div>
                </div>
                <div class="sr-siap" id="srSiap">
                    <span class="sr-chip-siap" id="srSiapChip">Siap dikirim</span>
                    <p id="srSiapTeks">Surat sudah siap dikirim</p>
                </div>
            </section>
            <p class="sr-note">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
                Pastikan surat dapat dibaca, sesuai jenisnya, dan telah ditandatangani sebelum dikirim.
            </p>
        </aside>
    </div>

    <footer class="rv-decision">
        <div>
            <strong id="srFooterJudul">Siap mengirim surat ke User INTRAM</strong>
            <p class="sr-foot-sub">Status pengajuan dapat dipantau pada riwayat setelah dikirim.</p>
        </div>
        <div class="rv-dec-btns">
            <button type="submit" name="aksi" value="draf" class="btn btn-light">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 4h11l3 3v13H5zM8 4v5h7V4M8 20v-6h8v6"/></svg>
                Simpan draf
            </button>
            <button type="submit" name="aksi" value="kirim" id="btnKirim" class="btn btn-primary">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 3L3 11l7 3 3 7zM10 14l11-11"/></svg>
                Kirim ke User INTRAM
            </button>
        </div>
    </footer>
</form>

@if ($draf)
    @foreach ($draf->dokumen as $dok)
        <form id="hapus-{{ $dok->id_dokumen_surat }}" method="POST" action="{{ route('surat.hapus', $dok->id_dokumen_surat) }}" data-confirm="Hapus berkas {{ $dok->nama_file }} dari draf?">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
@else
    <div class="alert alert-error" role="alert">Akun Anda tidak memiliki hak akses untuk mengunggah surat. Hubungi Super Admin.</div>
@endif

{{-- ===== Riwayat ===== --}}
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
                    <th>Tanggal unggah / kirim</th>
                    <th>Penerima</th>
                    <th>Status pengajuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                    @php($p = $r->pengajuan)
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
                        <td data-label="Tanggal unggah / kirim">
                            <span>{{ \App\Support\Tanggal::pendek($p->dikirim_at ?? $r->updated_at) }}</span>
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
                    <tr><td colspan="5" class="empty">Belum ada surat.@if ($filter['q'] !== '' || $filter['status']) <a class="link-teal" href="{{ route('surat.index') }}">Reset filter</a>@endif</td></tr>
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
            <nav class="pager-num" aria-label="Halaman">
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
