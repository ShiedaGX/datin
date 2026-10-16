@extends('layouts.app')

@section('title', 'Detail User')
@section('heading', 'Detail Akun User')
@section('search_action', route('admin.user.index'))
@section('search_placeholder', 'Cari user...')

@section('content')
<section class="box">
    <div class="box-head row-between">
        <div>
            <h2>{{ $user->full_name }}</h2>
            <p class="muted">{{ $user->username }} &middot; {{ $user->email }}</p>
        </div>
        <div class="acts">
            <span class="badge {{ $user->diverifikasi_at ? 'status-final' : 'status-belum' }}">{{ $user->diverifikasi_at ? 'Aktif' : 'Belum aktif' }}</span>
            <a class="btn btn-soft" href="{{ route('admin.user.edit', $user->id_user) }}">Edit akun</a>
        </div>
    </div>

    <dl class="detail-grid">
        <div>
            <dt>Role</dt>
            <dd>{{ $user->role->nama_role ?? '-' }}</dd>
        </div>
        <div>
            <dt>Instansi / organisasi</dt>
            <dd>{{ $user->instansi ?: '-' }}</dd>
        </div>
        <div>
            <dt>Cakupan wilayah</dt>
            <dd>{{ $user->wilayah->nama_wilayah ?? '-' }}</dd>
        </div>
        <div>
            <dt>Cakupan moda</dt>
            <dd>{{ $user->moda->nama_moda ?? '-' }}</dd>
        </div>
        <div>
            <dt>Terdaftar</dt>
            <dd>{{ $user->created_at?->format('d-m-Y H:i') ?? '-' }}</dd>
        </div>
        <div>
            <dt>Diverifikasi</dt>
            <dd>{{ $user->diverifikasi_at?->format('d-m-Y H:i') ?? '-' }}</dd>
        </div>
        <div>
            <dt>Data wilayah dikelola</dt>
            <dd>{{ $jumlahData }}</dd>
        </div>
        <div>
            <dt>Dokumen diunggah</dt>
            <dd>{{ $jumlahDokumen }}</dd>
        </div>
        <div class="span-2">
            <dt>Hak akses</dt>
            <dd>
                @if ($user->isSuperAdmin())
                    <span class="muted">Super Admin memiliki seluruh hak akses.</span>
                @elseif ($user->role && $user->role->hakAkses->isNotEmpty())
                    <ul class="checks">
                        @foreach ($user->role->hakAkses as $h)
                            <li>{{ $h->nama }}</li>
                        @endforeach
                    </ul>
                @else
                    -
                @endif
            </dd>
        </div>
    </dl>
</section>

<a class="btn btn-outline" href="{{ route('admin.user.index') }}">&larr; Kembali ke daftar</a>
@endsection
