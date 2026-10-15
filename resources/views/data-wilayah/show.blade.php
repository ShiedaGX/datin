@extends('layouts.app')

@section('title', 'Detail Data Wilayah')
@section('heading', 'Detail Data Wilayah')

@section('content')
<section class="box">
    <div class="box-head row-between">
        <div>
            <h2>{{ $pemda->kabKota->nama_kab_kota }}</h2>
            <p class="muted">{{ $pemda->kabKota->provinsi->nama_provinsi }} &middot; {{ $pemda->kabKota->wilayah->nama_wilayah }}</p>
        </div>
        <div class="acts">
            <span class="badge badge-{{ \Illuminate\Support\Str::slug($pemda->klasifikasi_label) }}">{{ $pemda->klasifikasi_label }}</span>
            <span class="badge {{ $pemda->status_kelas }}">{{ $pemda->status_label }}</span>
        </div>
    </div>

    <dl class="detail-grid">
        <div>
            <dt>Keterangan Daerah</dt>
            <dd>{{ $pemda->keterangan_pemda ?: '-' }}</dd>
        </div>
        <div>
            <dt>Moda Transportasi</dt>
            <dd>{{ $rute?->moda?->nama_moda ?? '-' }}</dd>
        </div>
        <div class="span-2">
            <dt>Potensi Unggulan Daerah</dt>
            <dd>
                @if (count($pemda->potensi_daftar))
                    <ol class="plain-list">
                        @foreach ($pemda->potensi_daftar as $butir)
                            <li>{{ $butir }}</li>
                        @endforeach
                    </ol>
                @else
                    -
                @endif
            </dd>
        </div>
        <div class="span-2">
            <dt>Sektor Potensi ({{ $pemda->jumlah_sektor }})</dt>
            <dd>
                @forelse ($pemda->sektor_terdeteksi as $sektor)
                    <span class="tag">{{ $sektor }}</span>
                @empty
                    -
                @endforelse
            </dd>
        </div>
        <div class="span-2">
            <dt>Kebutuhan Konektivitas Regional @if ($pemda->jumlah_tujuan !== null)({{ $pemda->jumlah_tujuan }} tujuan)@endif</dt>
            <dd class="prewrap">{{ $pemda->kebutuhan_konektivitas ?: '-' }}</dd>
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

<a class="btn btn-outline" href="{{ route('data-wilayah.index') }}">&larr; Kembali ke daftar</a>
@endsection
