@extends('layouts.app')

@section('title', 'Atur Hak Akses')
@section('heading', 'Atur Hak Akses Role')
@section('search_action', route('admin.user.index'))
@section('search_placeholder', 'Cari user...')

@section('content')
<form class="box form" method="POST" action="{{ route('admin.role.hak-akses.update', $role->id_role) }}">
    @csrf
    @method('PUT')

    <div class="card-head">
        <h2>Hak akses role {{ $role->nama_role }}</h2>
        <p>Perubahan berlaku untuk seluruh akun dengan role ini ({{ $jumlahAkun }} akun). Super Admin selalu memiliki semua hak akses.</p>
    </div>

    <ul class="perm-list">
        @foreach ($katalog as $h)
            <li>
                <label class="check perm">
                    <input type="checkbox" name="hak_akses[]" value="{{ $h->id_hak_akses }}" @checked(in_array($h->id_hak_akses, old('hak_akses', $dipilih)))>
                    <span></span>
                    <em>{{ $h->nama }}<small>{{ $h->kode }}</small></em>
                </label>
            </li>
        @endforeach
    </ul>

    <p class="hint">Saat ini hak akses <strong>pemda.unggah</strong> menentukan siapa yang dapat membuka halaman Input Data. Hak akses lainnya disiapkan untuk fitur peninjauan dan surat CHR yang akan dibuat berikutnya.</p>

    <div class="actions">
        <a class="btn btn-outline" href="{{ route('admin.user.index') }}">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan Hak Akses</button>
    </div>
</form>
@endsection
