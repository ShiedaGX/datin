
@php
    // Memuat tujuan beserta komoditas dan berat dari tabel pivot.
    $daftarTujuan = $rute->tujuan;

    // Master komoditas untuk mendapatkan sektor secara otomatis.
    $masterKomoditas = collect(\App\Support\Sektor::komoditas());
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
                    // Mengambil nama komoditas dari tabel master.
                    $namaKomoditas = $tujuan->pivot->komoditas;

                    // Mencari sektor berdasarkan komoditas yang diinput Pemda.
                    $dataKomoditas = $masterKomoditas->firstWhere(
                        'nama',
                        $namaKomoditas
                    );

                    $sektorTujuan = $dataKomoditas['sektor'] ?? [];
                @endphp

                <div class="rv-tujuan-item">

                    {{-- NAMA KABUPATEN / KOTA TUJUAN --}}
                    <strong class="rv-tujuan-title">
                        {{ $loop->iteration }}.
                        {{ $tujuan->nama_lengkap }}
                    </strong>

                    <div class="rv-tujuan-detail">

                        {{-- KOMODITAS --}}
                        <div>
                            <span>Potensi Komoditas</span>
                            <strong>
                                {{ $namaKomoditas ?: '-' }}
                            </strong>
                        </div>

                        {{-- SEKTOR --}}
                        <div>
                            <span>Sektor</span>
                            <strong>
                                @if (count($sektorTujuan))
                                    {{ implode(', ', $sektorTujuan) }}
                                @else
                                    Tidak teridentifikasi
                                @endif
                            </strong>
                        </div>

                        {{-- BERAT --}}
                        <div>
                            <span>Berat (ton)</span>
                            <strong>
                                @if ($tujuan->pivot->berat !== null)
                                    {{ number_format((float) $tujuan->pivot->berat, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </strong>
                        </div>

                    </div>
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
    /* Kartu setiap rute tujuan */
    .rv-tujuan-item {
        padding: 12px 14px;
        margin-top: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    /* Nama kabupaten/kota tujuan */
    .rv-tujuan-title {
        display: block;
        margin-bottom: 12px;
        color: #1e3a8a;
        font-size: 13px;
        font-weight: 700;
    }

    /* Susunan informasi komoditas, sektor, dan berat */
    .rv-tujuan-detail {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .rv-tujuan-detail div {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 0;
    }

    .rv-tujuan-detail span {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .rv-tujuan-detail strong {
        font-size: 13px;
        color: #1e293b;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    /* Tampilan perangkat mobile */
    @media (max-width: 640px) {
        .rv-tujuan-detail {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
</style>
