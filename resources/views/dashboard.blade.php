@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard Overview')

@section('content')
{{-- ===== Kartu ringkasan ===== --}}
<section class="kpis" aria-label="Ringkasan">
    <article class="kpi">
        <div class="kpi-top"><span>Total Provinsi</span>
            <i class="tile tile-blue"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21V4"/><path d="M5 4h13l-3 4.5 3 4.5H5"/></svg></i></div>
        <strong>{{ number_format($kpi['provinsi'], 0, ',', '.') }}</strong>
        <small>Terdaftar di sistem nasional</small>
    </article>
    <article class="kpi">
        <div class="kpi-top"><span>Total Wilayah Kerja</span>
            <i class="tile tile-teal"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/></svg></i></div>
        <strong>{{ number_format($kpi['wilayah'], 0, ',', '.') }}</strong>
        <small>Terdaftar di sistem nasional</small>
    </article>
    <article class="kpi">
        <div class="kpi-top"><span>Total Kabupaten / Kota</span>
            <i class="tile tile-amber"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.2 7-11.5A7 7 0 005 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg></i></div>
        <strong>{{ number_format($kpi['kab_kota'], 0, ',', '.') }}</strong>
        <small>Terdaftar di sistem nasional</small>
    </article>
    <article class="kpi">
        <div class="kpi-top"><span>Sektor Potensi</span>
            <i class="tile tile-rose"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M3 13h18"/></svg></i></div>
        <strong>{{ number_format($kpi['sektor'], 0, ',', '.') }}</strong>
        <small>Terdaftar di sistem nasional</small>
    </article>
</section>

<div class="dash-grid">
    {{-- ===== Distribusi per wilayah ===== --}}
    <section class="box">
        <div class="box-head row-between">
            <h2>Distribusi per Wilayah</h2>
            <span class="muted">Jumlah Kab/Kota</span>
        </div>
        @forelse ($distribusi as $d)
            <div class="bar-row">
                <span class="bar-name">{{ $d['nama'] }}</span>
                <div class="bar-track" role="img" aria-label="{{ $d['nama'] }}: {{ $d['jumlah'] }} kabupaten/kota">
                    <div class="bar-fill" style="width: {{ number_format($d['lebar'], 1, '.', '') }}%; background: {{ $d['warna'] }}"></div>
                </div>
                <strong class="bar-val">{{ $d['jumlah'] }}</strong>
            </div>
        @empty
            <p class="empty-note">Belum ada data wilayah.</p>
        @endforelse
    </section>

    {{-- ===== Klasifikasi wilayah ===== --}}
    <section class="box">
        <div class="box-head">
            <h2>Klasifikasi Wilayah</h2>
            <p class="muted">Proporsi klasifikasi pembangunan regional</p>
        </div>
        <div class="donut-wrap">
            <svg class="donut" viewBox="0 0 42 42" role="img" aria-label="Diagram klasifikasi wilayah">
                <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#eef2f6" stroke-width="6.2"/>
                @foreach ($donat as $d)
                    <circle cx="21" cy="21" r="15.9155" fill="none" stroke="{{ $d['warna'] }}" stroke-width="6.2"
                            stroke-dasharray="{{ number_format($d['arc'], 3, '.', '') }} {{ number_format(100 - $d['arc'], 3, '.', '') }}"
                            stroke-dashoffset="{{ number_format(-$d['awal'], 3, '.', '') }}"
                            transform="rotate(-90 21 21)"/>
                @endforeach
            </svg>
            <div class="donut-center">
                <strong>{{ number_format($totalPemda, 0, ',', '.') }}</strong>
                <span>TOTAL DAERAH</span>
            </div>
        </div>
        <ul class="legend">
            @foreach ($donat as $d)
                <li><i style="background: {{ $d['warna'] }}"></i>{{ $d['label'] }} ({{ $d['persen'] }}%)</li>
            @endforeach
        </ul>
    </section>
</div>

{{-- ===== Entri terbaru ===== --}}
<section class="box">
    <div class="box-head row-between">
        <div>
            <h2>Entri Data Wilayah Terbaru</h2>
            <p class="muted">Lima data daerah terakhir yang baru saja diinput ke sistem</p>
        </div>
        <a class="btn btn-soft" href="{{ route('data-wilayah.index') }}">
            Lihat Semua Data
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </div>

    <div class="table-box">
        <table class="table table-recent">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Provinsi</th>
                    <th>Kabupaten / Kota</th>
                    <th>Wilayah</th>
                    <th>Klasifikasi</th>
                    <th>Jumlah Tujuan</th>
                    <th>Jumlah Sektor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($terbaru as $row)
                    <tr>
                        <td data-label="No">{{ $loop->iteration }}</td>
                        <td data-label="Provinsi"><strong>{{ $row->kabKota->provinsi->nama_provinsi }}</strong></td>
                        <td data-label="Kabupaten / Kota">{{ $row->kabKota->nama_kab_kota }}</td>
                        <td data-label="Wilayah" class="soft">{{ $row->kabKota->wilayah->nama_wilayah }}</td>
                        <td data-label="Klasifikasi"><span class="badge badge-{{ \Illuminate\Support\Str::slug($row->klasifikasi_label) }}">{{ $row->klasifikasi_label }}</span></td>
                        <td data-label="Jumlah Tujuan"><strong>{{ $row->jumlah_tujuan !== null ? $row->jumlah_tujuan . ' Titik' : '-' }}</strong></td>
                        <td data-label="Jumlah Sektor"><strong>{{ $row->jumlah_sektor }} Sektor</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty">Belum ada data. <a class="link-teal" href="{{ route('input-data.create') }}">Input data pertama</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
