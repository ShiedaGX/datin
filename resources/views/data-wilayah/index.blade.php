@extends('layouts.app')

@section('title', 'Data Wilayah')
@section('heading', 'Data Wilayah')

@section('content')
<div class="card">
    <div class="card-head row-between">
        <div>
            <h2>Daftar Data Wilayah</h2>
            <p>@if (request('q'))Hasil pencarian untuk "{{ request('q') }}"@else Seluruh data kabupaten/kota yang sudah tersimpan.@endif</p>
        </div>
        <a class="btn btn-primary" href="{{ route('input-data.create') }}">+ Input Data</a>
    </div>

    <div class="table-wrap">
        <table class="table table-long">
            <thead>
                <tr>
                    <th>Kabupaten / Kota</th>
                    <th>Provinsi</th>
                    <th>Wilayah</th>
                    <th>Klasifikasi</th>
                    <th>Keterangan</th>
                    <th>Potensi Unggulan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td data-label="Kabupaten / Kota"><strong>{{ $row->kabKota->nama_kab_kota }}</strong></td>
                        <td data-label="Provinsi">{{ $row->kabKota->provinsi->nama_provinsi }}</td>
                        <td data-label="Wilayah">{{ $row->kabKota->wilayah->nama_wilayah }}</td>
                        <td data-label="Klasifikasi">{{ $row->klasifikasi_pemda ?? '-' }}</td>
                        <td data-label="Keterangan">{{ $row->keterangan_pemda ?? '-' }}</td>
                        <td data-label="Potensi Unggulan">{{ $row->potensi_pemda ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pager">
        @if ($data->previousPageUrl())<a class="btn btn-outline" href="{{ $data->previousPageUrl() }}">&larr; Sebelumnya</a>@endif
        @if ($data->nextPageUrl())<a class="btn btn-outline" href="{{ $data->nextPageUrl() }}">Berikutnya &rarr;</a>@endif
    </div>
</div>
@endsection
