@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="stats">
    @foreach ($jumlah as $label => $nilai)
        <div class="stat">
            <span>{{ $label }}</span>
            <strong>{{ number_format($nilai, 0, ',', '.') }}</strong>
        </div>
    @endforeach
</div>
<div class="card">
    <div class="card-head">
        <h2>Mulai Input Data</h2>
        <p>Tambahkan data wilayah baru beserta surat pengajuan subsidi transportasi.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('input-data.create') }}">Input Data Wilayah Baru</a>
</div>
@endsection
