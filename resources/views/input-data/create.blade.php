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
            <div class="field">
                @if ($terkunci)
                    <label for="tampil_wilayah">Wilayah Regional</label>
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
            </div>

            <div class="field">
                @if ($terkunci)
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
                <ul class="ac-list sektor-list" id="sektorList" role="listbox" aria-label="Saran sektor potensi" hidden></ul>
            </div>
            <p class="hint" id="potensiHint" aria-live="polite">Isi satu potensi pada setiap kolom. Ketik atau klik kolom untuk melihat saran sektor.</p>
            @error('potensi')<p class="error">{{ $message }}</p>@enderror
            @error('potensi.*')<p class="error">{{ $message }}</p>@enderror
        <script type="application/json" id="data-sektor">@json($sektor)</script>
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

        {{-- Rute tujuan: tiap tujuan punya komoditas, sektor (otomatis), dan berat (ton) --}}
        <div class="field field-wide">
            <label for="tujuanCari">Kebutuhan Konektivitas Regional (Hubungan Logistik) / Rute Tujuan</label>

            @php
                $oldTujuan = collect(old('tujuan', []));
                $namaOld   = $oldTujuan->isNotEmpty()
                    ? \App\Models\KabKota::whereIn('id_kab_kota', $oldTujuan->pluck('id')->filter())->pluck('nama_kab_kota', 'id_kab_kota')
                    : collect();
                $tujuanAwal = $oldTujuan->isNotEmpty()
                    ? $oldTujuan->map(fn ($t) => [
                        'id'        => (int) ($t['id'] ?? 0),
                        'nama'      => $namaOld[$t['id'] ?? 0] ?? '',
                        'komoditas' => $t['komoditas'] ?? '',
                        'berat'     => $t['berat'] ?? '',
                    ])->values()->all()
                    : ($isian['tujuan'] ?? []);
                $galatTujuan = collect($errors->get('tujuan.*'))->flatten()->unique();
            @endphp

            <div class="ac">
                <input type="text" id="tujuanCari" placeholder="Ketik nama kabupaten/kota tujuan, lalu klik hasilnya" autocomplete="off"
                       role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="tujuanList">
                <ul class="ac-list" id="tujuanList" role="listbox" hidden></ul>
            </div>

            {{-- Judul kolom --}}
            <div class="tujuan-head" aria-hidden="true">
                <span>Kabupaten/Kota Tujuan</span>
                <span>Komoditas</span>
                <span>Sektor</span>
                <span>Berat (ton)</span>
                <span></span>
            </div>
            <div class="tujuan-rows" id="tujuanRows"></div>
            <p class="tujuan-kosong" id="tujuanKosong">Belum ada tujuan. Cari dan pilih kabupaten/kota di atas, lalu kolom komoditas, sektor, dan berat akan muncul di sini.</p>

            {{-- Saran komoditas dari master_komoditas --}}
            <datalist id="komoditasList">
                @foreach ($komoditasSektor as $km)
                    <option value="{{ $km['nama'] }}"></option>
                @endforeach
            </datalist>

            <p class="hint">Satu asal bisa memiliki lebih dari satu tujuan. Pilih komoditas dari saran agar sektornya terisi otomatis, lalu isi berat (ton).</p>
            @error('tujuan')<p class="error">{{ $message }}</p>@enderror
            @foreach ($galatTujuan as $pesan)<p class="error">{{ $pesan }}</p>@endforeach

            <script type="application/json" id="data-kabkota">@json($saran)</script>
            <script type="application/json" id="data-tujuan-awal">@json($tujuanAwal)</script>
            <script type="application/json" id="data-komoditas">@json($komoditasSektor)</script>
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

<style>
    /* Wilayah / provinsi / kab-kota: terisi otomatis dari akun, hanya baca */
    input.is-locked {
        background-color: #f1f5f9;
        color: #475569;
        cursor: default;
    }

    /* Rute tujuan: satu baris per tujuan (tujuan, komoditas, sektor, berat, hapus) */
    .tujuan-head, .tujuan-row { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(0,1.1fr) minmax(0,1.1fr) minmax(0,.7fr) 2rem; gap:.5rem; align-items:center; }
    .tujuan-head { margin-top:.8rem; padding:0 .7rem; font-size:.8rem; font-weight:600; color:#475569; }
    .tujuan-rows { display:flex; flex-direction:column; gap:.5rem; margin-top:.4rem; }
    .tujuan-row { background:#f0f9ff; border-radius:.6rem; padding:.5rem .7rem; }
    .tujuan-row .nama { font-weight:600; font-size:.9rem; }
    .tujuan-row input { width:100%; }
    .tujuan-row .sektor { font-size:.82rem; color:#0369a1; }
    .tujuan-row .sektor.is-kosong { color:#94a3b8; font-style:italic; }
    .tujuan-row .hapus { border:0; background:none; cursor:pointer; font-size:1.2rem; line-height:1; }
    .tujuan-kosong { margin:.4rem 0 0; padding:.8rem; border:1px dashed #cbd5e1; border-radius:.6rem; color:#64748b; font-size:.85rem; }
    @media (max-width:640px) {
        .tujuan-head { display:none; }
        .tujuan-row { grid-template-columns:1fr 1fr; }
        .tujuan-row .nama, .tujuan-row .sektor { grid-column:1 / -1; }
    }
</style>

<script>
(() => {
    const kab = JSON.parse(document.getElementById('data-kabkota').textContent);
    const awal = JSON.parse(document.getElementById('data-tujuan-awal').textContent || '[]');
    const komoditas = JSON.parse(document.getElementById('data-komoditas').textContent || '[]');
    const cari = document.getElementById('tujuanCari');
    const list = document.getElementById('tujuanList');
    const rows = document.getElementById('tujuanRows');
    const asal = () => document.getElementById('id_kab_kota');
    // id => {nama, komoditas, berat}
    const dipilih = new Map(awal.map(t => [Number(t.id), {nama: t.nama, komoditas: t.komoditas ?? '', berat: t.berat ?? ''}]));

    // Sektor dari teks komoditas: cocok persis dulu, lalu cocok sebagian
    function cariSektor(teks) {
        const t = (teks || '').trim().toLowerCase();
        if (t.length < 2) return null;                       // belum diketik
        const persis = komoditas.find(k => k.nama.toLowerCase() === t);
        if (persis) return persis.sektor;
        if (t.length < 3) return [];
        const hasil = new Set();
        komoditas.forEach(k => {
            const n = k.nama.toLowerCase();
            if (t.includes(n) || n.includes(t)) k.sektor.forEach(s => hasil.add(s));
        });
        return [...hasil];
    }

    function tampilSektor(el, teks) {
        const s = cariSektor(teks);
        el.classList.remove('is-kosong');
        if (s === null) { el.textContent = '-'; el.classList.add('is-kosong'); }
        else if (s.length === 0) { el.textContent = 'Tidak dikenali'; el.classList.add('is-kosong'); }
        else { el.textContent = s.join(', '); }
    }

    function render() {
        rows.innerHTML = '';
        document.getElementById('tujuanKosong').hidden = dipilih.size > 0;
        document.querySelector('.tujuan-head').hidden = dipilih.size === 0;
        let i = 0;
        dipilih.forEach((d, id) => {
            const n = i++;
            const row = document.createElement('div');
            row.className = 'tujuan-row';

            const nama = document.createElement('span');
            nama.className = 'nama'; nama.textContent = d.nama;

            const hid = document.createElement('input');
            hid.type = 'hidden'; hid.name = `tujuan[${n}][id]`; hid.value = id;

            const sek = document.createElement('span');
            sek.className = 'sektor';
            tampilSektor(sek, d.komoditas);

            const kom = document.createElement('input');
            kom.type = 'text'; kom.name = `tujuan[${n}][komoditas]`; kom.value = d.komoditas;
            kom.maxLength = 150; kom.required = true; kom.placeholder = 'Contoh: Udang';
            kom.setAttribute('list', 'komoditasList'); kom.autocomplete = 'off';
            kom.setAttribute('aria-label', 'Komoditas ke ' + d.nama);
            kom.addEventListener('input', () => { d.komoditas = kom.value; tampilSektor(sek, kom.value); });

            const brt = document.createElement('input');
            brt.type = 'number'; brt.name = `tujuan[${n}][berat]`; brt.value = d.berat;
            brt.min = '0.01'; brt.step = '0.01'; brt.required = true; brt.placeholder = 'Contoh: 12.5';
            brt.setAttribute('aria-label', 'Berat ke ' + d.nama + ' dalam ton');
            brt.addEventListener('input', () => { d.berat = brt.value; });

            const x = document.createElement('button');
            x.type = 'button'; x.className = 'hapus'; x.textContent = '×';
            x.setAttribute('aria-label', 'Hapus ' + d.nama);
            x.onclick = () => { dipilih.delete(id); render(); };

            // urutan kolom: tujuan | komoditas | sektor | berat | hapus
            row.append(nama, kom, sek, brt, x, hid);
            rows.appendChild(row);
        });
    }

    function tampilkan() {
        const q = cari.value.trim().toLowerCase();
        const idAsal = Number(asal()?.value || 0);
        const hasil = kab.filter(k => k.id !== idAsal && !dipilih.has(k.id) &&
            (!q || (k.nama + ' ' + k.provinsi).toLowerCase().includes(q))).slice(0, 12);

        list.innerHTML = '';
        hasil.forEach(k => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.textContent = k.nama + ' — ' + k.provinsi;
            li.onmousedown = e => {
                e.preventDefault();
                dipilih.set(k.id, {nama: k.nama, komoditas: '', berat: ''});
                cari.value = ''; render(); tampilkan();
            };
            list.appendChild(li);
        });
        list.hidden = hasil.length === 0;
        cari.setAttribute('aria-expanded', String(!list.hidden));
    }

    cari.addEventListener('input', tampilkan);
    cari.addEventListener('focus', tampilkan);
    cari.addEventListener('blur', () => { list.hidden = true; });
    // kab/kota asal berubah: buang tujuan yang sama dengan asal
    asal()?.addEventListener('change', () => { dipilih.delete(Number(asal().value)); render(); });
    render();
})();
</script>
@endsection