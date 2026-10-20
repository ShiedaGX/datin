@extends('layouts.app')

@section('title', 'Data User')
@section('heading', 'Data User Terdaftar')
@section('search_action', route('admin.user.index'))
@section('search_placeholder', 'Cari user...')

@section('content')
{{-- ===== Filter & aksi ===== --}}
<form class="box filter-bar" method="GET" action="{{ route('admin.user.index') }}">
    @if ($filter['q'] !== '')
        <input type="hidden" name="q" value="{{ $filter['q'] }}">
    @endif

    <label class="filter">
        <span>User:</span>
        <select name="role" data-autosubmit aria-label="Filter role">
            <option value="">Semua User</option>
            @foreach ($roles as $r)
                <option value="{{ $r->id_role }}" @selected($filter['role'] === (int) $r->id_role)>{{ $r->nama_role }}</option>
            @endforeach
        </select>
    </label>

    <noscript><button type="submit" class="btn btn-outline">Terapkan</button></noscript>

    <span class="count">Menampilkan {{ $users->count() }} dari {{ number_format($users->total(), 0, ',', '.') }} entri</span>
    @if ($adaFilter)
        <a class="reset" href="{{ route('admin.user.index') }}">Reset</a>
    @endif

    <a class="btn btn-new" href="{{ route('admin.user.create') }}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
        New User
    </a>
    <a class="btn btn-export" href="{{ route('admin.user.export', array_filter(['q' => $filter['q'], 'role' => $filter['role']])) }}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6M9 9h2"/></svg>
        Export Excel
    </a>
</form>

{{-- ===== Tabel ===== --}}
<section class="box box-table">
    <div class="table-box">
        <table class="table table-data table-users">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $u)
                    <tr>
                        <td data-label="No">{{ $users->firstItem() + $loop->index }}</td>
                        <td data-label="Username">
                            <strong>{{ $u->username }}</strong>
                            @if ($u->diverifikasi_at === null)
                                <span class="badge status-belum badge-mini">Belum aktif</span>
                            @endif
                        </td>
                        <td data-label="Role" class="soft">{{ $u->role->nama_role ?? '-' }}</td>
                        <td data-label="Email"><strong>{{ $u->email }}</strong></td>
                        <td class="td-aksi">
                            <div class="acts">
                                <a class="act act-edit" href="{{ route('admin.user.edit', $u->id_user) }}" title="Edit" aria-label="Edit {{ $u->username }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4z"/><path d="M13.5 6.5l4 4"/></svg>
                                </a>
                                @if ($u->id_user !== $sayaId)
                                    <form method="POST" action="{{ route('admin.user.destroy', $u->id_user) }}" data-confirm="Hapus akun {{ $u->username }}? Tindakan ini tidak dapat dibatalkan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="act act-del" title="Hapus" aria-label="Hapus {{ $u->username }}">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3"/></svg>
                                        </button>
                                    </form>
                                @endif
                                <a class="act act-view" href="{{ route('admin.user.show', $u->id_user) }}" title="Lihat detail" aria-label="Lihat detail {{ $u->username }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">Tidak ada user yang cocok.@if ($adaFilter) <a class="link-teal" href="{{ route('admin.user.index') }}">Reset filter</a>@endif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot">
        <p class="muted">
            @if ($users->total() > 0)
                Menampilkan {{ $users->firstItem() }}-{{ $users->lastItem() }} dari {{ number_format($users->total(), 0, ',', '.') }} data user
            @else
                Tidak ada data user
            @endif
        </p>

        @if ($users->lastPage() > 1)
            <nav class="pager-num" aria-label="Halaman">
                @if ($users->onFirstPage())
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&lsaquo;</span>
                @else
                    <a class="pg pg-nav" href="{{ $users->previousPageUrl() }}" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif

                @foreach ($halaman as $h)
                    @if ($h === '...')
                        <span class="gap">&hellip;</span>
                    @elseif ($h === $users->currentPage())
                        <span class="pg active" aria-current="page">{{ $h }}</span>
                    @else
                        <a class="pg" href="{{ $users->url($h) }}" aria-label="Halaman {{ $h }}">{{ $h }}</a>
                    @endif
                @endforeach

                @if ($users->hasMorePages())
                    <a class="pg pg-nav" href="{{ $users->nextPageUrl() }}" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&rsaquo;</span>
                @endif
            </nav>
        @endif
    </div>
</section>

{{-- ===== Role & hak akses ===== --}}
<section class="role-section">
    <div class="role-head">
        <h2>Role &amp; hak akses</h2>
        <p class="muted">Tetapkan role dan cakupan melalui panel kelola akun</p>
    </div>

    <div class="role-grid">
        @foreach ($kartu as $r)
            <article class="role-card">
                <header>
                    <i class="tile tile-teal">
                        @if ($r->nama_role === 'Pemda')
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10l9-6 9 6M5 10v8M9.5 10v8M14.5 10v8M19 10v8M3 20h18"/></svg>
                        @elseif ($r->nama_role === 'Moda')
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="14" rx="3"/><path d="M5 11h14M9 21l2-4M15 21l-2-4"/><circle cx="9" cy="14" r=".6"/><circle cx="15" cy="14" r=".6"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 13l2 2 4-4"/></svg>
                        @endif
                    </i>
                    <h3>User {{ $r->nama_role === 'Intram' ? 'INTRAM' : $r->nama_role }}</h3>
                </header>
                <p class="muted">Cakupan: {{ $cakupan[$r->nama_role] ?? '-' }}</p>
                <ul class="checks">
                    @forelse ($r->hakAkses as $h)
                        <li>{{ $h->nama }}</li>
                    @empty
                        <li class="kosong">Belum ada hak akses</li>
                    @endforelse
                </ul>
                <a class="link-teal role-atur" href="{{ route('admin.role.hak-akses.edit', $r->id_role) }}">Atur hak akses &rarr;</a>
            </article>
        @endforeach
    </div>
</section>
@endsection
