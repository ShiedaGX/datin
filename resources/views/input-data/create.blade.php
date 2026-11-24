@extends('layouts.app')

@section('title', 'Input Data Wilayah Baru')
@section('heading', 'Input Data Wilayah Baru')

@section('content')
<form class="card form" id="formInput" method="POST" action="{{ route('input-data.store') }}" enctype="multipart/form-data">
    @csrf
        @if ($sumber ?? null)
            <div class="alert alert-info form-prefill" role="status">
                <div>
                    <strong>Pengajuan ulang atas permintaan revisi INTRAM</strong>
                    <p>Data diisi otomatis dari pengajuan sebelumnya ({{ $sumber->nomor_laporan }}). Periksa dan ubah bila perlu, lalu unggah dokumen pengajuan untuk moda yang diminta. Pengajuan moda sebelumnya tidak berubah.</p>
                </div>
            </div>
        @endif


    @if ($sumber ?? null)
        <div class="alert alert-info form-prefill" role="status">
            <div>
                <strong>Pengajuan ulang atas permintaan revisi INTRAM</strong>
                <p>
                    Data diisi otomatis dari pengajuan sebelumnya
                    ({{ $sumber->nomor_laporan }}).
                    Periksa dan ubah bila perlu, lalu unggah dokumen
                    pengajuan untuk moda yang diminta.
                    Pengajuan moda sebelumnya tidak berubah.
                </p>
            </div>
        </div>
    @endif

    <div class="card-head">
        <h2>Sistem Formulir Digital Pengajuan Subsidi</h2>
        <p>
            Pastikan semua data wilayah, keterkaitan geografis,
            potensi lokal, serta sektor potensial diisi dengan akurat
            sesuai rujukan Bappenas.
        </p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            <strong>Data belum dapat disimpan.</strong>
            Periksa kembali isian yang ditandai merah.
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- 1. INFORMASI ADMINISTRASI DAN WILAYAH --}}
    {{-- ========================================================= --}}

    <section class="step">
        <h3>
            <span class="step-no">1</span>
            Informasi Administrasi &amp; Wilayah
        </h3>

        @if ($akunBelumTerhubung)
            <div class="alert alert-error" role="alert">
                <strong>Akun Anda belum terhubung ke kabupaten/kota.</strong>
                Hubungi Super Admin untuk melengkapi data akun sebelum
                mengisi formulir ini.
            </div>
        @elseif ($terkunci && $alasanSaya)
            <div class="alert alert-info" role="status">
                <strong>Kabupaten/Kota Anda saat ini tidak dapat diajukan:</strong>
                {{ $alasanSaya }}.
            </div>
        @endif

        @if ($terkunci)
            @php
                $idWilayahSaya = $kabSaya->provinsi->id_wilayah ?? $kabSaya->id_wilayah;
                $wilayahSaya = $wilayah->firstWhere('id_wilayah', $idWilayahSaya);
                $provinsiSaya = $provinsi->firstWhere('id_provinsi', $kabSaya->id_provinsi);
                $kabSayaData = collect($kabKota)->firstWhere('id', $kabSaya->id_kab_kota);
            @endphp

            {{-- Select tersembunyi untuk akun Pemda yang terkunci --}}
            <select id="id_wilayah" name="id_wilayah" hidden aria-hidden="true" tabindex="-1">
                <option value="{{ $idWilayahSaya }}" selected>
                    {{ $wilayahSaya->nama_wilayah ?? '' }}
                </option>
            </select>

            <select id="id_provinsi" name="id_provinsi" hidden aria-hidden="true" tabindex="-1">
                <option value="{{ $kabSaya->id_provinsi }}"
                    data-wilayah="{{ $idWilayahSaya }}" selected>
                    {{ $provinsiSaya->nama_provinsi ?? '' }}
                </option>
            </select>

            <select id="id_kab_kota" name="id_kab_kota" hidden aria-hidden="true" tabindex="-1">
                <option value="{{ $kabSaya->id_kab_kota }}"
                    data-provinsi="{{ $kabSaya->id_provinsi }}"
                    @if ($kabSayaData && count($kabSayaData['revisi']))
                        data-revisi="{{ implode(',', $kabSayaData['revisi']) }}"
                        data-isi="{{ route('input-data.create', ['kab' => $kabSaya->id_kab_kota]) }}"
                    @endif
                    @if ($kabSayaData && count($kabSayaData['sudah']))
                        data-sudah="{{ implode(',', $kabSayaData['sudah']) }}"
                    @endif
                    selected>
                    {{ $kabSaya->nama_kab_kota }}
                </option>
            </select>
        @endif

        @if ($akunBelumTerhubung)
            <div class="alert alert-error" role="alert">
                <strong>Akun Anda belum terhubung ke kabupaten/kota.</strong> Hubungi Super Admin untuk melengkapi data akun sebelum mengisi formulir ini.
            </div>
        @elseif ($terkunci && $alasanSaya)
            <div class="alert alert-info" role="status">
                <strong>Kabupaten/Kota Anda saat ini tidak dapat diajukan:</strong> {{ $alasanSaya }}.
            </div>
        @endif

        @if ($terkunci)
            @php
                $idWilayahSaya = $kabSaya->provinsi->id_wilayah ?? $kabSaya->id_wilayah;
                $wilayahSaya   = $wilayah->firstWhere('id_wilayah', $idWilayahSaya);
                $provinsiSaya  = $provinsi->firstWhere('id_provinsi', $kabSaya->id_provinsi);
                $kabSayaData   = collect($kabKota)->firstWhere('id', $kabSaya->id_kab_kota);
            @endphp

            {{-- Select tersembunyi: menjaga nilai yang dikirim & JS halaman ini tetap bekerja --}}
            <select id="id_wilayah" name="id_wilayah" hidden aria-hidden="true" tabindex="-1">
                <option value="{{ $idWilayahSaya }}" selected>{{ $wilayahSaya->nama_wilayah ?? '' }}</option>
            </select>
            <select id="id_provinsi" name="id_provinsi" hidden aria-hidden="true" tabindex="-1">
                <option value="{{ $kabSaya->id_provinsi }}" data-wilayah="{{ $idWilayahSaya }}" selected>{{ $provinsiSaya->nama_provinsi ?? '' }}</option>
            </select>
            <select id="id_kab_kota" name="id_kab_kota" hidden aria-hidden="true" tabindex="-1">
                <option value="{{ $kabSaya->id_kab_kota }}" data-provinsi="{{ $kabSaya->id_provinsi }}"
                        @if ($kabSayaData && count($kabSayaData['revisi'])) data-revisi="{{ implode(',', $kabSayaData['revisi']) }}" data-isi="{{ route('input-data.create', ['kab' => $kabSaya->id_kab_kota]) }}" @endif
                        @if ($kabSayaData && count($kabSayaData['sudah'])) data-sudah="{{ implode(',', $kabSayaData['sudah']) }}" @endif
                        selected>{{ $kabSaya->nama_kab_kota }}</option>
            </select>
        @endif

        <div class="grid-2">

            {{-- Wilayah regional --}}
            <div class="field">
                @if ($terkunci)
                    <label for="tampil_wilayah">Wilayah Regional</label>
<<<<<<< HEAD
                    <input type="text" id="tampil_wilayah" class="is-locked" value="{{ $wilayahSaya->nama_wilayah ?? '' }}" readonly tabindex="-1">
                @else
                    <label for="id_wilayah">Wilayah Regional</label>
                    <select id="id_wilayah" name="id_wilayah" required class="@error('id_wilayah') is-invalid @enderror">
                        <option value="">Pilih Wilayah Regional (Papua/Sulawesi/Maluku/etc)...</option>
                        @foreach ($wilayah as $w)
                            <option value="{{ $w->id_wilayah }}" @selected(old('id_wilayah', $isian['id_wilayah'] ?? null) == $w->id_wilayah)>{{ $w->nama_wilayah }}</option>
                        @endforeach
                    </select>
                @endif
                @error('id_wilayah')<p class="error">{{ $message }}</p>@enderror
=======
                    <input
                        type="text"
                        id="tampil_wilayah"
                        class="is-locked"
                        value="{{ $wilayahSaya->nama_wilayah ?? '' }}"
                        readonly
                        tabindex="-1"
                    >
                @else
                    <label for="id_wilayah">Wilayah Regional</label>
                    <select
                        id="id_wilayah"
                        name="id_wilayah"
                        required
                        class="@error('id_wilayah') is-invalid @enderror"
                    >
                        <option value="">Pilih Wilayah Regional...</option>
                        @foreach ($wilayah as $w)
                            <option
                                value="{{ $w->id_wilayah }}"
                                @selected(old('id_wilayah', $isian['id_wilayah'] ?? null) == $w->id_wilayah)
                            >
                                {{ $w->nama_wilayah }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @error('id_wilayah')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Provinsi --}}
            <div class="field">
                @if ($terkunci)
                    <label for="tampil_provinsi">Provinsi</label>
                    <input
                        type="text"
                        id="tampil_provinsi"
                        class="is-locked"
                        value="{{ $provinsiSaya->nama_provinsi ?? '' }}"
                        readonly
                        tabindex="-1"
                    >
                @else
                    <label for="id_provinsi">Provinsi</label>
                    <select
                        id="id_provinsi"
                        name="id_provinsi"
                        required
                        class="@error('id_provinsi') is-invalid @enderror"
                    >
                        <option value="">Pilih Provinsi...</option>
                        @foreach ($provinsi as $p)
                            <option
                                value="{{ $p->id_provinsi }}"
                                data-wilayah="{{ $p->id_wilayah }}"
                                @selected(old('id_provinsi', $isian['id_provinsi'] ?? null) == $p->id_provinsi)
                            >
                                {{ $p->nama_provinsi }}
                            </option>
                        @endforeach
                    </select>
                @endif

                @error('id_provinsi')
                    <p class="error">{{ $message }}</p>
                @enderror
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
            </div>

            {{-- Kabupaten/kota asal --}}
            <div class="field">
                @if ($terkunci)
<<<<<<< HEAD
                    <label for="tampil_provinsi">Provinsi</label>
                    <input type="text" id="tampil_provinsi" class="is-locked" value="{{ $provinsiSaya->nama_provinsi ?? '' }}" readonly tabindex="-1">
                @else
                    <label for="id_provinsi">Provinsi</label>
                    <select id="id_provinsi" name="id_provinsi" required class="@error('id_provinsi') is-invalid @enderror">
                        <option value="">Pilih Provinsi...</option>
                        @foreach ($provinsi as $p)
                            <option value="{{ $p->id_provinsi }}" data-wilayah="{{ $p->id_wilayah }}" @selected(old('id_provinsi', $isian['id_provinsi'] ?? null) == $p->id_provinsi)>{{ $p->nama_provinsi }}</option>
                        @endforeach
                    </select>
                @endif
                @error('id_provinsi')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                @if ($terkunci)
                    <label for="tampil_kab_kota">Kabupaten / Kota</label>
                    <input type="text" id="tampil_kab_kota" class="is-locked" value="{{ $kabSaya->nama_kab_kota }}" readonly tabindex="-1">
                @else
                    <label for="id_kab_kota">Kabupaten / Kota</label>
                    <select id="id_kab_kota" name="id_kab_kota" required class="@error('id_kab_kota') is-invalid @enderror">
                        <option value="">Pilih Kabupaten / Kota</option>
                        @foreach ($kabKota as $k)
                            <option value="{{ $k['id'] }}" data-provinsi="{{ $k['id_provinsi'] }}"
                                    @if ($k['alasan']) disabled data-terkunci="1" @endif
                                    @if (count($k['revisi'])) data-revisi="{{ implode(',', $k['revisi']) }}" data-isi="{{ route('input-data.create', ['kab' => $k['id']]) }}" @endif
                                    @if (count($k['sudah'])) data-sudah="{{ implode(',', $k['sudah']) }}" @endif
                                    @selected((string) old('id_kab_kota', $isian['id_kab_kota'] ?? '') === (string) $k['id'])>{{ $k['nama'] }}@if ($k['alasan']) ({{ $k['alasan'] }})@elseif (count($k['revisi'])) (revisi diminta: {{ implode(', ', $k['revisiNama']) }})@elseif (count($k['sudah'])) (pernah diajukan: {{ implode(', ', $k['sudahNama']) }})@endif</option>
                        @endforeach
                    </select>
                @endif
                @error('id_kab_kota')<p class="error">{{ $message }}</p>@enderror
=======
                    <label for="tampil_kab_kota">Kabupaten / Kota</label>
                    <input
                        type="text"
                        id="tampil_kab_kota"
                        class="is-locked"
                        value="{{ $kabSaya->nama_kab_kota }}"
                        readonly
                        tabindex="-1"
                    >
                @else
                    <label for="id_kab_kota">Kabupaten / Kota</label>
                    <select
                        id="id_kab_kota"
                        name="id_kab_kota"
                        required
                        class="@error('id_kab_kota') is-invalid @enderror"
                    >
                        <option value="">Pilih Kabupaten / Kota</option>

                        @foreach ($kabKota as $k)
                            <option
                                value="{{ $k['id'] }}"
                                data-provinsi="{{ $k['id_provinsi'] }}"
                                @if ($k['alasan'])
                                    disabled
                                    data-terkunci="1"
                                @endif
                                @if (count($k['revisi']))
                                    data-revisi="{{ implode(',', $k['revisi']) }}"
                                    data-isi="{{ route('input-data.create', ['kab' => $k['id']]) }}"
                                @endif
                                @if (count($k['sudah']))
                                    data-sudah="{{ implode(',', $k['sudah']) }}"
                                @endif
                                @selected((string) old('id_kab_kota', $isian['id_kab_kota'] ?? '') === (string) $k['id'])
                            >
                                {{ $k['nama'] }}
                                @if ($k['alasan'])
                                    ({{ $k['alasan'] }})
                                @elseif (count($k['revisi']))
                                    (revisi diminta: {{ implode(', ', $k['revisiNama']) }})
                                @elseif (count($k['sudah']))
                                    (pernah diajukan: {{ implode(', ', $k['sudahNama']) }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                @endif

                @error('id_kab_kota')
                    <p class="error">{{ $message }}</p>
                @enderror
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
            </div>

            {{-- Klasifikasi --}}
            <div class="field">
                <label for="klasifikasi_pemda">Klasifikasi Wilayah</label>

                <select
                    id="klasifikasi_pemda"
                    name="klasifikasi_pemda"
                    required
                    class="@error('klasifikasi_pemda') is-invalid @enderror"
                >
                    <option value="">Pilih Klasifikasi...</option>

                    @foreach ($klasifikasi as $k)
<<<<<<< HEAD
                        <option value="{{ $k }}" @selected(old('klasifikasi_pemda', $isian['klasifikasi_pemda'] ?? '') === $k)>{{ $k }}</option>
=======
                        <option
                            value="{{ $k }}"
                            @selected(old('klasifikasi_pemda', $isian['klasifikasi_pemda'] ?? '') === $k)
                        >
                            {{ $k }}
                        </option>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
                    @endforeach
                </select>

                @error('klasifikasi_pemda')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- 2. KETERANGAN PEMBANGUNAN DAN POTENSI LOKAL --}}
    {{-- ========================================================= --}}

    <section class="step">
        <h3>
            <span class="step-no">2</span>
            Keterangan Pembangunan &amp; Potensi Lokal
        </h3>

        {{-- Keterangan daerah --}}
        <fieldset class="field">
            <legend>Keterangan Daerah (Dapat Memilih Lebih Dari Satu)</legend>

            <div class="chips">
                @foreach ($keterangan as $key => $item)
                    <label class="chip">
<<<<<<< HEAD
                        <input type="checkbox" name="keterangan[]" value="{{ $key }}" @checked(in_array($key, old('keterangan', $isian['keterangan'] ?? [])))>
=======
                        <input
                            type="checkbox"
                            name="keterangan[]"
                            value="{{ $key }}"
                            @checked(in_array($key, old('keterangan', $isian['keterangan'] ?? [])))
                        >
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
                        <span>{{ $item['label'] }}</span>
                    </label>
                @endforeach
            </div>

            @error('keterangan.*')
                <p class="error">{{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Potensi unggulan --}}
        <fieldset class="field">
            <legend>Potensi Unggulan Daerah</legend>

            <div class="stack">
                @for ($i = 0; $i < 5; $i++)
<<<<<<< HEAD
                    <input type="text" name="potensi[]" maxlength="200" autocomplete="off"
                           aria-label="Potensi unggulan {{ $i + 1 }}"
                           placeholder="{{ $i < 4 ? 'Masukkan Potensi Unggulan Daerah' : 'Lainnya' }}"
                           value="{{ old('potensi.' . $i, $isian['potensi'][$i] ?? '') }}">
                @endfor
                <ul class="ac-list sektor-list" id="sektorList" role="listbox" aria-label="Saran sektor potensi" hidden></ul>
            </div>
            <p class="hint" id="potensiHint" aria-live="polite">Isi satu potensi pada setiap kolom. Ketik atau klik kolom untuk melihat saran sektor.</p>
            @error('potensi')<p class="error">{{ $message }}</p>@enderror
            @error('potensi.*')<p class="error">{{ $message }}</p>@enderror
        <script type="application/json" id="data-sektor">@json($sektor)</script>
=======
                    <input
                        type="text"
                        name="potensi[]"
                        maxlength="200"
                        autocomplete="off"
                        aria-label="Potensi unggulan {{ $i + 1 }}"
                        placeholder="{{ $i < 4 ? 'Masukkan Potensi Unggulan Daerah' : 'Lainnya' }}"
                        value="{{ old('potensi.' . $i, $isian['potensi'][$i] ?? '') }}"
                    >
                @endfor

                <ul
                    class="ac-list sektor-list"
                    id="sektorList"
                    role="listbox"
                    aria-label="Saran sektor potensi"
                    hidden
                ></ul>
            </div>

            <p class="hint" id="potensiHint" aria-live="polite">
                Isi satu potensi pada setiap kolom.
                Ketik atau klik kolom untuk melihat saran sektor.
            </p>

            @error('potensi')
                <p class="error">{{ $message }}</p>
            @enderror

            @error('potensi.*')
                <p class="error">{{ $message }}</p>
            @enderror

            <script type="application/json" id="data-sektor">@json($sektor)</script>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        </fieldset>

        {{-- Pemilihan moda --}}
        <fieldset class="field">
            <legend>Pemilihan Moda Transportasi</legend>

            <div class="chips">
                @foreach ($moda as $m)
                    <label class="chip">
<<<<<<< HEAD
                        <input type="radio" name="id_moda" value="{{ $m->id_moda }}" required @checked((string) old('id_moda', $isian['id_moda'] ?? '') === (string) $m->id_moda)>
=======
                        <input
                            type="radio"
                            name="id_moda"
                            value="{{ $m->id_moda }}"
                            required
                            @checked((string) old('id_moda', $isian['id_moda'] ?? '') === (string) $m->id_moda)
                        >
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
                        <span>{{ $m->nama_moda }}</span>
                    </label>
                @endforeach
            </div>
<<<<<<< HEAD
            <p class="hint is-warn" id="modaHint" hidden></p>
            @error('id_moda')<p class="error">{{ $message }}</p>@enderror
=======

            <p class="hint is-warn" id="modaHint" hidden></p>

            @error('id_moda')
                <p class="error">{{ $message }}</p>
            @enderror
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        </fieldset>

        {{-- ===================================================== --}}
        {{-- RUTE TUJUAN DAN KOMODITAS --}}
        {{-- ===================================================== --}}

        <div class="field field-wide">
<<<<<<< HEAD
            <label for="kebutuhan_konektivitas">Kebutuhan Konektivitas Regional (Hubungan Logistik) / Rute Tujuan</label>
            <div class="ac">
                <textarea id="kebutuhan_konektivitas" name="kebutuhan_konektivitas" rows="4" maxlength="2000"
                          placeholder="masukkan kebutuhan konektivitas daerah" autocomplete="off"
                          role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="acList"
                          class="@error('kebutuhan_konektivitas') is-invalid @enderror">{{ old('kebutuhan_konektivitas', $isian['kebutuhan_konektivitas'] ?? '') }}</textarea>
                <ul class="ac-list" id="acList" role="listbox" hidden></ul>
            </div>
            <p class="hint">Ketik nama kabupaten/kota, lalu klik saran yang muncul. Bisa memilih lebih dari satu; pisahkan dengan koma.</p>
            @error('kebutuhan_konektivitas')<p class="error">{{ $message }}</p>@enderror
            <script type="application/json" id="data-kabkota">@json($saran)</script>
=======

            <label for="tujuanCari">
                Kebutuhan Konektivitas Regional (Hubungan Logistik) / Rute Tujuan
            </label>

            @php
                $oldTujuan = collect(old('tujuan', []));

                $namaOld = $oldTujuan->isNotEmpty()
                    ? \App\Models\KabKota::whereIn(
                        'id_kab_kota',
                        $oldTujuan->pluck('id')->filter()
                    )->pluck('nama_kab_kota', 'id_kab_kota')
                    : collect();

                $tujuanAwal = $oldTujuan->isNotEmpty()
                    ? $oldTujuan->map(fn ($t) => [
                        'id' => (int) ($t['id'] ?? 0),
                        'nama' => $namaOld[$t['id'] ?? 0] ?? '',
                        'komoditas' => collect($t['komoditas'] ?? [])
                            ->map(fn ($k) => [
                                'nama' => $k['nama'] ?? '',
                                'jumlah' => $k['jumlah'] ?? '',
                                'satuan_data' => $k['satuan_data'] ?? '',
                            ])
                            ->values()
                            ->all(),
                    ])->values()->all()
                    : ($isian['tujuan'] ?? []);

                $galatTujuan = collect($errors->get('tujuan.*'))
                    ->flatten()
                    ->unique();
            @endphp

            {{-- Pencarian kabupaten/kota tujuan --}}
            <div class="ac">
                <input
                    type="text"
                    id="tujuanCari"
                    placeholder="Ketik nama kabupaten/kota tujuan, lalu klik hasilnya"
                    autocomplete="off"
                    role="combobox"
                    aria-autocomplete="list"
                    aria-expanded="false"
                    aria-controls="tujuanList"
                >

                <ul class="ac-list" id="tujuanList" role="listbox" hidden></ul>
            </div>

            {{-- Daftar tujuan --}}
            <div class="tujuan-rows" id="tujuanRows"></div>

            <p class="tujuan-kosong" id="tujuanKosong">
                Belum ada tujuan. Cari dan pilih kabupaten/kota di atas,
                lalu tambahkan komoditas, jumlah, dan satuannya.
            </p>

            {{-- Saran komoditas --}}
            <datalist id="komoditasList">
                @foreach ($komoditasSektor as $km)
                    <option value="{{ $km['nama'] }}"></option>
                @endforeach
            </datalist>

            <p class="hint">
                Satu daerah asal dapat memiliki lebih dari satu tujuan.
                Setiap tujuan dapat memiliki lebih dari satu komoditas.
                Setiap komoditas harus memiliki jumlah dan satuan data.
            </p>

            @error('tujuan')
                <p class="error">{{ $message }}</p>
            @enderror

            @foreach ($galatTujuan as $pesan)
                <p class="error">{{ $pesan }}</p>
            @endforeach

            <script type="application/json" id="data-kabkota">@json($saran)</script>
            <script type="application/json" id="data-tujuan-awal">@json($tujuanAwal)</script>
            <script type="application/json" id="data-komoditas">@json($komoditasSektor)</script>
            <script type="application/json" id="data-satuan">@json($pilihanSatuan)</script>

>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
        </div>
    </section>

    {{-- ========================================================= --}}
    {{-- 3. UPLOAD DOKUMEN --}}
    {{-- ========================================================= --}}

    <section class="upload-card">

        <div class="card-head">
            <h2>Upload Surat</h2>
            <p>Pastikan semua surat sudah terupload</p>
        </div>

        <h3>
            <span class="step-no">3</span>
            Upload Surat
        </h3>

        <div class="grid-2">

            @foreach ($dokumen as $field => $label)
                <div class="field">
<<<<<<< HEAD
                    <label for="{{ $field }}_name">{{ $label }}</label>
                    <div class="nomor-surat">
                        <label for="nomor_{{ $field }}" class="sub">Nomor surat <span class="wajib">*</span></label>
                        <input type="text" id="nomor_{{ $field }}" name="nomor[{{ $field }}]" value="{{ old('nomor.' . $field) }}" maxlength="100" required placeholder="Contoh: 045/123/DISHUB/2026" autocomplete="off" class="@error('nomor.' . $field) is-invalid @enderror">
                        @error('nomor.' . $field)<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="upload-box @error($field) is-invalid @enderror">
                        <input type="text" id="{{ $field }}_name" class="upload-name" data-for="{{ $field }}" readonly
                               placeholder="upload {{ strtolower($label) }}">
                        <input type="file" id="{{ $field }}" name="{{ $field }}" accept=".pdf,.doc,.docx" data-upload hidden>
                        <button type="button" class="btn-upload" data-target="{{ $field }}">Upload</button>
=======

                    <label for="{{ $field }}_name">
                        {{ $label }}
                    </label>

                    <div class="nomor-surat">
                        <label for="nomor_{{ $field }}" class="sub">
                            Nomor surat <span class="wajib">*</span>
                        </label>

                        <input
                            type="text"
                            id="nomor_{{ $field }}"
                            name="nomor[{{ $field }}]"
                            value="{{ old('nomor.' . $field) }}"
                            maxlength="100"
                            required
                            placeholder="Contoh: 045/123/DISHUB/2026"
                            autocomplete="off"
                            class="@error('nomor.' . $field) is-invalid @enderror"
                        >

                        @error('nomor.' . $field)
                            <p class="error">{{ $message }}</p>
                        @enderror
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
                    </div>

                    <div class="upload-box @error($field) is-invalid @enderror">

                        <input
                            type="text"
                            id="{{ $field }}_name"
                            class="upload-name"
                            data-for="{{ $field }}"
                            readonly
                            placeholder="upload {{ strtolower($label) }}"
                        >

                        <input
                            type="file"
                            id="{{ $field }}"
                            name="{{ $field }}"
                            accept=".pdf,.doc,.docx"
                            data-upload
                            hidden
                        >

                        <button
                            type="button"
                            class="btn-upload"
                            data-target="{{ $field }}"
                        >
                            Upload
                        </button>

                    </div>

                    @error($field)
                        <p class="error">{{ $message }}</p>
                    @enderror

                </div>
            @endforeach

        </div>

        <p class="hint">
            Format PDF, DOC, atau DOCX. Ukuran maksimal 5 MB per file.
        </p>

    </section>

    {{-- Tombol aksi --}}
    <div class="actions">
        <a class="btn btn-outline" href="{{ route('data-wilayah.index') }}">
            Batal
        </a>

        <button type="submit" class="btn btn-primary">
            Tambah Data
        </button>
    </div>

</form>

<<<<<<< HEAD
<style>
    /* Wilayah / provinsi / kab-kota: terisi otomatis dari akun, hanya baca */
=======
{{-- ============================================================= --}}
{{-- CSS --}}
{{-- ============================================================= --}}

<style>
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
    input.is-locked {
        background-color: #f1f5f9;
        color: #475569;
        cursor: default;
    }
<<<<<<< HEAD
</style>
@endsection
=======

    /* Kartu tujuan */
    .tujuan-rows {
        display: flex;
        flex-direction: column;
        gap: .7rem;
        margin-top: .8rem;
    }

    .tujuan-card {
        background: #f0f9ff;
        border-radius: .7rem;
        padding: .7rem .8rem;
    }

    .tujuan-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }

    .tujuan-card .nama {
        font-weight: 600;
        font-size: .95rem;
    }

    .tujuan-card .hapus-tujuan {
        border: 0;
        background: none;
        cursor: pointer;
        font-size: .8rem;
        color: #b91c1c;
    }

    /* Kolom komoditas */
    .kom-head,
    .kom-row {
        display: grid;
        grid-template-columns:
            minmax(0, 1.3fr)
            minmax(0, 1fr)
            minmax(0, .7fr)
            minmax(0, 1fr)
            2rem;
        gap: .5rem;
        align-items: center;
    }

    .kom-head {
        margin-top: .5rem;
        padding: 0 .1rem;
        font-size: .78rem;
        font-weight: 600;
        color: #475569;
    }

    .kom-row {
        margin-top: .35rem;
    }

    .kom-row input,
    .kom-row select {
        width: 100%;
        min-width: 0;
    }

    .kom-row .sektor {
        font-size: .82rem;
        color: #0369a1;
    }

    .kom-row .sektor.is-kosong {
        color: #94a3b8;
        font-style: italic;
    }

    .kom-row .hapus {
        border: 0;
        background: none;
        cursor: pointer;
        font-size: 1.2rem;
        line-height: 1;
    }

    .kom-row .hapus:disabled {
        opacity: .3;
        cursor: not-allowed;
    }

    .btn-tambah-kom {
        margin-top: .5rem;
        border: 1px dashed #0369a1;
        background: #fff;
        color: #0369a1;
        border-radius: .5rem;
        padding: .3rem .7rem;
        font-size: .82rem;
        cursor: pointer;
    }

    .tujuan-kosong {
        margin: .4rem 0 0;
        padding: .8rem;
        border: 1px dashed #cbd5e1;
        border-radius: .6rem;
        color: #64748b;
        font-size: .85rem;
    }

    @media (max-width: 640px) {
        .kom-head {
            display: none;
        }

        .kom-row {
            grid-template-columns: 1fr 1fr 2rem;
        }

        .kom-row input:first-child,
        .kom-row .sektor {
            grid-column: 1 / -1;
        }
    }
</style>

{{-- ============================================================= --}}
{{-- JAVASCRIPT RUTE TUJUAN --}}
{{-- ============================================================= --}}

<script>
(() => {
    const kab = JSON.parse(
        document.getElementById('data-kabkota').textContent
    );

    const awal = JSON.parse(
        document.getElementById('data-tujuan-awal').textContent || '[]'
    );

    const komoditas = JSON.parse(
        document.getElementById('data-komoditas').textContent || '[]'
    );

    const satuanData = JSON.parse(
        document.getElementById('data-satuan').textContent || '[]'
    );

    const cari = document.getElementById('tujuanCari');
    const list = document.getElementById('tujuanList');
    const rows = document.getElementById('tujuanRows');

    const asal = () => document.getElementById('id_kab_kota');

    const kosongKom = () => ({
        nama: '',
        jumlah: '',
        satuan_data: ''
    });

    /*
     * Struktur data:
     * ID tujuan => {
     *     nama,
     *     komoditas: [
     *         {nama, jumlah, satuan_data}
     *     ]
     * }
     */

    const dipilih = new Map(
        awal.map(t => [
            Number(t.id),
            {
                nama: t.nama,

                komoditas: (
                    t.komoditas && t.komoditas.length
                        ? t.komoditas
                        : [kosongKom()]
                ).map(k => ({
                    nama: k.nama ?? '',
                    jumlah: k.jumlah ?? '',
                    satuan_data: k.satuan_data ?? '',
                    satuanOtomatis: k.satuanOtomatis === true
                }))
            }
        ])
    );

    // Mencari sektor berdasarkan nama komoditas.
    function cariSektor(teks) {
        const t = (teks || '').trim().toLowerCase();

        if (t.length < 2) return null;

        const persis = komoditas.find(
            k => k.nama.toLowerCase() === t
        );

        if (persis) return persis.sektor;

        if (t.length < 3) return [];

        const hasil = new Set();

        komoditas.forEach(k => {
            const n = k.nama.toLowerCase();

            if (t.includes(n) || n.includes(t)) {
                k.sektor.forEach(s => hasil.add(s));
            }
        });

        return [...hasil];
    }

    // Menampilkan sektor.
    function tampilSektor(el, teks) {
        const s = cariSektor(teks);

        el.classList.remove('is-kosong');

        if (s === null) {
            el.textContent = '-';
            el.classList.add('is-kosong');
        } else if (s.length === 0) {
            el.textContent = 'Tidak dikenali';
            el.classList.add('is-kosong');
        } else {
            el.textContent = s.join(', ');
        }
    }

    // Rekomendasi berasal dari Master Komoditas yang diatur Super Admin.
    // Nama harus cocok persis; satuan tidak aktif atau nama ambigu tidak disarankan.
    function satuanRekomendasi(nama) {
        const key = String(nama || '').trim().toLowerCase().replace(/\s+/g, ' ');
        const item = komoditas.find(k =>
            String(k.nama || '').trim().toLowerCase().replace(/\s+/g, ' ') === key
        );
        const s = item?.satuan_default;
        return s ? (satuanData.find(x => String(x).toLowerCase() === String(s).toLowerCase()) || '') : '';
    }

    // Membuat dropdown satuan.
    function buatDropdownSatuan(k, i, m, namaTujuan) {
        const satuan = document.createElement('select');

        satuan.name =
            `tujuan[${i}][komoditas][${m}][satuan_data]`;

        satuan.required = true;

        satuan.setAttribute(
            'aria-label',
            `Satuan komoditas ${m + 1} ke ${namaTujuan}`
        );

        const placeholder = document.createElement('option');

        placeholder.value = '';
        placeholder.textContent = 'Pilih satuan';

        satuan.appendChild(placeholder);

        satuanData.forEach(s => {
            const option = document.createElement('option');

            option.value = s;
            option.textContent = s;

            if (k.satuan_data === s) {
                option.selected = true;
            }

            satuan.appendChild(option);
        });

        satuan.addEventListener('change', () => {
            k.satuan_data = satuan.value;
            k.satuanOtomatis = false;
        });

        return satuan;
    }

    // Render seluruh tujuan dan komoditas.
    function render(fokus) {
        rows.innerHTML = '';

        document.getElementById('tujuanKosong').hidden =
            dipilih.size > 0;

        let n = 0;
        let elFokus = null;

        dipilih.forEach((d, id) => {
            const i = n++;

            const card = document.createElement('div');
            card.className = 'tujuan-card';

            // Kepala kartu.
            const kepala = document.createElement('div');
            kepala.className = 'tujuan-card-head';

            const nama = document.createElement('span');
            nama.className = 'nama';
            nama.textContent = d.nama;

            const hid = document.createElement('input');
            hid.type = 'hidden';
            hid.name = `tujuan[${i}][id]`;
            hid.value = id;

            const hapusT = document.createElement('button');

            hapusT.type = 'button';
            hapusT.className = 'hapus-tujuan';
            hapusT.textContent = 'Hapus tujuan';

            hapusT.setAttribute(
                'aria-label',
                'Hapus tujuan ' + d.nama
            );

            hapusT.onclick = () => {
                dipilih.delete(id);
                render();
            };

            kepala.append(nama, hid, hapusT);
            card.appendChild(kepala);

            // Header kolom.
            const head = document.createElement('div');

            head.className = 'kom-head';
            head.setAttribute('aria-hidden', 'true');

            head.innerHTML = `
                <span>Komoditas</span>
                <span>Sektor</span>
                <span>Jumlah</span>
                <span>Satuan Data</span>
                <span></span>
            `;

            card.appendChild(head);

            // Daftar komoditas.
            d.komoditas.forEach((k, m) => {
                const row = document.createElement('div');
                row.className = 'kom-row';

                // Nama komoditas.
                const kom = document.createElement('input');

                kom.type = 'text';
                kom.name = `tujuan[${i}][komoditas][${m}][nama]`;
                kom.value = k.nama;
                kom.maxLength = 150;
                kom.required = true;
                kom.placeholder = 'Contoh: Udang';

                kom.setAttribute('list', 'komoditasList');
                kom.autocomplete = 'off';

                kom.setAttribute(
                    'aria-label',
                    `Komoditas ${m + 1} ke ${d.nama}`
                );

                // Sektor otomatis.
                const sek = document.createElement('span');

                sek.className = 'sektor';

                tampilSektor(sek, k.nama);

                kom.addEventListener('input', () => {
                    const rekomendasiLama = satuanRekomendasi(k.nama);
                    k.nama = kom.value;
                    tampilSektor(sek, kom.value);
                    const rekomendasiBaru = satuanRekomendasi(k.nama);
                    if (!k.satuan_data || (k.satuanOtomatis && k.satuan_data === rekomendasiLama)) {
                        k.satuan_data = rekomendasiBaru || '';
                        k.satuanOtomatis = Boolean(rekomendasiBaru);
                        satuan.value = k.satuan_data;
                    }
                });

                // Jumlah (type text + inputmode decimal supaya tampil sama dengan kolom lain)
                const jumlah = document.createElement('input');

                jumlah.type = 'text';
                jumlah.inputMode = 'decimal';
                jumlah.name =
                    `tujuan[${i}][komoditas][${m}][jumlah]`;

                jumlah.value = k.jumlah;
                jumlah.required = true;
                jumlah.maxLength = 15;
                jumlah.autocomplete = 'off';
                jumlah.placeholder = 'Contoh: 12.5';

                jumlah.setAttribute(
                    'aria-label',
                    `Jumlah komoditas ${m + 1} ke ${d.nama}`
                );

                // Hanya angka dan satu titik desimal; koma otomatis jadi titik.
                jumlah.addEventListener('input', () => {
                    let v = jumlah.value.replace(',', '.').replace(/[^0-9.]/g, '');
                    const p = v.indexOf('.');
                    if (p !== -1) {
                        v = v.slice(0, p + 1) + v.slice(p + 1).replace(/\./g, '');
                    }
                    if (v !== jumlah.value) jumlah.value = v;
                    k.jumlah = v;
                });

                // Satuan data.
                if (!k.satuan_data) {
                    const otomatis = satuanRekomendasi(k.nama);
                    if (otomatis) {
                        k.satuan_data = otomatis;
                        k.satuanOtomatis = true;
                    }
                }
                const satuan = buatDropdownSatuan(
                    k,
                    i,
                    m,
                    d.nama
                );

                // Hapus komoditas.
                const x = document.createElement('button');

                x.type = 'button';
                x.className = 'hapus';
                x.textContent = '×';

                x.setAttribute(
                    'aria-label',
                    `Hapus komoditas ${m + 1} ke ${d.nama}`
                );

                // Minimal satu komoditas per tujuan.
                x.disabled = d.komoditas.length === 1;

                x.onclick = () => {
                    d.komoditas.splice(m, 1);
                    render();
                };

                row.append(
                    kom,
                    sek,
                    jumlah,
                    satuan,
                    x
                );

                card.appendChild(row);

                if (
                    fokus === id &&
                    m === d.komoditas.length - 1
                ) {
                    elFokus = kom;
                }
            });

            // Tambah komoditas.
            const tambah = document.createElement('button');

            tambah.type = 'button';
            tambah.className = 'btn-tambah-kom';
            tambah.textContent = '+ Tambah komoditas';

            tambah.onclick = () => {
                d.komoditas.push(kosongKom());
                render(id);
            };

            card.appendChild(tambah);

            rows.appendChild(card);
        });

        if (elFokus) {
            elFokus.focus();
        }
    }

    function tutupSaran() {
        list.hidden = true;
        cari.setAttribute('aria-expanded', 'false');
    }

    // Pencarian kabupaten/kota.
    function tampilkan() {
        const q = cari.value.trim().toLowerCase();

        const idAsal = Number(asal()?.value || 0);

        const hasil = kab.filter(k =>
            Number(k.id) !== idAsal &&
            !dipilih.has(Number(k.id)) &&
            (
                !q ||
                (k.nama + ' ' + k.provinsi)
                    .toLowerCase()
                    .includes(q)
            )
        ).slice(0, 12);

        list.innerHTML = '';

        hasil.forEach(k => {
            const li = document.createElement('li');

            li.setAttribute('role', 'option');

            li.textContent = k.nama + ' — ' + k.provinsi;

            li.onmousedown = e => {
                e.preventDefault();

                dipilih.set(Number(k.id), {
                    nama: k.nama,
                    komoditas: [kosongKom()]
                });

                cari.value = '';

                // Tutup daftar saran setelah memilih (jangan dibuka lagi).
                tutupSaran();

                render(Number(k.id));
            };

            list.appendChild(li);
        });

        list.hidden = hasil.length === 0;

        cari.setAttribute(
            'aria-expanded',
            String(!list.hidden)
        );
    }

    cari.addEventListener('input', tampilkan);
    cari.addEventListener('focus', tampilkan);

    cari.addEventListener('blur', tutupSaran);

    // Esc menutup saran; klik di luar kolom pencarian juga menutup.
    cari.addEventListener('keydown', e => {
        if (e.key === 'Escape') tutupSaran();
    });

    document.addEventListener('mousedown', e => {
        if (e.target !== cari && !list.contains(e.target)) tutupSaran();
    });

    // Jika asal berubah, hapus tujuan yang sama dengan asal.
    asal()?.addEventListener('change', () => {
        dipilih.delete(Number(asal().value));
        render();
    });

    // Tampilkan data awal atau data revisi INTRAM.
    render();

})();
</script>

@endsection
>>>>>>> 0910d5c911ca44bd84ff0e009248ac8404333f54
