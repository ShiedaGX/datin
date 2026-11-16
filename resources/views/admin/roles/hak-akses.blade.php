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

    <p class="hint">Hak akses yang sudah berlaku: <strong>pemda.unggah</strong> menentukan siapa yang dapat membuka halaman Input Data, <strong>laporan.tinjau</strong> menentukan akses ke halaman Permohonan (khusus role Moda), <strong>chr.tinjau</strong> ke halaman Approval (khusus role Intram), <strong>cakupan.lihat_unggahan</strong> ke halaman Hasil Unggahan, dan <strong>rekomendasi_teknis.unggah</strong> untuk mengunggah Surat Rekomendasi Teknis.</p>

    <div class="actions">
        <a class="btn btn-outline" href="{{ route('admin.user.index') }}">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan Hak Akses</button>
    </div>
</form>
@endsection
