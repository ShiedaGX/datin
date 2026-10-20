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
