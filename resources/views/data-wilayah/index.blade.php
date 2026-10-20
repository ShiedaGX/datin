@extends('layouts.app')

@section('title', 'Data Wilayah')
@section('heading', 'Data Pengajuan Wilayah Terdaftar')

@section('content')
{{-- ===== Filter ===== --}}
<form class="box filter-bar" method="GET" action="{{ route('data-wilayah.index') }}">
    @if ($filter['q'] !== '')
        <input type="hidden" name="q" value="{{ $filter['q'] }}">
    @endif

    <label class="filter">
        <span>Wilayah:</span>
        <select name="wilayah" data-autosubmit aria-label="Filter wilayah">
            <option value="">Semua Wilayah</option>
            @foreach ($wilayah as $w)
                <option value="{{ $w->id_wilayah }}" @selected($filter['wilayah'] === (int) $w->id_wilayah)>{{ $w->nama_wilayah }}</option>
            @endforeach
        </select>
    </label>

    <label class="filter">
        <span>Klasifikasi:</span>
        <select name="klasifikasi" data-autosubmit aria-label="Filter klasifikasi">
            <option value="">Semua Klasifikasi</option>
            @foreach ($klasifikasi as $nilai => $label)
                <option value="{{ $nilai }}" @selected($filter['klasifikasi'] === $nilai)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <noscript><button type="submit" class="btn btn-outline">Terapkan</button></noscript>

    <span class="count">Menampilkan {{ $data->count() }} dari {{ number_format($data->total(), 0, ',', '.') }} entri</span>
    @if ($adaFilter)
        <a class="reset" href="{{ route('data-wilayah.index') }}">Reset</a>
    @endif

    <a class="btn btn-export" href="{{ route('data-wilayah.export', array_filter(['q' => $filter['q'], 'wilayah' => $filter['wilayah'], 'klasifikasi' => $filter['klasifikasi']])) }}">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6M9 9h2"/></svg>
        Export Excel
    </a>
</form>

{{-- ===== Tabel ===== --}}
<section class="box box-table">
    <div class="table-box">
        <table class="table table-data">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Provinsi</th>
                    <th>Wilayah</th>
                    <th>Kabupaten / Kota</th>
                    <th>Klasifikasi Wilayah</th>
                    <th>Potensi Unggulan Daerah</th>
                    <th class="num">Jml Tujuan</th>
                    <th class="num">Jml Sektor</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td data-label="No">{{ $data->firstItem() + $loop->index }}</td>
                        <td data-label="Provinsi"><strong>{{ $row->kabKota->provinsi->nama_provinsi }}</strong></td>
                        <td data-label="Wilayah" class="soft">{{ $row->kabKota->wilayah->nama_wilayah }}</td>
                        <td data-label="Kabupaten / Kota"><strong>{{ $row->kabKota->nama_kab_kota }}</strong></td>
                        <td data-label="Klasifikasi Wilayah"><span class="badge badge-{{ \Illuminate\Support\Str::slug($row->klasifikasi_label) }}">{{ $row->klasifikasi_label }}</span></td>
                        <td data-label="Potensi Unggulan Daerah" class="soft"><div class="trunc" title="{{ $row->potensi_ringkas }}">{{ $row->potensi_ringkas !== '' ? $row->potensi_ringkas : '-' }}</div></td>
                        <td data-label="Jml Tujuan" class="num"><strong>{{ $row->jumlah_tujuan ?? '-' }}</strong></td>
                        <td data-label="Jml Sektor" class="num"><strong>{{ $row->jumlah_sektor }}</strong></td>
                        <td data-label="Status"><span class="badge {{ $row->status_kelas }}">{{ $row->status_label }}</span></td>
                        <td class="td-aksi">
                            <div class="acts">
                                @if ($row->bisaDiubahOleh(auth()->user()))
                                    <form method="POST" action="{{ route('data-wilayah.destroy', $row->id_pemda) }}" data-confirm="Hapus pengajuan {{ $row->kabKota->nama_kab_kota }}? Dokumen yang sudah diunggah akan dihapus. Data wilayah tetap tersimpan.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="act act-del" title="Hapus pengajuan" aria-label="Hapus pengajuan {{ $row->kabKota->nama_kab_kota }}">
                                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3"/></svg>
                                        </button>
                                    </form>
                                @endif
                                <a class="act act-view" href="{{ route('data-wilayah.show', $row->id_pemda) }}" title="Lihat detail" aria-label="Lihat detail {{ $row->kabKota->nama_kab_kota }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="empty">Tidak ada data yang cocok.@if ($adaFilter) <a class="link-teal" href="{{ route('data-wilayah.index') }}">Reset filter</a>@endif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-foot">
        <p class="muted">
            @if ($data->total() > 0)
                Menampilkan {{ $data->firstItem() }}-{{ $data->lastItem() }} dari {{ number_format($data->total(), 0, ',', '.') }} data wilayah
            @else
                Tidak ada data wilayah
            @endif
        </p>

        @if ($data->lastPage() > 1)
            <nav class="pager-num" aria-label="Halaman">
                @if ($data->onFirstPage())
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&lsaquo;</span>
                @else
                    <a class="pg pg-nav" href="{{ $data->previousPageUrl() }}" aria-label="Halaman sebelumnya">&lsaquo;</a>
                @endif

                @foreach ($halaman as $h)
                    @if ($h === '...')
                        <span class="gap">&hellip;</span>
                    @elseif ($h === $data->currentPage())
                        <span class="pg active" aria-current="page">{{ $h }}</span>
                    @else
                        <a class="pg" href="{{ $data->url($h) }}" aria-label="Halaman {{ $h }}">{{ $h }}</a>
                    @endif
                @endforeach

                @if ($data->hasMorePages())
                    <a class="pg pg-nav" href="{{ $data->nextPageUrl() }}" aria-label="Halaman berikutnya">&rsaquo;</a>
                @else
                    <span class="pg pg-nav is-disabled" aria-hidden="true">&rsaquo;</span>
                @endif
            </nav>
        @endif
    </div>
</section>
@endsection
