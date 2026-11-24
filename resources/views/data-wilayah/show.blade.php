
@extends('layouts.app')

@section('title', 'Detail Data Wilayah')
@section('heading', 'Detail Data Wilayah')

@section('content')
<<<<<<< HEAD
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
=======

@php
    $isi = $rute ?? $pemda;

    $statusUtama = $rute
        ? \App\Support\StatusPengajuan::normalisasi($rute->status_pengajuan)
        : $pemda->status_label;

    /*
     * Format angka Indonesia.
     *
     * Contoh:
     * 10.00   => 10
     * 22.60   => 22,6
     * 1234.00 => 1.234
     * 9.57    => 9,57
     */
    $fmtJumlah = function ($nilai) {
        if ($nilai === null || $nilai === '') {
            return '-';
        }

        return rtrim(
            rtrim(
                number_format((float) $nilai, 2, ',', '.'),
                '0'
            ),
            ','
        );
    };
@endphp

{{-- ========================================================= --}}
{{-- INFORMASI REVISI INTRAM --}}
{{-- ========================================================= --}}

@foreach ($pemda->permintaan_terbuka as $pr)
    <div class="alert alert-revisi" role="status">
        <div>
            <strong>
                INTRAM meminta pengajuan untuk moda
                {{ $pr->moda->nama_moda }}
            </strong>

            <p>{{ $pr->catatan }}</p>

            <small>
                Diminta
                {{ \App\Support\Tanggal::panjang($pr->created_at) }}.
                Pengajuan moda lain tidak perlu direvisi.
            </small>
        </div>

        @if (
            (int) $pemda->id_user === (int) auth()->user()->id_user &&
            auth()->user()->bisa('pemda.unggah')
        )
            <a
                class="btn btn-primary"
                href="{{ route('input-data.create', ['kab' => $pemda->id_kab_kota]) }}"
            >
                Ajukan untuk moda {{ $pr->moda->nama_moda }}
            </a>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        @endif
    </div>
@endforeach

<<<<<<< HEAD
=======

{{-- ========================================================= --}}
{{-- 1. DETAIL DATA WILAYAH --}}
{{-- ========================================================= --}}

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
<section class="box">

    <div class="box-head row-between">
        <div>
            <h2>
                {{ $pemda->kabKota->nama_kab_kota }}
            </h2>

            <p class="muted">
                {{ $pemda->kabKota->provinsi->nama_provinsi }}
                &middot;
                {{ $pemda->kabKota->wilayah->nama_wilayah }}

                @if ($rute)
                    &middot;
                    {{ $rute->nomor_laporan }}
                @endif
            </p>
        </div>

        <div class="acts">
<<<<<<< HEAD
            <span class="badge badge-{{ \Illuminate\Support\Str::slug($isi->klasifikasi_label) }}">{{ $isi->klasifikasi_label }}</span>
            <span class="badge {{ $pemda->status_kelas }}">{{ $pemda->status_label }}</span>
=======
            <span class="badge badge-{{ \Illuminate\Support\Str::slug($isi->klasifikasi_label) }}">
                {{ $isi->klasifikasi_label }}
            </span>

            <span class="badge {{ \App\Support\StatusPengajuan::kelas($statusUtama) }}">
                {{ $statusUtama }}
            </span>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        </div>
    </div>

    <dl class="detail-grid">

        {{-- Keterangan daerah --}}
        <div>
            <dt>Keterangan Daerah</dt>
<<<<<<< HEAD
            <dd>{{ $isi->keterangan_pemda ?: '-' }}</dd>
=======
            <dd>
                {{ $isi->keterangan_pemda ?: '-' }}
            </dd>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        </div>

        {{-- Moda transportasi --}}
        <div>
            <dt>Moda Transportasi</dt>
            <dd>
                {{ $rute?->moda?->nama_moda ?? '-' }}
            </dd>
        </div>

        {{-- Potensi unggulan daerah asal --}}
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

        {{-- Sektor potensi daerah --}}
        <div class="span-2">
<<<<<<< HEAD
            <dt>Sektor Potensi ({{ $isi->jumlah_sektor }})</dt>
            <dd>
                @forelse ($isi->sektor_terdeteksi as $sektor)
                    <span class="tag">{{ $sektor }}</span>
=======
            <dt>
                Sektor Potensi
                ({{ $isi->jumlah_sektor }})
            </dt>

            <dd>
                @forelse ($isi->sektor_terdeteksi as $sektor)
                    <span class="tag">
                        {{ $sektor }}
                    </span>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
                @empty
                    -
                @endforelse
            </dd>
        </div>

        {{-- Kebutuhan konektivitas --}}
        <div class="span-2">
<<<<<<< HEAD
            <dt>Kebutuhan Konektivitas Regional @if ($isi->jumlah_tujuan !== null)({{ $isi->jumlah_tujuan }} tujuan)@endif</dt>
            <dd class="prewrap">{{ $isi->kebutuhan_konektivitas ?: '-' }}</dd>
=======
            <dt>
                Kebutuhan Konektivitas Regional

                @if ($isi->jumlah_tujuan !== null)
                    ({{ $isi->jumlah_tujuan }} tujuan)
                @endif
            </dt>

            <dd class="prewrap">
                {{ $isi->kebutuhan_konektivitas ?: '-' }}
            </dd>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        </div>

        {{-- Tanggal pengajuan --}}
        <div>
            <dt>Tanggal Pengajuan</dt>

            <dd>
                {{ $rute?->tanggal_pengajuan?->format('d-m-Y') ?? '-' }}
            </dd>
        </div>

        {{-- Penginput --}}
        <div>
            <dt>Diinput oleh</dt>

            <dd>
                {{ $pemda->user->full_name ?? '-' }}
            </dd>
        </div>

    </dl>
</section>

<<<<<<< HEAD
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
                <thead><tr><th>Jenis Dokumen</th><th>Nomor Surat</th><th>Nama File</th><th class="num">Versi</th><th>Tanggal Upload</th><th></th></tr></thead>
                <tbody>
                    @foreach ($rute->dokumen as $dok)
                        <tr>
                            <td data-label="Jenis Dokumen"><strong>{{ $dok->jenis_dokumen }}</strong></td>
                            <td data-label="Nomor Surat" class="soft">{{ $dok->nomor_surat ?: '-' }}</td>
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
=======

{{-- ========================================================= --}}
{{-- 2. RUTE TUJUAN DAN DETAIL KOMODITAS --}}
{{-- ========================================================= --}}

@if ($rute && $rute->tujuan->isNotEmpty())

    @php
        /*
         * Ambil seluruh komoditas dari tabel:
         *
         * rute_subsidi_tujuan_komoditas
         *
         * Kelompokkan berdasarkan kabupaten/kota tujuan.
         *
         * Kolom utama:
         * - nama_komoditas
         * - jumlah
         * - satuan_data
         *
         * Kolom berat hanya digunakan sebagai fallback.
         */

        $komPerTujuan = $rute->detailKomoditasTujuan
            ->groupBy('id_kab_kota_tujuan');
    @endphp

    <section class="box">

        <div class="box-head">
            <h2>Rute Tujuan</h2>

            <p class="muted">
                Pengajuan {{ $rute->nomor_laporan }}
                &middot;
                moda {{ $rute->moda->nama_moda ?? '-' }}
            </p>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        </div>

        <div class="table-box">

            <table class="table table-data">

                <thead>
                    <tr>
                        <th>Tujuan</th>
                        <th>Komoditas</th>
                        <th class="num">Jumlah</th>
                        <th>Satuan Data</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($rute->tujuan as $t)

                        @php
                            /*
                             * Ambil daftar komoditas untuk
                             * kabupaten/kota tujuan saat ini.
                             */
                            $daftar = $komPerTujuan->get(
                                $t->id_kab_kota,
                                collect()
                            );

                            /*
                             * Kompatibilitas data lama.
                             *
                             * Jika tidak ada komoditas
                             * pada tabel detail, gunakan
                             * data dari tabel pivot.
                             *
                             * Satuan data lama belum diketahui.
                             */
                            if (
                                $daftar->isEmpty() &&
                                trim((string) $t->pivot->komoditas) !== ''
                            ) {
                                $daftar = collect([
                                    (object) [
                                        'nama_komoditas' =>
                                            $t->pivot->komoditas,

                                        'jumlah' =>
                                            $t->pivot->berat,

                                        'satuan_data' => null,
                                    ],
                                ]);
                            }

                            $jumlahKomoditas = $daftar->count();
                        @endphp

                        @forelse ($daftar as $k)

                            @php
                                /*
                                 * Data baru menggunakan jumlah.
                                 * Data lama menggunakan berat.
                                 */
                                $jumlah = data_get($k, 'jumlah')
                                    ?? data_get($k, 'berat');

                                $satuan = data_get($k, 'satuan_data');

                                $namaKomoditas = data_get(
                                    $k,
                                    'nama_komoditas',
                                    '-'
                                );
                            @endphp

                            <tr>

                                {{-- Kabupaten/Kota Tujuan --}}
                                <td data-label="Tujuan">

                                    @if ($loop->first)

                                        <strong>
                                            {{ $t->nama_kab_kota }}
                                        </strong>

                                        @if ($jumlahKomoditas > 1)

                                            <small
                                                class="muted"
                                                style="display:block"
                                            >
                                                {{ $jumlahKomoditas }}
                                                komoditas
                                            </small>

                                        @endif

                                    @endif

                                </td>

                                {{-- Nama Komoditas --}}
                                <td
                                    data-label="Komoditas"
                                    class="soft"
                                >
                                    {{ $namaKomoditas }}
                                </td>

                                {{-- Jumlah Komoditas --}}
                                <td
                                    data-label="Jumlah"
                                    class="num"
                                >
                                    <strong>
                                        {{ $fmtJumlah($jumlah) }}
                                    </strong>
                                </td>

                                {{-- Satuan Data --}}
                                <td data-label="Satuan Data">

                                    @if (
                                        $satuan !== null &&
                                        trim((string) $satuan) !== ''
                                    )

                                        <span class="tag">
                                            {{ $satuan }}
                                        </span>

                                    @else

                                        <span class="muted">
                                            Belum diketahui
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            {{-- Tujuan tanpa data komoditas --}}
                            <tr>

                                <td data-label="Tujuan">
                                    <strong>
                                        {{ $t->nama_kab_kota }}
                                    </strong>
                                </td>

                                <td
                                    data-label="Komoditas"
                                    class="soft"
                                >
                                    -
                                </td>

                                <td
                                    data-label="Jumlah"
                                    class="num"
                                >
                                    -
                                </td>

                                <td data-label="Satuan Data">
                                    -
                                </td>

                            </tr>

                        @endforelse

                    @endforeach

                </tbody>

            </table>

        </div>

        <p class="muted" style="margin-top:12px;font-size:.82rem;">
            Jumlah dan satuan ditampilkan berdasarkan data
            setiap komoditas. Komoditas dengan satuan berbeda
            tidak dijumlahkan menjadi satu total.
        </p>

    </section>

@endif


{{-- ========================================================= --}}
{{-- 3. PENGAJUAN INI --}}
{{-- ========================================================= --}}

<section class="box">

    <div class="box-head">
        <h2>Pengajuan Ini</h2>

        <p class="muted">
            Status peninjauan dan surat untuk pengajuan yang dipilih.
        </p>
    </div>

    <div class="table-box">

        <table class="table table-data">

            <thead>
                <tr>
                    <th>Nomor laporan</th>
                    <th>Moda</th>
                    <th>Status</th>
                    <th>Surat CHR</th>
                    <th>Surat Rekomendasi Teknis</th>
                </tr>
            </thead>

            <tbody>

                @forelse (collect([$rute])->filter() as $r)

                    @php
                        $st = \App\Support\StatusPengajuan::normalisasi(
                            $r->status_pengajuan
                        );

                        $chr = $r->chr_terbaru;

                        $rt = $r->rekomendasi_terbaru;
                    @endphp

                    <tr>

                        {{-- Nomor laporan --}}
                        <td data-label="Nomor laporan">
                            <strong>
                                {{ $r->nomor_laporan }}
                            </strong>
                        </td>

                        {{-- Moda --}}
                        <td data-label="Moda">
                            {{ $r->moda->nama_moda ?? '-' }}
                        </td>

                        {{-- Status --}}
                        <td data-label="Status">
                            <span class="badge {{ \App\Support\StatusPengajuan::kelas($st) }}">
                                {{ $st }}
                            </span>
                        </td>

                        {{-- Surat CHR --}}
                        <td data-label="Surat CHR">

                            @if (! $chr)

                                -

                            @elseif (
                                $chr->bisaDiaksesOleh(auth()->user()) &&
                                $chr->dokumen->isNotEmpty()
                            )

                                <div class="doc-links">

                                    <span>
                                        {{ $chr->status }}
                                    </span>

                                    @foreach ($chr->dokumen as $d)

                                        <div class="acts">

                                            <a
                                                class="sr-lihat"
                                                target="_blank"
                                                rel="noopener"
                                                href="{{ route('surat.dokumen', $d->id_dokumen_surat) }}"
                                            >
                                                Lihat
                                            </a>

                                            <a
                                                class="icon-act"
                                                href="{{ route('surat.dokumen', [$d->id_dokumen_surat, 'unduh' => 1]) }}"
                                                title="Unduh {{ $d->nama_jenis }}"
                                                aria-label="Unduh {{ $d->nama_jenis }}"
                                            >
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    width="18"
                                                    height="18"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >
                                                    <path d="M12 4v11M8 11l4 4 4-4M5 20h14"/>
                                                </svg>
                                            </a>

                                        </div>

                                        <small>
                                            {{ $d->nama_jenis }}
                                        </small>

                                    @endforeach

                                </div>

                            @else

                                {{ $chr->status }}

                            @endif

                        </td>

                        {{-- Surat Rekomendasi Teknis --}}
                        <td data-label="Surat Rekomendasi Teknis">

                            @if ($rt)

                                <div class="acts">

                                    <a
                                        class="sr-lihat"
                                        target="_blank"
                                        rel="noopener"
                                        href="{{ route('rekomendasi.dokumen', $rt->id_rekomendasi_teknis) }}"
                                    >
                                        Lihat
                                    </a>

                                    <a
                                        class="icon-act"
                                        href="{{ route('rekomendasi.dokumen', [$rt->id_rekomendasi_teknis, 'unduh' => 1]) }}"
                                        title="Unduh"
                                        aria-label="Unduh Surat Rekomendasi Teknis"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            width="18"
                                            height="18"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M12 4v11M8 11l4 4 4-4M5 20h14"/>
                                        </svg>
                                    </a>

                                </div>

                            @else

                                <span class="muted">
                                    Belum diunggah
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="empty">
                            Belum ada pengajuan.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</section>

<<<<<<< HEAD
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
=======

{{-- ========================================================= --}}
{{-- 4. DOKUMEN PENGAJUAN --}}
{{-- ========================================================= --}}

<section class="box">

    <div class="box-head">
        <h2>Dokumen Pengajuan</h2>

        @if ($rute)
            <p class="muted">
                {{ $rute->nomor_laporan }}
                &middot;
                moda {{ $rute->moda->nama_moda ?? '-' }}
            </p>
        @endif
    </div>

    @if ($rute && $rute->dokumen->isNotEmpty())

        <div class="table-box">

            <table class="table table-data">

                <thead>
                    <tr>
                        <th>Jenis Dokumen</th>
                        <th>Nomor Surat</th>
                        <th>Nama File</th>
                        <th class="num">Versi</th>
                        <th>Tanggal Upload</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($rute->dokumen as $dok)

                        <tr>

                            <td data-label="Jenis Dokumen">
                                <strong>
                                    {{ $dok->jenis_dokumen }}
                                </strong>
                            </td>

                            <td
                                data-label="Nomor Surat"
                                class="soft"
                            >
                                {{ $dok->nomor_surat ?: '-' }}
                            </td>

                            <td
                                data-label="Nama File"
                                class="soft"
                            >
                                {{ $dok->nama_file }}
                            </td>

                            <td
                                data-label="Versi"
                                class="num"
                            >
                                {{ $dok->versi_dokumen }}
                            </td>

                            <td
                                data-label="Tanggal Upload"
                                class="soft"
                            >
                                {{ $dok->tanggal_upload?->format('d-m-Y H:i') }}
                            </td>

                            <td class="td-aksi">

                                @if ($bisaUnduh)

                                    <a
                                        class="btn btn-soft"
                                        href="{{ route('dokumen.unduh', $dok->id_dokumen_pengajuan) }}"
                                    >
                                        Unduh
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <p class="empty-note">
            Belum ada dokumen pengajuan untuk daerah ini.
        </p>

    @endif

</section>


{{-- ========================================================= --}}
{{-- 5. RIWAYAT PENINJAUAN --}}
{{-- ========================================================= --}}

@if ($rute && $rute->verifikasi->isNotEmpty())

    <section class="box">

        <div class="box-head">
            <h2>Riwayat Peninjauan</h2>

            <p class="muted">
                {{ $rute->nomor_laporan }}
            </p>
        </div>

        <ol class="rv-timeline">

            @foreach (
                $rute->verifikasi->sortBy('id_verifikasi_pengajuan')
                as $v
            )

                <li class="done s-{{ \App\Support\StatusPengajuan::grup($v->status_verifikasi) }}">

                    <strong>
                        {{ $v->tahap_verifikasi }}:
                        {{ $v->status_verifikasi }}
                    </strong>

                    <span>
                        Oleh {{ $v->verifikator->full_name ?? '-' }}
                    </span>

                    @if ($v->catatan_verifikasi)

                        <p class="rv-note">
                            {{ $v->catatan_verifikasi }}
                        </p>

                    @endif

                    <small>
                        {{ \App\Support\Tanggal::pendek($v->tanggal_verifikasi) }}
                        &bull;
                        {{ \App\Support\Tanggal::jam($v->tanggal_verifikasi) }}
                    </small>

                </li>

            @endforeach

        </ol>

    </section>

@endif


{{-- ========================================================= --}}
{{-- KEMBALI --}}
{{-- ========================================================= --}}

<a
    class="btn btn-outline"
    href="{{ route('data-wilayah.index') }}"
>
    &larr; Kembali ke daftar
</a>

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
@endsection
