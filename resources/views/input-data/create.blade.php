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


    <div class="card-head">
        <h2>Sistem Formulir Digital Pengajuan Subsidi</h2>
        <p>Pastikan semua data wilayah, keterkaitan geografis, potensi lokal, serta sektor potensial diisi dengan akurat sesuai rujukan Bappenas.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            <strong>Data belum dapat disimpan.</strong> Periksa kembali isian yang ditandai merah.
        </div>
    @endif

    {{-- 1. Informasi Administrasi & Wilayah --}}
    <section class="step">
        <h3><span class="step-no">1</span> Informasi Administrasi &amp; Wilayah</h3>

        <div class="grid-2">
            <div class="field">
                <label for="id_wilayah">Wilayah Regional</label>
                <select id="id_wilayah" name="id_wilayah" required class="@error('id_wilayah') is-invalid @enderror">
                    <option value="">Pilih Wilayah Regional (Papua/Sulawesi/Maluku/etc)...</option>
                    @foreach ($wilayah as $w)
                        <option value="{{ $w->id_wilayah }}" @selected(old('id_wilayah', $isian['id_wilayah'] ?? null) == $w->id_wilayah)>{{ $w->nama_wilayah }}</option>
                    @endforeach
                </select>
                @error('id_wilayah')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="id_provinsi">Provinsi</label>
                <select id="id_provinsi" name="id_provinsi" required class="@error('id_provinsi') is-invalid @enderror">
                    <option value="">Pilih Provinsi...</option>
                    @foreach ($provinsi as $p)
                        <option value="{{ $p->id_provinsi }}" data-wilayah="{{ $p->id_wilayah }}" @selected(old('id_provinsi', $isian['id_provinsi'] ?? null) == $p->id_provinsi)>{{ $p->nama_provinsi }}</option>
                    @endforeach
                </select>
                @error('id_provinsi')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="id_kab_kota">Kabupaten / Kota</label>
                <select id="id_kab_kota" name="id_kab_kota" required class="@error('id_kab_kota') is-invalid @enderror">
                    <option value="">Pilih Kabupaten / Kota</option>
                    @foreach ($kabKota as $k)
                        <option value="{{ $k['id'] }}" data-provinsi="{{ $k['id_provinsi'] }}"
                                @if ($k['alasan']) disabled data-terkunci="1" @endif
                                @if (count($k['revisi'])) data-revisi="{{ implode(',', $k['revisi']) }}" data-isi="{{ route('input-data.create', ['kab' => $k['id']]) }}" @endif
                                @if (count($k['sudah'])) data-sudah="{{ implode(',', $k['sudah']) }}" @endif
                                @selected((string) old('id_kab_kota', $isian['id_kab_kota'] ?? '') === (string) $k['id'])>{{ $k['nama'] }}@if ($k['alasan']) ({{ $k['alasan'] }})@elseif (count($k['revisi'])) (revisi diminta: {{ implode(', ', $k['revisiNama']) }})@elseif (count($k['sudah'])) (sudah diajukan: {{ implode(', ', $k['sudahNama']) }})@endif</option>
                    @endforeach
                </select>
                @error('id_kab_kota')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <label for="klasifikasi_pemda">Klasifikasi Wilayah</label>
                <select id="klasifikasi_pemda" name="klasifikasi_pemda" required class="@error('klasifikasi_pemda') is-invalid @enderror">
                    <option value="">Pilih Klasifikasi...</option>
                    @foreach ($klasifikasi as $k)
                        <option value="{{ $k }}" @selected(old('klasifikasi_pemda', $isian['klasifikasi_pemda'] ?? '') === $k)>{{ $k }}</option>
                    @endforeach
                </select>
                @error('klasifikasi_pemda')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    {{-- 2. Keterangan Pembangunan & Potensi Lokal --}}
    <section class="step">
        <h3><span class="step-no">2</span> Keterangan Pembangunan &amp; Potensi Lokal</h3>

        <fieldset class="field">
            <legend>Keterangan Daerah (Dapat Memilih Lebih Dari Satu)</legend>
            <div class="chips">
                @foreach ($keterangan as $key => $item)
                    <label class="chip">
                        <input type="checkbox" name="keterangan[]" value="{{ $key }}" @checked(in_array($key, old('keterangan', $isian['keterangan'] ?? [])))>
                        <span>{{ $item['label'] }}</span>
                    </label>
                @endforeach
            </div>
            @error('keterangan.*')<p class="error">{{ $message }}</p>@enderror
        </fieldset>

        <fieldset class="field">
            <legend>Potensi Unggulan Daerah</legend>
            <div class="stack">
                @for ($i = 0; $i < 5; $i++)
                    <input type="text" name="potensi[]" maxlength="200" autocomplete="off"
                           aria-label="Potensi unggulan {{ $i + 1 }}"
                           placeholder="{{ $i < 4 ? 'Masukkan Potensi Unggulan Daerah' : 'Lainnya' }}"
                           value="{{ old('potensi.' . $i, $isian['potensi'][$i] ?? '') }}">
                @endfor
            </div>
            <p class="hint" id="potensiHint" aria-live="polite">Isi satu potensi pada setiap kolom.</p>
            @error('potensi')<p class="error">{{ $message }}</p>@enderror
            @error('potensi.*')<p class="error">{{ $message }}</p>@enderror
        </fieldset>

        <fieldset class="field">
            <legend>Pemilihan Moda Transportasi</legend>
            <div class="chips">
                @foreach ($moda as $m)
                    <label class="chip">
                        <input type="radio" name="id_moda" value="{{ $m->id_moda }}" required @checked((string) old('id_moda', $isian['id_moda'] ?? '') === (string) $m->id_moda)>
                        <span>{{ $m->nama_moda }}</span>
                    </label>
                @endforeach
            </div>
            <p class="hint is-warn" id="modaHint" hidden></p>
            @error('id_moda')<p class="error">{{ $message }}</p>@enderror
        </fieldset>

        <div class="field field-wide">
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
        </div>
    </section>

    {{-- 3. Upload Surat --}}
    <section class="upload-card">
        <div class="card-head">
            <h2>Upload Surat</h2>
            <p>Pastikan semua surat sudah terupload</p>
        </div>

        <h3><span class="step-no">3</span> Upload Surat</h3>

        <div class="grid-2">
            @foreach ($dokumen as $field => $label)
                <div class="field">
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
                    </div>
                    @error($field)<p class="error">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
        <p class="hint">Format PDF, DOC, atau DOCX. Ukuran maksimal 5 MB per file.</p>
    </section>

    <div class="actions">
        <a class="btn btn-outline" href="{{ route('data-wilayah.index') }}">Batal</a>
        <button type="submit" class="btn btn-primary">Tambah Data</button>
    </div>
</form>
@endsection
