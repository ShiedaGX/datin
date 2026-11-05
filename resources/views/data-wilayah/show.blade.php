@extends('layouts.app')

@section('title', 'Detail Data Wilayah')
@section('heading', 'Detail Data Wilayah')

@section('content')
@php($isi = $rute ?? $pemda)
@foreach ($pemda->permintaan_terbuka as $pr)
    <div class="alert alert-revisi" role="status">
        <div>
            <strong>INTRAM meminta pengajuan untuk moda {{ $pr->moda->nama_moda }}</strong>
            <p>{{ $pr->catatan }}</p>
            <small>Diminta {{ \App\Support\Tanggal::panjang($pr->created_at) }}. Pengajuan moda lain tidak perlu direvisi.</small>
        </div>
        @if ((int) $pemda->id_user === (int) auth()->user()->id_user && auth()->user()->bisa('pemda.unggah'))
            <a class="btn btn-primary" href="{{ route('input-data.create', ['kab' => $pemda->id_kab_kota]) }}">Ajukan untuk moda {{ $pr->moda->nama_moda }}</a>
        @endif
    </div>
@endforeach

<section class="box">
    <div class="box-head row-between">
        <div>
            <h2>{{ $pemda->kabKota->nama_kab_kota }}</h2>
            <p class="muted">{{ $pemda->kabKota->provinsi->nama_provinsi }} &middot; {{ $pemda->kabKota->wilayah->nama_wilayah }}</p>
        </div>
        <div class="acts">
            <span class="badge badge-{{ \Illuminate\Support\Str::slug($isi->klasifikasi_label) }}">{{ $isi->klasifikasi_label }}</span>
            <span class="badge {{ $pemda->status_kelas }}">{{ $pemda->status_label }}</span>
        </div>
    </div>

    <dl class="detail-grid">
        <div>
            <dt>Keterangan Daerah</dt>
            <dd>{{ $isi->keterangan_pemda ?: '-' }}</dd>
        </div>
        <div>
            <dt>Moda Transportasi</dt>
            <dd>{{ $rute?->moda?->nama_moda ?? '-' }}</dd>
        </div>
        <div class="span-2">
            <dt>Potensi Unggulan Daerah</dt>
            <dd>
                @if (count($isi->potensi_daftar))
                    <ol class="plain-list">
                        @foreach ($isi->potensi_daftar as $butir)
                            <li>{{ $butir }}</li>
                        @endforeach
                    </ol>
                @else
                    -
                @endif
            </dd>
        </div>
        <div class="span-2">
            <dt>Sektor Potensi ({{ $isi->jumlah_sektor }})</dt>
            <dd>
                @forelse ($isi->sektor_terdeteksi as $sektor)
                    <span class="tag">{{ $sektor }}</span>
                @empty
                    -
                @endforelse
            </dd>
        </div>
        <div class="span-2">
            <dt>Kebutuhan Konektivitas Regional @if ($isi->jumlah_tujuan !== null)({{ $isi->jumlah_tujuan }} tujuan)@endif</dt>
            <dd class="prewrap">{{ $isi->kebutuhan_konektivitas ?: '-' }}</dd>
        </div>
        <div>
            <dt>Tanggal Pengajuan</dt>
            <dd>{{ $rute?->tanggal_pengajuan?->format('d-m-Y') ?? '-' }}</dd>
        </div>
        <div>
            <dt>Diinput oleh</dt>
            <dd>{{ $pemda->user->full_name ?? '-' }}</dd>
        </div>
    </dl>
</section>

<section class="box">
    <div class="box-head">
        <h2>Pengajuan per Moda</h2>
        <p class="muted">Setiap moda diajukan dan ditinjau secara terpisah.</p>
    </div>
    <div class="table-box">
        <table class="table table-data">
            <thead>
                <tr><th>Nomor laporan</th><th>Moda</th><th>Status</th><th>Surat CHR</th><th>Surat Rekomendasi Teknis</th></tr>
            </thead>
            <tbody>
                @forelse ($pemda->ruteSubsidi->sortBy('id_rute_subsidi') as $r)
                    @php($st = \App\Support\StatusPengajuan::normalisasi($r->status_pengajuan))
                    <tr>
                        <td data-label="Nomor laporan"><strong>{{ $r->nomor_laporan }}</strong></td>
                        <td data-label="Moda">{{ $r->moda->nama_moda ?? '-' }}</td>
                        <td data-label="Status"><span class="badge {{ \App\Support\StatusPengajuan::kelas($st) }}">{{ $st }}</span></td>
                        <td data-label="Surat CHR">{{ $r->chr_terbaru->status ?? '-' }}</td>
                        <td data-label="Surat Rekomendasi Teknis">
                            @if ($rt = $r->rekomendasi_terbaru)
                                <div class="acts">
                                    <a class="sr-lihat" target="_blank" rel="noopener" href="{{ route('rekomendasi.dokumen', $rt->id_rekomendasi_teknis) }}">Lihat</a>
                                    <a class="icon-act" href="{{ route('rekomendasi.dokumen', [$rt->id_rekomendasi_teknis, 'unduh' => 1]) }}" title="Unduh" aria-label="Unduh Surat Rekomendasi Teknis">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M8 11l4 4 4-4M5 20h14"/></svg>
                                    </a>
                                </div>
                            @else
                                <span class="muted">Belum diunggah</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Belum ada pengajuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="box">
    <div class="box-head"><h2>Dokumen Pengajuan</h2></div>
    @if ($rute && $rute->dokumen->isNotEmpty())
        <div class="table-box">
            <table class="table table-data">
                <thead><tr><th>Jenis Dokumen</th><th>Nama File</th><th class="num">Versi</th><th>Tanggal Upload</th><th></th></tr></thead>
                <tbody>
                    @foreach ($rute->dokumen as $dok)
                        <tr>
                            <td data-label="Jenis Dokumen"><strong>{{ $dok->jenis_dokumen }}</strong></td>
                            <td data-label="Nama File" class="soft">{{ $dok->nama_file }}</td>
                            <td data-label="Versi" class="num">{{ $dok->versi_dokumen }}</td>
                            <td data-label="Tanggal Upload" class="soft">{{ $dok->tanggal_upload?->format('d-m-Y H:i') }}</td>
                            <td class="td-aksi">
                                @if ($bisaUnduh)
                                    <a class="btn btn-soft" href="{{ route('dokumen.unduh', $dok->id_dokumen_pengajuan) }}">Unduh</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="empty-note">Belum ada dokumen pengajuan untuk daerah ini.</p>
    @endif
</section>

@if ($rute && $rute->verifikasi->isNotEmpty())
<section class="box">
    <div class="box-head"><h2>Riwayat Peninjauan</h2></div>
    <ol class="rv-timeline">
        @foreach ($rute->verifikasi->sortBy('id_verifikasi_pengajuan') as $v)
            <li class="done s-{{ \App\Support\StatusPengajuan::grup($v->status_verifikasi) }}">
                <strong>{{ $v->tahap_verifikasi }}: {{ $v->status_verifikasi }}</strong>
                <span>Oleh {{ $v->verifikator->full_name ?? '-' }}</span>
                @if ($v->catatan_verifikasi)<p class="rv-note">{{ $v->catatan_verifikasi }}</p>@endif
                <small>{{ \App\Support\Tanggal::pendek($v->tanggal_verifikasi) }} &bull; {{ \App\Support\Tanggal::jam($v->tanggal_verifikasi) }}</small>
            </li>
        @endforeach
    </ol>
</section>
@endif

<a class="btn btn-outline" href="{{ route('data-wilayah.index') }}">&larr; Kembali ke daftar</a>
@endsection
