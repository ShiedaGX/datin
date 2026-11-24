<<<<<<< HEAD
            {{-- 2. Data yang diinput Pemda --}}
            <section>
                <h3 class="rv-h"><span class="step-no">{{ $no }}</span> Data Pengajuan Pemda</h3>
                <p class="muted rv-hs">Data yang diinput Pemda pada formulir pengajuan. Cocokkan dengan isi dokumen surat.</p>
                <div class="rv-data">
                    <dl class="rv-grid">
                        <div><dt>Provinsi</dt><dd>{{ $rute->kabKotaAsal->provinsi->nama_provinsi }}</dd></div>
                        <div><dt>Kabupaten / Kota</dt><dd>{{ $rute->kabKotaAsal->nama_lengkap }}</dd></div>
                        <div><dt>Wilayah regional</dt><dd>{{ $rute->kabKotaAsal->wilayah->nama_wilayah }}</dd></div>
                        <div><dt>Moda transportasi</dt><dd>{{ $rute->moda->nama_moda ?? '-' }}</dd></div>
                        <div><dt>Klasifikasi wilayah</dt><dd><span class="badge badge-{{ \Illuminate\Support\Str::slug($rute->klasifikasi_label) }}">{{ $rute->klasifikasi_label }}</span></dd></div>
                    </dl>
                    <div class="rv-block">
                        <h4>Keterangan daerah</h4>
                        @forelse ($rute->keterangan_daftar as $butir)<span class="tag">{{ $butir }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                    <div class="rv-block">
                        <h4>Potensi unggulan daerah</h4>
                        @if (count($rute->potensi_daftar))
                            <ol class="rv-list">
                                @foreach ($rute->potensi_daftar as $butir)<li>{{ $butir }}</li>@endforeach
                            </ol>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </div>
                    <div class="rv-block">
                        <h4>Sektor potensi ({{ $rute->jumlah_sektor }})</h4>
                        @forelse ($rute->sektor_terdeteksi as $sektor)<span class="tag">{{ $sektor }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                    <div class="rv-block">
                        <h4>Kebutuhan konektivitas / rute tujuan @if ($rute->jumlah_tujuan !== null)({{ $rute->jumlah_tujuan }} tujuan)@endif</h4>
                        @forelse ($rute->tujuan_daftar as $tujuan)<span class="tag tag-alt">{{ $tujuan }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                </div>
            </section>
=======
@php
    $daftarTujuan = $rute->tujuan;

    $komPerTujuan = $rute->detailKomoditasTujuan
        ->groupBy('id_kab_kota_tujuan');
@endphp

<section>
    <h3 class="rv-h">
        <span class="step-no">{{ $no }}</span>
        Data Pengajuan Pemda
    </h3>

    <p class="muted rv-hs">
        Data yang diinput Pemda pada formulir pengajuan.
        Cocokkan dengan isi dokumen surat.
    </p>

    <div class="rv-data">

        {{-- INFORMASI WILAYAH ASAL --}}
        <dl class="rv-grid">

            <div>
                <dt>Provinsi</dt>
                <dd>
                    {{ $rute->kabKotaAsal->provinsi->nama_provinsi ?? '-' }}
                </dd>
            </div>

            <div>
                <dt>Kabupaten / Kota Asal</dt>
                <dd>
                    {{ $rute->kabKotaAsal->nama_lengkap }}
                </dd>
            </div>

            <div>
                <dt>Wilayah Regional</dt>
                <dd>
                    {{ $rute->kabKotaAsal->wilayah->nama_wilayah ?? '-' }}
                </dd>
            </div>

            <div>
                <dt>Moda Transportasi</dt>
                <dd>
                    {{ $rute->moda->nama_moda ?? '-' }}
                </dd>
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

                <span class="tag">
                    {{ $butir }}
                </span>

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

            <h4>
                Sektor Potensi Daerah Asal
                ({{ $rute->jumlah_sektor }})
            </h4>

            @forelse ($rute->sektor_terdeteksi as $sektor)

                <span class="tag">
                    {{ $sektor }}
                </span>

            @empty

                <span class="muted">-</span>

            @endforelse

        </div>

        {{-- RINCIAN KEBUTUHAN KONEKTIVITAS --}}
        <div class="rv-block">

            <h4>
                Kebutuhan Konektivitas / Rute Tujuan
                ({{ $daftarTujuan->count() }} tujuan)
            </h4>

            @forelse ($daftarTujuan as $tujuan)

                @php
                    $daftarKomoditas = $komPerTujuan->get(
                        $tujuan->id_kab_kota,
                        collect()
                    );

                    $dataLama = false;

                    /*
                    | Fallback untuk pengajuan lama.
                    |
                    | Digunakan hanya jika belum terdapat
                    | detail komoditas pada struktur baru.
                    */

                    if (
                        $daftarKomoditas->isEmpty()
                        && trim(
                            (string) ($tujuan->pivot->komoditas ?? '')
                        ) !== ''
                    ) {
                        $dataLama = true;

                        $daftarKomoditas = collect([
                            (object) [
                                'nama_komoditas' =>
                                    $tujuan->pivot->komoditas,

                                'jumlah' =>
                                    $tujuan->pivot->berat,

                                'satuan_data' => null,
                            ]
                        ]);
                    }
                @endphp

                <div class="rv-tujuan-item">

                    {{-- NAMA DAERAH TUJUAN --}}
                    <div class="rv-tujuan-head">

                        <strong class="rv-tujuan-title">
                            {{ $loop->iteration }}.
                            {{ $tujuan->nama_lengkap }}
                        </strong>

                        @if ($daftarKomoditas->isNotEmpty())

                            <span class="rv-komoditas-count">
                                {{ $daftarKomoditas->count() }}
                                komoditas
                            </span>

                        @endif

                    </div>

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

                                            $sektorTujuan = $namaKomoditas !== ''
                                                ? \App\Support\Sektor::dariKomoditas(
                                                    $namaKomoditas
                                                )
                                                : [];

                                            $jumlah = $kom->jumlah;

                                            $satuan = trim(
                                                (string) ($kom->satuan_data ?? '')
                                            );

                                            $jumlahValid =
                                                $jumlah !== null
                                                && trim((string) $jumlah) !== ''
                                                && is_numeric($jumlah)
                                                && is_finite((float) $jumlah);
                                        @endphp

                                        <tr>

                                            {{-- KOMODITAS --}}
                                            <td data-label="Komoditas">

                                                <strong>
                                                    {{ $namaKomoditas ?: '-' }}
                                                </strong>

                                            </td>

                                            {{-- SEKTOR --}}
                                            <td data-label="Sektor">

                                                @if (!empty($sektorTujuan))

                                                    {{
                                                        is_array($sektorTujuan)
                                                            ? implode(', ', $sektorTujuan)
                                                            : $sektorTujuan
                                                    }}

                                                @else

                                                    <span class="muted">
                                                        Tidak teridentifikasi
                                                    </span>

                                                @endif

                                            </td>

                                            {{-- JUMLAH --}}
                                            <td data-label="Jumlah" class="num">

                                                {{
                                                    $jumlahValid
                                                        ? rtrim(
                                                            rtrim(
                                                                number_format(
                                                                    (float) $jumlah,
                                                                    2,
                                                                    ',',
                                                                    '.'
                                                                ),
                                                                '0'
                                                            ),
                                                            ','
                                                        )
                                                        : '-'
                                                }}

                                            </td>

                                            {{-- SATUAN --}}
                                            <td data-label="Satuan">

                                                @if ($satuan !== '')

                                                    <span class="rv-satuan">
                                                        {{ $satuan }}
                                                    </span>

                                                @else

                                                    <span class="rv-satuan-unknown">
                                                        Belum diketahui
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        {{-- INFORMASI DATA LAMA --}}
                        @if ($dataLama)

                            <div class="rv-legacy-note">

                                <svg
                                    viewBox="0 0 24 24"
                                    width="17"
                                    height="17"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 11v5M12 8h.01"/>
                                </svg>

                                <span>
                                    Rincian ini berasal dari data
                                    pengajuan lama. Satuan asli belum
                                    dapat dipastikan dan tidak
                                    diasumsikan sebagai Ton.
                                </span>

                            </div>

                        @endif

                    @else

                        <div class="rv-komoditas-kosong">

                            <span class="muted">
                                Belum ada detail komoditas
                                untuk tujuan ini.
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

    .rv-tujuan-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .rv-tujuan-title {
        display: block;
        color: #1e3a8a;
        font-size: 13px;
        font-weight: 700;
        margin: 0;
    }

    .rv-komoditas-count {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
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

    .rv-satuan {
        color: #334155;
        font-size: 12px;
        font-weight: 500;
    }

    .rv-satuan-unknown {
        display: inline-block;
        padding: 4px 8px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 5px;
        color: #92400e;
        font-size: 11px;
        font-weight: 500;
    }

    .rv-legacy-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 10px;
        padding: 10px 12px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 6px;
        color: #92400e;
        font-size: 11px;
        line-height: 1.6;
    }

    .rv-legacy-note svg {
        flex-shrink: 0;
        margin-top: 1px;
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

        .rv-tujuan-head {
            align-items: flex-start;
        }
    }
</style>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
