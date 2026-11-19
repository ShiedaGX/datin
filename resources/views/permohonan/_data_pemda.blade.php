
@php
    // Daftar kabupaten/kota tujuan.
    $daftarTujuan = $rute->tujuan;

    // Detail komoditas dari tabel rute_subsidi_tujuan_komoditas.
    $komPerTujuan = $rute->detailKomoditasTujuan
        ->groupBy('id_kab_kota_tujuan');
@endphp

{{-- 2. Data yang diinput Pemda --}}
<section>
    <h3 class="rv-h">
        <span class="step-no">{{ $no }}</span>
        Data Pengajuan Pemda
    </h3>

    <p class="muted rv-hs">
        Data yang diinput Pemda pada formulir pengajuan. Cocokkan dengan isi dokumen surat.
    </p>

    <div class="rv-data">

        {{-- INFORMASI WILAYAH ASAL --}}
        <dl class="rv-grid">
            <div>
                <dt>Provinsi</dt>
                <dd>{{ $rute->kabKotaAsal->provinsi->nama_provinsi }}</dd>
            </div>

            <div>
                <dt>Kabupaten / Kota Asal</dt>
                <dd>{{ $rute->kabKotaAsal->nama_lengkap }}</dd>
            </div>

            <div>
                <dt>Wilayah Regional</dt>
                <dd>{{ $rute->kabKotaAsal->wilayah->nama_wilayah }}</dd>
            </div>

            <div>
                <dt>Moda Transportasi</dt>
                <dd>{{ $rute->moda->nama_moda ?? '-' }}</dd>
            </div>

            <div>
                <dt>Klasifikasi Wilayah</dt>
                <dd>
                    <span class="badge badge-{{ \Illuminate\Support\Str::slug($rute->klasifikasi_label) }}">
                        {{ $rute->klasifikasi_label }}
                    </span>
                </dd>
            </div>
        </dl>

        {{-- KETERANGAN DAERAH ASAL --}}
        <div class="rv-block">
            <h4>Keterangan Daerah Asal</h4>

            @forelse ($rute->keterangan_daftar as $butir)
                <span class="tag">{{ $butir }}</span>
            @empty
                <span class="muted">-</span>
            @endforelse
        </div>

        {{-- POTENSI UNGGULAN DAERAH ASAL --}}
        <div class="rv-block">
            <h4>Potensi Unggulan Daerah Asal</h4>

            @if (count($rute->potensi_daftar))
                <ol class="rv-list">
                    @foreach ($rute->potensi_daftar as $butir)
                        <li>{{ $butir }}</li>
                    @endforeach
                </ol>
            @else
                <span class="muted">-</span>
            @endif
        </div>

        {{-- SEKTOR POTENSI DAERAH ASAL --}}
        <div class="rv-block">
            <h4>Sektor Potensi Daerah Asal ({{ $rute->jumlah_sektor }})</h4>

            @forelse ($rute->sektor_terdeteksi as $sektor)
                <span class="tag">{{ $sektor }}</span>
            @empty
                <span class="muted">-</span>
            @endforelse
        </div>

        {{-- DETAIL KEBUTUHAN KONEKTIVITAS / RUTE TUJUAN --}}
        <div class="rv-block">
            <h4>
                Kebutuhan Konektivitas / Rute Tujuan
                ({{ $daftarTujuan->count() }} tujuan)
            </h4>

            @forelse ($daftarTujuan as $tujuan)

                @php
                    // Ambil detail komoditas berdasarkan ID kabupaten/kota tujuan.
                    $daftarKomoditas = $komPerTujuan->get(
                        $tujuan->id_kab_kota,
                        collect()
                    );

                    // Kompatibilitas data pengajuan lama yang masih memakai pivot.
                    if (
                        $daftarKomoditas->isEmpty() &&
                        trim((string) ($tujuan->pivot->komoditas ?? '')) !== ''
                    ) {
                        $daftarKomoditas = collect([
                            (object) [
                                'nama_komoditas' => $tujuan->pivot->komoditas,
                                'jumlah' => $tujuan->pivot->berat,
                                'satuan_data' => 'Ton',
                            ]
                        ]);
                    }
                @endphp

                <div class="rv-tujuan-item">

                    {{-- NAMA KABUPATEN / KOTA TUJUAN --}}
                    <strong class="rv-tujuan-title">
                        {{ $loop->iteration }}.
                        {{ $tujuan->nama_lengkap }}
                    </strong>

                    @if ($daftarKomoditas->isNotEmpty())

                        <div class="table-box rv-komoditas-table">
                            <table class="table table-data">
                                <thead>
                                    <tr>
                                        <th>Komoditas</th>
                                        <th>Sektor</th>
                                        <th class="num">Jumlah</th>
                                        <th>Satuan</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($daftarKomoditas as $kom)
                                        @php
                                            $namaKomoditas = trim(
                                                (string) $kom->nama_komoditas
                                            );

                                            $sektorTujuan = \App\Support\Sektor::dariKomoditas(
                                                $namaKomoditas
                                            );

                                            $jumlah = $kom->jumlah;
                                            $satuan = $kom->satuan_data;
                                        @endphp

                                        <tr>
                                            <td data-label="Komoditas">
                                                <strong>{{ $namaKomoditas ?: '-' }}</strong>
                                            </td>

                                            <td data-label="Sektor">
                                                @if (!empty($sektorTujuan))
                                                    {{ is_array($sektorTujuan)
                                                        ? implode(', ', $sektorTujuan)
                                                        : $sektorTujuan }}
                                                @else
                                                    <span class="muted">
                                                        Tidak teridentifikasi
                                                    </span>
                                                @endif
                                            </td>

                                            <td data-label="Jumlah" class="num">
                                                {{ $jumlah !== null
                                                    ? rtrim(rtrim(number_format((float) $jumlah, 2, ',', '.'), '0'), ',')
                                                    : '-' }}
                                            </td>

                                            <td data-label="Satuan">
                                                {{ $satuan ?: '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    @else
                        <div class="rv-komoditas-kosong">
                            <span class="muted">
                                Belum ada detail komoditas untuk tujuan ini.
                            </span>
                        </div>
                    @endif

                </div>

            @empty
                <span class="muted">
                    Belum ada data rute tujuan.
                </span>
            @endforelse
        </div>

    </div>
</section>

<style>
    .rv-tujuan-item {
        padding: 12px 14px;
        margin-top: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .rv-tujuan-title {
        display: block;
        margin-bottom: 12px;
        color: #1e3a8a;
        font-size: 13px;
        font-weight: 700;
    }

    .rv-komoditas-table {
        width: 100%;
        overflow-x: auto;
    }

    .rv-komoditas-table table {
        width: 100%;
        min-width: 520px;
        margin: 0;
    }

    .rv-komoditas-table th,
    .rv-komoditas-table td {
        padding: 10px 12px;
    }

    .rv-komoditas-kosong {
        padding: 12px;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
    }

    @media (max-width: 640px) {
        .rv-komoditas-table {
            overflow-x: auto;
        }
    }
</style>
