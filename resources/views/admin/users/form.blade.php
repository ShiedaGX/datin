@extends('layouts.app')

@section('title', $mode === 'edit' ? 'Edit User' : 'Tambah User')
@section('heading', $mode === 'edit' ? 'Edit Akun User' : 'Tambah Akun User')
@section('search_action', route('admin.user.index'))
@section('search_placeholder', 'Cari user...')

@section('content')
<form class="box form" id="formUser" method="POST" autocomplete="off"
      action="{{ $mode === 'edit' ? route('admin.user.update', $user->id_user) : route('admin.user.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="card-head">
        <h2>{{ $mode === 'edit' ? 'Edit akun ' . $user->username : 'Akun baru' }}</h2>
        <p>Pilih role untuk menentukan hak akses. Isian cakupan menyesuaikan role yang dipilih.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" role="alert">
            <strong>Data belum dapat disimpan.</strong> Periksa kembali isian yang ditandai merah.
        </div>
    @endif

    <section class="step">
        <h3><span class="step-no">1</span> Data akun</h3>
    {{-- ===== 1. Data pengguna ===== --}}
    <section class="step">
        <h3><span class="step-no">1</span> Data pengguna</h3>
        <div class="grid-2">
            <div class="field">
                <label for="full_name">Nama lengkap</label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" maxlength="150" required class="@error('full_name') is-invalid @enderror">
                @error('full_name')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" maxlength="50" required placeholder="huruf kecil, angka, . _ -" class="@error('username') is-invalid @enderror">
                @error('username')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="email">Alamat email</label>
                <input type="text" id="email" name="email" value="{{ old('email', $user->email) }}" maxlength="150" required placeholder="nama@instansi.go.id" inputmode="email" class="@error('email') is-invalid @enderror">
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="instansi">Instansi / organisasi <span class="wajib" id="wajibInstansi" hidden>*</span></label>
                <input type="text" id="instansi" name="instansi" value="{{ old('instansi', $user->instansi) }}" maxlength="150" data-wajib="instansi" placeholder="Contoh: Pemda Sumatera Selatan" class="@error('instansi') is-invalid @enderror">
                @error('instansi')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password">Kata sandi{{ $mode === 'edit' ? ' baru' : '' }}</label>
                <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" @if ($mode === 'create') required @endif
                       placeholder="{{ $mode === 'edit' ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}" class="@error('password') is-invalid @enderror">
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Konfirmasi kata sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" @if ($mode === 'create') required @endif placeholder="Ulangi kata sandi">
            </div>
        </div>
    </section>

        </div>
    </section>

    {{-- ===== 2. Role & cakupan ===== --}}
    <section class="step">
        <h3><span class="step-no">2</span> Role &amp; cakupan</h3>
        <div class="grid-2">
            <div class="field">
                <label for="id_role">Role</label>
                <select id="id_role" name="id_role" required class="@error('id_role') is-invalid @enderror">
                    <option value="">Pilih Role...</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->id_role }}" @selected((string) old('id_role', $user->id_role) === (string) $r->id_role)>{{ $r->nama_role }}</option>
                    @endforeach
                </select>
                @error('id_role')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field field-suggest">
                <label for="instansi">Instansi / organisasi <span class="wajib" id="wajibInstansi" hidden>*</span></label>
                <input type="text" id="instansi" name="instansi_teks" value="{{ old('instansi_teks', $user->instansi) }}" maxlength="150"
                       data-url="{{ route('admin.kabkota.saran') }}"
                       role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="instansiList"
                       placeholder="Ketik nama kabupaten/kota, lalu pilih saran" class="@error('id_kab_kota') is-invalid @enderror">
                <input type="hidden" id="id_kab_kota" name="id_kab_kota" value="{{ old('id_kab_kota', $user->id_kab_kota) }}">
                <ul id="instansiList" class="suggest" role="listbox" hidden></ul>
                @error('id_kab_kota')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field" data-butuh="wilayah" hidden>
                <label for="id_wilayah">Cakupan wilayah</label>
                <select id="id_wilayah" name="id_wilayah" class="@error('id_wilayah') is-invalid @enderror">
                    <option value="">Pilih Wilayah Regional...</option>
                    @foreach ($wilayah as $w)
                        <option value="{{ $w->id_wilayah }}" @selected((string) old('id_wilayah', $user->id_wilayah) === (string) $w->id_wilayah)>{{ $w->nama_wilayah }}</option>
                    @endforeach
                </select>
                @error('id_wilayah')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="field" data-butuh="moda" hidden>
                <label for="id_moda">Cakupan moda</label>
                <select id="id_moda" name="id_moda" class="@error('id_moda') is-invalid @enderror">
                    <option value="">Pilih Moda...</option>
                    @foreach ($moda as $m)
                        <option value="{{ $m->id_moda }}" @selected((string) old('id_moda', $user->id_moda) === (string) $m->id_moda)>{{ $m->nama_moda }}</option>
                    @endforeach
                </select>
                @error('id_moda')<p class="error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="role-info" id="roleInfo" hidden>
            <p class="role-info-title" id="roleInfoTitle">Hak akses role ini</p>
            <ul class="checks" id="roleInfoList"></ul>
            <p class="hint" id="roleInfoCatatan"></p>
        </div>
    </section>

    {{-- ===== 3. Akun login ===== --}}
    <section class="step">
        <h3><span class="step-no">3</span> Akun login</h3>
        <div class="grid-2">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" maxlength="50" required placeholder="huruf kecil, angka, . _ -" class="@error('username') is-invalid @enderror">
                @error('username')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field" aria-hidden="true"></div>

            <div class="field">
                <label for="password">Kata sandi{{ $mode === 'edit' ? ' baru' : '' }}</label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" @if ($mode === 'create') required @endif
                           placeholder="{{ $mode === 'edit' ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}" class="@error('password') is-invalid @enderror">
                    <button type="button" class="pw-toggle" data-target="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg class="ic-show" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="ic-hide" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M3 3l18 18"/><path d="M10.6 5.1A10.4 10.4 0 0112 5c6.4 0 10 7 10 7a17 17 0 01-3.2 4M6.7 6.7C3.8 8.6 2 12 2 12s3.6 7 10 7c1.7 0 3.2-.4 4.5-1.1"/><path d="M9.9 9.9a3 3 0 004.2 4.2"/></svg>
                    </button>
                </div>
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">Konfirmasi kata sandi</label>
                <div class="pw-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" @if ($mode === 'create') required @endif placeholder="Ulangi kata sandi">
                    <button type="button" class="pw-toggle" data-target="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false">
                        <svg class="ic-show" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="ic-hide" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M3 3l18 18"/><path d="M10.6 5.1A10.4 10.4 0 0112 5c6.4 0 10 7 10 7a17 17 0 01-3.2 4M6.7 6.7C3.8 8.6 2 12 2 12s3.6 7 10 7c1.7 0 3.2-.4 4.5-1.1"/><path d="M9.9 9.9a3 3 0 004.2 4.2"/></svg>
                    </button>
                </div>
                <p class="pw-match" id="pwMatch" role="status" aria-live="polite" hidden></p>
            </div>
        </div>

        <label class="check aktif-check">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $user->diverifikasi_at !== null))>
            <span></span>
            Akun aktif (terverifikasi). Akun yang tidak aktif tidak dapat masuk.
        </label>
    </section>

    <div class="actions">
        <a class="btn btn-outline" href="{{ route('admin.user.index') }}">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $mode === 'edit' ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
    </div>

    <script type="application/json" id="data-role">@json($infoRole)</script>
</form>
@endsection

<style>
    .field-suggest { position: relative; }
    .suggest {
        position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 30;
        margin: 0; padding: 6px; list-style: none;
        background: #fff; border: 1px solid #dbe3ee; border-radius: 12px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
        max-height: 280px; overflow-y: auto;
    }
    .suggest[hidden] { display: none; }
    .suggest li { padding: 9px 12px; border-radius: 8px; cursor: pointer; line-height: 1.3; }
    .suggest li strong { display: block; font-size: .95rem; color: #0f172a; font-weight: 600; }
    .suggest li small { color: #64748b; font-size: .8rem; }
    .suggest li:hover, .suggest li.is-active { background: #eef3fb; }
    #instansi:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }

    /* Tombol lihat/sembunyikan kata sandi (ikon di dalam kolom input) */
    .pw-wrap { position: relative; display: block; width: 100%; }
    .field .pw-wrap input {
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding-right: 48px !important;
    }
    .pw-toggle {
        position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; padding: 0; margin: 0;
        background: transparent; border: 0; border-radius: 8px;
        color: #64748b; cursor: pointer; line-height: 1;
    }
    .pw-toggle:hover { background: #eef3fb; color: #0f172a; }
    .pw-toggle:focus-visible { outline: 2px solid #1e3a8a; outline-offset: 1px; }
    .pw-toggle [hidden] { display: none; }
    .pw-toggle svg { display: block; pointer-events: none; }

    /* Pengecekan kecocokan kata sandi (real time) */
    .pw-match { margin: 6px 0 0; font-size: .85rem; line-height: 1.3; }
    .pw-match[hidden] { display: none; }
    .pw-match.ok  { color: #15803d; }
    .pw-match.bad { color: #b91c1c; }
    .pw-wrap input.is-match-bad { border-color: #dc2626 !important; }
    .pw-wrap input.is-match-ok  { border-color: #16a34a !important; }
</style>

<script>
/* ---------- Lihat / sembunyikan kata sandi ---------- */
document.querySelectorAll('.pw-toggle').forEach((btn) => {
    btn.addEventListener('click', () => {
        const field = document.getElementById(btn.dataset.target);
        if (!field) return;
        const tampil = field.type === 'password';
        field.type = tampil ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(tampil));
        btn.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        btn.querySelector('.ic-show').hidden = tampil;
        btn.querySelector('.ic-hide').hidden = !tampil;
    });
});
</script>

<script>
/* ---------- Cek kecocokan kata sandi secara real time ---------- */
(function () {
    const pw    = document.getElementById('password');
    const conf  = document.getElementById('password_confirmation');
    const pesan = document.getElementById('pwMatch');
    if (!pw || !conf || !pesan) return;

    const cek = () => {
        conf.classList.remove('is-match-ok', 'is-match-bad');
        pesan.classList.remove('ok', 'bad');

        // belum ada yang diketik di konfirmasi: tidak menampilkan apa-apa
        if (conf.value === '') { pesan.hidden = true; return; }

        const cocok = pw.value === conf.value;
        pesan.hidden = false;
        pesan.textContent = cocok ? 'Kata sandi cocok.' : 'Kata sandi belum sama.';
        pesan.classList.add(cocok ? 'ok' : 'bad');
        conf.classList.add(cocok ? 'is-match-ok' : 'is-match-bad');
    };

    pw.addEventListener('input', cek);
    conf.addEventListener('input', cek);

    // cegah submit kalau tidak sama (validasi server tetap jalan sebagai cadangan)
    const form = document.getElementById('formUser');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (pw.value !== conf.value) {
                e.preventDefault();
                cek();
                conf.focus();
            }
        });
    }
})();
</script>

<script>
(function () {
    const roleSel = document.getElementById('id_role');
    const input   = document.getElementById('instansi');
    const idKab   = document.getElementById('id_kab_kota');
    const list    = document.getElementById('instansiList');
    const bintang = document.getElementById('wajibInstansi');
    if (!input || !list) return;

    const placeholderAwal = input.getAttribute('placeholder');
    let items = [], active = -1, timer = null, ctrl = null;

    /* ---------- Aturan role: instansi wajib untuk Pemda, nonaktif untuk role lain ---------- */
    const adalahPemda = () => {
        if (!roleSel) return true;
        const opt = roleSel.options[roleSel.selectedIndex];
        return !!opt && opt.value !== '' && opt.text.trim().toLowerCase() === 'pemda';
    };

    const terapkanRole = () => {
        const pemda = adalahPemda();

        input.disabled = !pemda;
        input.required = pemda;
        if (bintang) bintang.hidden = !pemda;

        if (pemda) {
            input.placeholder = placeholderAwal;
        } else {
            input.value = '';
            if (idKab) idKab.value = '';
            input.placeholder = 'Hanya untuk role Pemda';
            items = [];
            tutup();
        }
    };

    /* ---------- Autocomplete ---------- */
    function tutup() {
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        active = -1;
    }

    const setAktif = (i) => {
        [...list.children].forEach((li, n) => li.classList.toggle('is-active', n === i));
        active = i;
        if (i >= 0) list.children[i].scrollIntoView({ block: 'nearest' });
    };

    const pilih = (i) => {
        if (!items[i]) return;
        input.value = items[i].value;
        if (idKab) idKab.value = items[i].id_kab_kota ?? '';

        // otomatis pilih "Cakupan wilayah" sesuai kab/kota yang dipilih
        const wil = document.getElementById('id_wilayah');
        if (wil && items[i].id_wilayah) {
            wil.value = String(items[i].id_wilayah);
            wil.dispatchEvent(new Event('change', { bubbles: true }));
        }

        tutup();
    };

    const render = () => {
        list.innerHTML = '';
        if (!items.length) return tutup();

        items.forEach((it, i) => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            const b = document.createElement('strong'); b.textContent = it.value;
            const s = document.createElement('small');  s.textContent = it.provinsi;
            li.append(b, s);
            // mousedown (bukan click) supaya jalan sebelum input kehilangan fokus
            li.addEventListener('mousedown', (e) => { e.preventDefault(); pilih(i); });
            list.appendChild(li);
        });
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        active = -1;
    };

    const cari = async () => {
        if (input.disabled) return;
        const q = input.value.trim();
        if (q.length < 2) { items = []; return tutup(); }

        if (ctrl) ctrl.abort();
        ctrl = new AbortController();
        try {
            const res = await fetch(input.dataset.url + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: ctrl.signal,
            });
            if (!res.ok) return tutup();
            items = await res.json();
            render();
        } catch (e) { /* dibatalkan / gagal: abaikan */ }
    };

    input.addEventListener('input', () => {
        // teks diketik manual: pilihan kab/kota sebelumnya tidak berlaku lagi
        if (idKab) idKab.value = '';
        clearTimeout(timer);
        timer = setTimeout(cari, 250);
    });
    input.addEventListener('focus', () => { if (input.value.trim().length >= 2 && !items.length) cari(); });
    input.addEventListener('blur', () => setTimeout(tutup, 120));

    input.addEventListener('keydown', (e) => {
        if (list.hidden) return;
        if (e.key === 'ArrowDown')      { e.preventDefault(); setAktif((active + 1) % items.length); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); setAktif((active - 1 + items.length) % items.length); }
        else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); pilih(active); }
        else if (e.key === 'Escape')    { tutup(); }
    });

    if (roleSel) roleSel.addEventListener('change', terapkanRole);
    terapkanRole();   // saat halaman dibuka (create, edit, atau balik dari error validasi)
})();
</script>
@endsection
