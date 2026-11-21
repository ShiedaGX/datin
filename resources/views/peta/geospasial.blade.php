@extends('layouts.app')

@section('title', 'Peta Geospasial')
@section('heading', 'Peta Geospasial')
@section('subheading', 'Sebaran fasilitas transportasi dan analisis konektivitas regional')

@section('content')

<style>
    .gs-note {
        padding: 12px 16px;
        background: #fff8e7;
        color: #765417;
        border: 1px solid #f0d89b;
        border-radius: 8px;
        margin-bottom: 14px;
        font-size: 13px;
        line-height: 1.6;
    }

    .gs-layout {
        display: grid;
        grid-template-columns: 300px minmax(0, 1fr);
        height: min(780px, calc(100vh - 175px));
        min-height: 620px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        overflow: hidden;
    }

    .gs-sidebar {
        background: #15263f;
        color: #fff;
        overflow: auto;
        padding: 20px;
    }

    .gs-sidebar h2 {
        font-size: 20px;
        margin: 0 0 8px;
    }

    .gs-sidebar p {
        font-size: 13px;
        color: #c2d1e5;
        line-height: 1.55;
        margin: 0 0 16px;
    }

    .gs-layer {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #40516b;
        cursor: pointer;
    }

    .gs-layer input {
        margin-top: 4px;
        accent-color: #65d3ff;
    }

    .gs-layer strong {
        display: block;
        font-size: 13px;
    }

    .gs-layer small {
        display: block;
        color: #b5c6da;
        margin-top: 3px;
        line-height: 1.5;
    }

    .gs-field {
        margin: 17px 0;
    }

    .gs-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 13px;
        font-weight: 600;
    }

    .gs-field select {
        width: 100%;
        padding: 9px;
        background: #253b5c;
        color: #fff;
        border: 1px solid #61728b;
        border-radius: 6px;
        font-size: 13px;
    }

    .gs-summary {
        font-size: 13px;
        line-height: 1.7;
        padding: 12px;
        background: #253b5c;
        border-radius: 6px;
        margin: 14px 0;
    }

    .gs-stat-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 5px;
    }

    .gs-stat-row span {
        color: #c2d1e5;
    }

    .gs-stat-row strong {
        color: #fff;
    }

    .gs-stat-note {
        margin-top: 10px;
        font-size: 11px;
        color: #a9e5c6;
        line-height: 1.5;
    }

    .gs-detail {
        color: #15263f;
        background: #fff;
        border-radius: 7px;
        padding: 14px;
        font-size: 13px;
        max-height: 300px;
        overflow: auto;
        overflow-wrap: anywhere;
        line-height: 1.55;
    }

    .gs-detail h3 {
        margin: 0 0 8px;
        font-size: 17px;
    }

    .gs-detail dl {
        margin: 0;
    }

    .gs-detail dt {
        font-weight: 700;
        margin-top: 10px;
    }

    .gs-detail dd {
        margin: 3px 0;
        color: #34455e;
    }

    .gs-mapbox {
        position: relative;
        background: #cbdde7;
        overflow: hidden;
    }

    #gs-map {
        display: block;
        width: 100%;
        height: 100%;
    }

    .gs-land {
        fill: #efe4c2;
        stroke: #b7aa89;
        stroke-width: .6;
        vector-effect: non-scaling-stroke;
    }

    .gs-connection {
        fill: none;
        stroke: #287fb3;
        stroke-opacity: .32;
        stroke-width: 1.4;
        vector-effect: non-scaling-stroke;
        cursor: pointer;
    }

    .gs-connection.gs-facility-link {
        stroke: #e18c36;
        stroke-dasharray: 5 3;
        stroke-opacity: .44;
    }

    .gs-connection.gs-highlight {
        stroke: #d96d06;
        stroke-width: 3;
        stroke-opacity: 1;
    }

    .gs-connection:hover {
        stroke-opacity: 1;
        stroke-width: 3;
    }

    .gs-recommend {
        stroke: #d37a1a;
        stroke-width: 2.5;
        stroke-dasharray: 6 4;
        vector-effect: non-scaling-stroke;
    }

    .gs-recommend.gs-service {
        stroke: #173d6a;
        stroke-width: 3;
        stroke-dasharray: none;
    }

    .gs-route-point {
        stroke: white;
        stroke-width: 1.4;
        vector-effect: non-scaling-stroke;
    }

    .gs-rail {
        fill: none;
        stroke: #48566b;
        stroke-width: 1;
        stroke-opacity: .55;
        stroke-dasharray: 3 3;
        vector-effect: non-scaling-stroke;
    }

    .gs-point {
        stroke: #fff;
        stroke-width: 1;
        cursor: pointer;
        vector-effect: non-scaling-stroke;
    }

    .gs-legend {
        position: absolute;
        bottom: 14px;
        left: 14px;
        background: rgba(255, 254, 242, .94);
        color: #1d334b;
        padding: 10px 12px;
        border-radius: 7px;
        font-size: 12px;
        line-height: 1.75;
        pointer-events: none;
        max-width: 245px;
    }

    .gs-legend span {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }

    .gs-zoom {
        position: absolute;
        right: 15px;
        bottom: 15px;
        display: grid;
        gap: 5px;
    }

    .gs-zoom button {
        width: 36px;
        height: 36px;
        background: #fff;
        border: 1px solid #a4b6c7;
        border-radius: 6px;
        font-size: 20px;
        cursor: pointer;
    }

    .gs-alert {
        position: absolute;
        top: 14px;
        left: 14px;
        right: 14px;
        background: #fffbea;
        padding: 13px;
        border-radius: 8px;
        color: #684f1a;
        font-size: 13px;
        z-index: 2;
    }

    .gs-warning {
        color: #dc2626;
        font-weight: 700;
    }

    @media (max-width: 920px) {
        .gs-layout {
            grid-template-columns: 1fr;
            height: auto;
        }

        .gs-sidebar {
            max-height: 620px;
        }

        .gs-mapbox {
            height: 65vh;
            min-height: 420px;
        }
    }
</style>


<div class="gs-note">
    <strong>Analisis spasial awal.</strong>

    Peta menggunakan fasilitas Kemenhub sebagai referensi utama
    dan GeoJSON Tim Statistika sebagai sumber tambahan.

    Kandidat fasilitas duplikat disaring dari visualisasi,
    tetapi seluruh data aslinya tetap tersimpan dalam database.

    Rekomendasi fasilitas dihitung berdasarkan jarak garis lurus,
    bukan bukti ketersediaan trayek atau layanan transportasi aktual.
</div>


<div class="gs-layout">

    {{-- SIDEBAR --}}

    <aside class="gs-sidebar">

        <h2>Fasilitas Transportasi</h2>

        <p>
            Sebaran bandar udara, terminal, pelabuhan,
            dan stasiun kereta api di Indonesia.
        </p>

        <p>
            <a
                href="{{ route('peta.index') }}"
                style="color:#b8e1ff"
            >
                Lihat Peta Konektivitas Pemda →
            </a>
        </p>

        @if(auth()->user()->isSuperAdmin())
            <p>
                <a
                    href="{{ route('fasilitas.index') }}"
                    style="color:#b8e1ff"
                >
                    Kelola Data Fasilitas →
                </a>
            </p>
        @endif


        {{-- STATISTIK FASILITAS --}}

        <div class="gs-summary">

            <strong>Referensi Fasilitas</strong>

            <div class="gs-stat-row">
                <span>Kemenhub</span>

                <strong>
                    {{ number_format($statistikFasilitas['kemenhub'], 0, ',', '.') }}
                </strong>
            </div>

            <div class="gs-stat-row">
                <span>GeoJSON tambahan</span>

                <strong>
                    {{ number_format($statistikFasilitas['geojson_tambahan'], 0, ',', '.') }}
                </strong>
            </div>

            <div class="gs-stat-row">
                <span>GeoJSON kandidat duplikat</span>

                <strong>
                    {{ number_format($statistikFasilitas['geojson_kandidat_duplikat'], 0, ',', '.') }}
                </strong>
            </div>

            <div class="gs-stat-row">
                <span>Total ditampilkan</span>

                <strong>
                    {{ number_format($statistikFasilitas['ditampilkan'], 0, ',', '.') }}
                </strong>
            </div>

            <div class="gs-stat-note">
                Data GeoJSON tidak dihapus.
                Penyaringan hanya berlaku pada tampilan peta.
            </div>

        </div>


        {{-- LAYER PETA --}}

        <div id="gs-layers">

            <label class="gs-layer">

                <input
                    type="checkbox"
                    data-layer="fasilitas"
                    checked
                >

                <span>
                    <strong>Titik fasilitas transportasi</strong>

                    <small>
                        Fasilitas Kemenhub dan GeoJSON tambahan
                    </small>
                </span>

            </label>


            <label class="gs-layer">

                <input
                    type="checkbox"
                    data-layer="akses-fasilitas"
                >

                <span>
                    <strong>Garis GeoJSON lama</strong>

                    <small>
                        Referensi hubungan akses fasilitas
                        dari tim statistika
                    </small>
                </span>

            </label>

        </div>


        {{-- FILTER MODA --}}

        <div class="gs-field">

            <label for="gs-moda">
                Moda Transportasi
            </label>

            <select id="gs-moda">

                <option value="">
                    Semua moda
                </option>

                <option value="1">
                    Darat / Terminal
                </option>

                <option value="2">
                    Laut / Pelabuhan
                </option>

                <option value="3">
                    Udara / Bandara
                </option>

                <option value="4">
                    Kereta Api / Stasiun
                </option>

            </select>

        </div>


        {{-- PENGAJUAN PEMDA --}}

        <div class="gs-field">

            <label for="gs-pengajuan">
                Pengajuan Pemda (Verifikasi Final)
            </label>

            <select id="gs-pengajuan">

                <option value="">
                    Pilih kebutuhan konektivitas...
                </option>

            </select>

        </div>


        {{-- JARINGAN REL --}}

        <label class="gs-layer">

            <input
                type="checkbox"
                id="gs-show-rail"
            >

            <span>
                <strong>Jaringan Rel Kemenhub</strong>

                <small>
                    Garis skematis antarujung segmen,
                    bukan geometri rel sebenarnya
                </small>
            </span>

        </label>


        {{-- RINGKASAN --}}

        <div
            class="gs-summary"
            id="gs-summary"
            aria-live="polite"
        >
            Memuat data geospasial...
        </div>


        {{-- DETAIL --}}

        <section
            class="gs-detail"
            id="gs-detail"
            aria-live="polite"
        >

            <h3>Informasi Lokasi</h3>

            <div>
                Klik titik fasilitas atau garis
                pada peta untuk melihat informasi.
            </div>

        </section>

    </aside>


    {{-- PETA --}}

    <div class="gs-mapbox">

        <svg
            id="gs-map"
            role="img"
            aria-label="Peta referensi geospasial Indonesia"
        ></svg>


        <div
            class="gs-alert"
            id="gs-alert"
        >
            Memuat peta...
        </div>


        {{-- LEGENDA --}}

        <div class="gs-legend">

            <div>
                <span style="background:#e67e22"></span>
                Terminal (Darat)
            </div>

            <div>
                <span style="background:#1688dc"></span>
                Pelabuhan (Laut)
            </div>

            <div>
                <span style="background:#8b5cf6"></span>
                Bandara (Udara)
            </div>

            <div>
                <span style="background:#159b68"></span>
                Stasiun (Kereta Api)
            </div>

            <div>
                <span style="background:#dc2626"></span>
                Koordinat perlu verifikasi
            </div>

            <div style="color:#ce7a23">
                ┄ Akses fasilitas pilihan
            </div>

            <div style="color:#173d6a">
                ━ Hubungan antarfasilitas (indikatif)
            </div>

            <small>
                Garis menunjukkan relasi spasial,
                bukan jalur transportasi aktual.
            </small>

        </div>


        {{-- ZOOM --}}

        <div class="gs-zoom">

            <button
                type="button"
                id="gs-in"
                aria-label="Perbesar"
            >+</button>

            <button
                type="button"
                id="gs-out"
                aria-label="Perkecil"
            >−</button>

            <button
                type="button"
                id="gs-home"
                aria-label="Atur ulang"
            >⟲</button>

        </div>

    </div>

</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js"></script>

<script>
(() => {
    'use strict';

    /*
    |--------------------------------------------------------------------------
    | DATA DARI LARAVEL
    |--------------------------------------------------------------------------
    */

    const managed = @json($fasilitasDatabase);

    const pengajuan = @json($pengajuanPeta);

    const requestedKab = @json(request()->query('kab'));

    const requestedModa = @json(request()->query('moda'));


    /*
    |--------------------------------------------------------------------------
    | SUMBER GEOJSON
    |--------------------------------------------------------------------------
    |
    | Titik fasilitas berasal dari database.
    | File GeoJSON lama digunakan hanya untuk garis akses opsional.
    |
    */

    const sources = {
        'akses-fasilitas': @json(
            route('peta.geospasial.data', [
                'layer' => 'akses-fasilitas'
            ])
        )
    };


    const all = {};

    const labels = {
        'fasilitas': 'Fasilitas transportasi',
        'akses-fasilitas': 'Akses fasilitas terdekat'
    };


    const colors = {
        1: '#e67e22',
        2: '#1688dc',
        3: '#8b5cf6',
        4: '#159b68'
    };


    const kind = {
        1: 'Terminal',
        2: 'Pelabuhan',
        3: 'Bandar Udara',
        4: 'Stasiun Kereta Api'
    };


    const modeNames = {
        1: 'Darat',
        2: 'Laut',
        3: 'Udara',
        4: 'Kereta Api'
    };


    const el = id => document.getElementById(id);


    const esc = s => String(s ?? '—')
        .replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[c]));


    /*
    |--------------------------------------------------------------------------
    | KONVERSI KOORDINAT GEOJSON LAMA
    |--------------------------------------------------------------------------
    */

    const mercatorToLonLat = ([x, y]) => [
        x * 180 / 20037508.342789244,

        (
            2 * Math.atan(
                Math.exp(
                    y * Math.PI / 20037508.342789244
                )
            ) - Math.PI / 2
        ) * 180 / Math.PI
    ];


    function convertGeometry(g) {
        if (!g) return g;

        const convert = (v, depth) =>
            depth === 0
                ? mercatorToLonLat(v)
                : v.map(item => convert(item, depth - 1));

        const depth = {
            Point: 0,
            MultiPoint: 1,
            LineString: 1,
            MultiLineString: 2,
            Polygon: 2,
            MultiPolygon: 3
        }[g.type];

        return depth === undefined
            ? g
            : {
                ...g,
                coordinates: convert(g.coordinates, depth)
            };
    }


    /*
    |--------------------------------------------------------------------------
    | INISIALISASI PETA
    |--------------------------------------------------------------------------
    */

    const svg = d3.select('#gs-map');

    const root = svg.append('g');

    const land = root.append('g');

    const relLayer = root.append('g');

    const connections = root.append('g');

    const recommendationLayer = root.append('g');

    const dots = root.append('g');

    const routeDots = root.append('g');


    const projection = d3.geoMercator();

    const path = d3.geoPath(projection);


    const zoom = d3.zoom()
        .scaleExtent([1, 16])
        .on('zoom', event => {
            root.attr('transform', event.transform);
        });


    svg.call(zoom);


    let base = null;

    let segmentsRel = null;

    let selectedPoint = null;


    /*
    |--------------------------------------------------------------------------
    | PERHITUNGAN JARAK
    |--------------------------------------------------------------------------
    */

    const rad = Math.PI / 180;


    const haversine = (a, b) => {
        const dl = (
            Number(b.lat) - Number(a.lat)
        ) * rad;

        const doLon = (
            Number(b.lon) - Number(a.lon)
        ) * rad;

        const h =
            Math.sin(dl / 2) ** 2
            + Math.cos(Number(a.lat) * rad)
            * Math.cos(Number(b.lat) * rad)
            * Math.sin(doLon / 2) ** 2;

        return 6371.0088
            * 2
            * Math.asin(
                Math.min(1, Math.sqrt(h))
            );
    };


    const formatKm = n =>
        new Intl.NumberFormat(
            'id-ID',
            { maximumFractionDigits: 1 }
        ).format(n) + ' km';


    const coord = p =>
        projection([
            Number(p.lon),
            Number(p.lat)
        ]);


    /*
    |--------------------------------------------------------------------------
    | DATA FASILITAS
    |--------------------------------------------------------------------------
    |
    | Seluruh titik berasal dari fasilitasDatabase.
    | Controller sudah menyaring kandidat duplikat GeoJSON.
    |
    */

    const rawPoints = managed
        .map(f => ({
            ...f,

            id: 'db-' + f.id,

            lat: Number(f.lat),

            lon: Number(f.lon),

            id_moda: Number(f.id_moda),

            perlu_verifikasi:
                f.perlu_verifikasi === true,

            layak_rekomendasi:
                f.layak_rekomendasi !== false
        }))
        .filter(f =>
            Number.isFinite(f.lat)
            && Number.isFinite(f.lon)
        );


    /*
    |--------------------------------------------------------------------------
    | ISI DROPDOWN PENGAJUAN PEMDA
    |--------------------------------------------------------------------------
    */

    pengajuan.forEach((r, i) => {
        const opt = document.createElement('option');

        opt.value = String(i);

        opt.textContent =
            `${r.laporan} | ${r.asal.nama} → ${r.tujuan.nama} (${modeNames[r.moda]})`;

        el('gs-pengajuan').appendChild(opt);
    });


    if (requestedKab) {
        const index = pengajuan.findIndex(r =>
            String(r.id_asal) === String(requestedKab)
            && (
                !requestedModa
                || String(r.moda) === String(requestedModa)
            )
        );

        if (index >= 0) {
            el('gs-pengajuan').value = String(index);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REKOMENDASI FASILITAS
    |--------------------------------------------------------------------------
    */

    function gambarRekomendasi() {
        const pilihan = el('gs-pengajuan').value;

        const r = pilihan === ''
            ? null
            : pengajuan[Number(pilihan)];


        const fasilitas = rawPoints.filter(p =>
            p.id_moda === r?.moda
            && p.layak_rekomendasi
        );


        let pair = null;


        if (r && fasilitas.length >= 2) {
            const origins = [...fasilitas]
                .sort(
                    (a, b) =>
                        haversine(r.asal, a)
                        - haversine(r.asal, b)
                )
                .slice(0, 5);


            const destinations = [...fasilitas]
                .sort(
                    (a, b) =>
                        haversine(r.tujuan, a)
                        - haversine(r.tujuan, b)
                )
                .slice(0, 5);


            const candidates = origins.flatMap(a =>
                destinations
                    .filter(b => b.id !== a.id)
                    .map(b => ({
                        a,
                        b,
                        da: haversine(r.asal, a),
                        db: haversine(b, r.tujuan)
                    }))
            );


            candidates.sort(
                (a, b) =>
                    (a.da + a.db) - (b.da + b.db)
            );


            pair = candidates[0] || null;
        }


        const lines = pair
            ? [
                {
                    from: r.asal,
                    to: pair.a,
                    main: false
                },
                {
                    from: pair.a,
                    to: pair.b,
                    main: true
                },
                {
                    from: pair.b,
                    to: r.tujuan,
                    main: false
                }
            ]
            : [];


        recommendationLayer
            .selectAll('line')
            .data(lines)
            .join('line')
            .attr(
                'class',
                d => d.main
                    ? 'gs-recommend gs-service'
                    : 'gs-recommend'
            )
            .attr('x1', d => coord(d.from)[0])
            .attr('y1', d => coord(d.from)[1])
            .attr('x2', d => coord(d.to)[0])
            .attr('y2', d => coord(d.to)[1]);


        const ends = r
            ? [
                {
                    ...r.asal,
                    type: 'Daerah asal'
                },
                {
                    ...r.tujuan,
                    type: 'Daerah tujuan'
                }
            ]
            : [];


        if (pair) {
            ends.push(
                {
                    ...pair.a,
                    type: 'Fasilitas asal'
                },
                {
                    ...pair.b,
                    type: 'Fasilitas tujuan'
                }
            );
        }


        routeDots
            .selectAll('circle')
            .data(ends, d => d.type)
            .join('circle')
            .attr('class', 'gs-route-point')
            .attr('r', 7)
            .attr('cx', d => coord(d)[0])
            .attr('cy', d => coord(d)[1])
            .attr(
                'fill',
                d => d.type.startsWith('Daerah')
                    ? '#c8372d'
                    : '#142b47'
            );


        routeDots
            .selectAll('title')
            .data(ends, d => d.type)
            .join('title')
            .text(
                d => d.type + ': ' + d.nama
            );


        if (!r) {
            return;
        }


        if (pair) {
            el('gs-detail').innerHTML = `
                <h3>Rekomendasi Spasial Awal</h3>

                <p>
                    <strong>${esc(r.laporan)}</strong>
                    · ${esc(modeNames[r.moda])}
                </p>

                <p>
                    ${esc(r.asal.nama)}
                    →
                    ${esc(r.tujuan.nama)}
                </p>

                <dl>
                    <dt>Fasilitas asal</dt>

                    <dd>
                        ${esc(pair.a.nama)}
                        (${formatKm(pair.da)} dari daerah asal)
                    </dd>

                    <dt>Sumber fasilitas asal</dt>

                    <dd>
                        ${esc(pair.a.sumber)}
                    </dd>

                    <dt>Fasilitas tujuan</dt>

                    <dd>
                        ${esc(pair.b.nama)}
                        (${formatKm(pair.db)} dari daerah tujuan)
                    </dd>

                    <dt>Sumber fasilitas tujuan</dt>

                    <dd>
                        ${esc(pair.b.sumber)}
                    </dd>

                    <dt>Jarak antarfasilitas</dt>

                    <dd>
                        ${formatKm(haversine(pair.a, pair.b))}
                        garis lurus
                    </dd>
                </dl>

                <p style="font-size:12px;color:#677b90">
                    Rekomendasi berdasarkan jarak geografis.
                    Belum mempertimbangkan trayek,
                    jaringan jalan, jadwal,
                    kapasitas, atau layanan aktual.
                </p>
            `;
        } else {
            el('gs-detail').innerHTML = `
                <h3>${esc(r.laporan)}</h3>

                <p>
                    ${esc(r.asal.nama)}
                    →
                    ${esc(r.tujuan.nama)}
                </p>

                <p>
                    Belum tersedia dua fasilitas
                    dengan moda ${esc(modeNames[r.moda])}
                    yang dapat digunakan untuk rekomendasi.
                </p>
            `;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    const layerEnabled = key => {
        const input = document.querySelector(
            `[data-layer="${key}"]`
        );

        return input ? input.checked : false;
    };


    const selectedModa = () =>
        el('gs-moda').value;


    /*
    |--------------------------------------------------------------------------
    | PEMERIKSAAN GARIS AKSES GEOJSON
    |--------------------------------------------------------------------------
    */

    const near = (a, b) =>
        Math.abs(a[0] - b[0]) < 0.002
        && Math.abs(a[1] - b[1]) < 0.002;


    const linked = (feature, p) => {
        const g = feature.geometry;

        if (!g) {
            return false;
        }

        const parts = g.type === 'LineString'
            ? [g.coordinates]
            : g.type === 'MultiLineString'
                ? g.coordinates
                : [];

        return parts.some(segment =>
            segment.length
            && (
                near(segment[0], [p.lon, p.lat])
                || near(
                    segment[segment.length - 1],
                    [p.lon, p.lat]
                )
            )
        );
    };


    /*
    |--------------------------------------------------------------------------
    | DETAIL FITUR GEOJSON
    |--------------------------------------------------------------------------
    */

    function showDetail(f, key) {
        const props = f.properties || {};

        const rows = Object.entries(props)
            .filter(([k, v]) =>
                v !== null
                && v !== ''
                && !['fid', 'NO', 'no'].includes(k)
            );


        el('gs-detail').innerHTML = `
            <h3>${esc(labels[key])}</h3>

            <dl>
                ${rows.map(([k, v]) => `
                    <dt>
                        ${esc(k.replace(/_/g, ' '))}
                    </dt>

                    <dd>
                        ${esc(
                            typeof v === 'object'
                                ? JSON.stringify(v)
                                : v
                        )}
                    </dd>
                `).join('')}
            </dl>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | DETAIL FASILITAS DATABASE
    |--------------------------------------------------------------------------
    */

    function showFacilityDetail(f) {
        const warning = f.perlu_verifikasi
            ? `
                <dt>Status koordinat</dt>

                <dd class="gs-warning">
                    Perlu verifikasi.
                    Koordinat identik dengan fasilitas lain.
                    Tidak digunakan untuk rekomendasi otomatis.
                </dd>
            `
            : '';


        el('gs-detail').innerHTML = `
            <h3>${esc(f.nama)}</h3>

            <dl>
                <dt>Moda</dt>

                <dd>
                    ${esc(kind[f.id_moda])}
                </dd>

                <dt>Kabupaten/Kota terkait</dt>

                <dd>
                    ${esc(f.kab_kota || 'Belum ditetapkan')}
                </dd>

                <dt>Provinsi</dt>

                <dd>
                    ${esc(f.provinsi || 'Belum ditetapkan')}
                </dd>

                <dt>Latitude</dt>

                <dd>
                    ${esc(f.lat)}
                </dd>

                <dt>Longitude</dt>

                <dd>
                    ${esc(f.lon)}
                </dd>

                <dt>Sumber data</dt>

                <dd>
                    ${esc(f.sumber)}
                </dd>

                ${warning}

                <dt>Garis akses GeoJSON terkait</dt>

                <dd>
                    ${
                        (
                            all['akses-fasilitas']?.features
                            || []
                        ).filter(g => linked(g, f)).length
                    } garis (perlu validasi)
                </dd>
            </dl>
        `;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE TAMPILAN PETA
    |--------------------------------------------------------------------------
    */

    function update() {
        const totals = [];


        /*
         * Garis akses fasilitas dari GeoJSON lama.
         */
        ['akses-fasilitas'].forEach(key => {
            const items = (
                all[key]?.features || []
            ).filter(f => layerEnabled(key));


            totals.push(
                `${items.length} ${labels[key].toLowerCase()}`
            );


            connections
                .selectAll(`path[data-key="${key}"]`)
                .data(items)
                .join('path')
                .attr('data-key', key)
                .attr(
                    'class',
                    f => `gs-connection gs-facility-link${
                        selectedPoint && linked(f, selectedPoint)
                            ? ' gs-highlight'
                            : ''
                    }`
                )
                .attr('d', path)
                .on('click', (event, f) => {
                    event.stopPropagation();

                    showDetail(f, key);
                });
        });


        /*
         * Titik fasilitas transportasi.
         */
        const items = rawPoints.filter(f =>
            layerEnabled('fasilitas')
            && (
                !selectedModa()
                || String(f.id_moda) === selectedModa()
            )
        );


        totals.push(
            `${items.length} titik fasilitas`
        );


        dots
            .selectAll('circle[data-key="fasilitas"]')
            .data(items, f => f.id)
            .join('circle')
            .attr('data-key', 'fasilitas')
            .attr('class', 'gs-point')
            .attr(
                'fill',
                f => colors[f.id_moda] || '#64748b'
            )
            .attr(
                'cx',
                f => coord(f)[0]
            )
            .attr(
                'cy',
                f => coord(f)[1]
            )
            .attr(
                'r',
                f => f.perlu_verifikasi ? 6 : 4
            )
            .attr(
                'stroke',
                f => f.perlu_verifikasi
                    ? '#dc2626'
                    : '#fff'
            )
            .attr(
                'stroke-width',
                f => f.perlu_verifikasi
                    ? 2.5
                    : 1
            )
            .on('click', (event, f) => {
                event.stopPropagation();

                selectedPoint = f;

                showFacilityDetail(f);

                update();
            });


        /*
         * Jaringan rel Kemenhub.
         */
        relLayer
            .selectAll('path')
            .data(
                el('gs-show-rail').checked
                    ? (segmentsRel?.features || [])
                    : []
            )
            .join('path')
            .attr('class', 'gs-rail')
            .attr('d', path);


        gambarRekomendasi();


        el('gs-summary').textContent =
            totals.join(' · ');
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER PETA
    |--------------------------------------------------------------------------
    */

    function render() {
        if (!base) {
            return;
        }

        const width =
            svg.node().clientWidth || 900;

        const height =
            svg.node().clientHeight || 600;


        svg.attr(
            'viewBox',
            `0 0 ${width} ${height}`
        );


        projection.fitExtent(
            [
                [24, 24],
                [width - 24, height - 24]
            ],
            base
        );


        land
            .selectAll('path')
            .data(base.features)
            .join('path')
            .attr('class', 'gs-land')
            .attr('d', path);


        update();
    }


    /*
    |--------------------------------------------------------------------------
    | EVENT FILTER DAN TOMBOL
    |--------------------------------------------------------------------------
    */

    Object.keys(sources).forEach(key => {
        document
            .querySelector(`[data-layer="${key}"]`)
            .addEventListener('change', update);
    });


    document
        .querySelector('[data-layer="fasilitas"]')
        .addEventListener('change', update);


    el('gs-moda').addEventListener(
        'change',
        update
    );


    el('gs-pengajuan').addEventListener(
        'change',
        () => {
            const selected =
                el('gs-pengajuan').value;

            if (selected !== '') {
                el('gs-moda').value = String(
                    pengajuan[Number(selected)].moda
                );
            }

            update();
        }
    );


    el('gs-show-rail').addEventListener(
        'change',
        update
    );


    el('gs-in').addEventListener(
        'click',
        () => svg.transition()
            .call(zoom.scaleBy, 1.5)
    );


    el('gs-out').addEventListener(
        'click',
        () => svg.transition()
            .call(zoom.scaleBy, 1 / 1.5)
    );


    el('gs-home').addEventListener(
        'click',
        () => svg.transition()
            .call(zoom.transform, d3.zoomIdentity)
    );


    /*
    |--------------------------------------------------------------------------
    | MUAT DATA PETA
    |--------------------------------------------------------------------------
    */

    Promise.all([

        /*
         * Batas provinsi Indonesia.
         */
        fetch(
            @json(asset('data/indonesia-provinsi.json'))
        ).then(r => {
            if (!r.ok) {
                throw Error(
                    'Batas provinsi tidak tersedia'
                );
            }

            return r.json();
        }),


        /*
         * Jaringan rel Kemenhub.
         * Opsional, jadi kegagalan tidak menggagalkan peta.
         */
        fetch(
            @json(
                route('peta.geospasial.data', [
                    'layer' => 'jaringan-rel'
                ])
            )
        )
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                segmentsRel = data;
            })
            .catch(() => {
                segmentsRel = null;
            }),


        /*
         * Garis akses GeoJSON.
         * Opsional, sehingga peta tetap berjalan
         * jika file ini belum tersedia.
         */
        ...Object.entries(sources).map(
            async ([key, url]) => {
                try {
                    const res = await fetch(url);

                    if (!res.ok) {
                        all[key] = {
                            type: 'FeatureCollection',
                            features: []
                        };

                        return;
                    }

                    const data = await res.json();

                    all[key] = {
                        ...data,

                        features: (
                            data.features || []
                        ).map(f => ({
                            ...f,

                            geometry: convertGeometry(
                                f.geometry
                            )
                        }))
                    };
                } catch (error) {
                    all[key] = {
                        type: 'FeatureCollection',
                        features: []
                    };
                }
            }
        )

    ]).then(([geo]) => {

        base = geo;


        /*
         * Filter moda dari URL.
         */
        if (
            ['1', '2', '3', '4'].includes(
                String(requestedModa)
            )
        ) {
            el('gs-moda').value =
                String(requestedModa);
        }


        /*
         * Detail fasilitas untuk kabupaten/kota
         * yang dikirim melalui URL.
         */
        if (requestedKab) {
            const matches = rawPoints.filter(p =>
                String(p.id_kab_kota) ===
                    String(requestedKab)
                && (
                    !requestedModa
                    || String(p.id_moda) ===
                        String(requestedModa)
                )
            );


            el('gs-detail').innerHTML =
                matches.length
                    ? `
                        <h3>Fasilitas Terkait Pengajuan</h3>

                        <p>
                            ${
                                matches
                                    .map(p => esc(p.nama))
                                    .join(', ')
                            }
                        </p>

                        <p>
                            Wilayah dan moda sesuai dengan
                            data referensi fasilitas.
                            Belum tentu tersedia layanan
                            untuk rute yang diajukan.
                        </p>
                    `
                    : `
                        <h3>Fasilitas Terkait Pengajuan</h3>

                        <p>
                            Belum ada fasilitas yang
                            terhubung langsung dengan
                            wilayah dan moda pengajuan ini.
                        </p>
                    `;
        }


        el('gs-alert').hidden = true;


        render();


        new ResizeObserver(
            render
        ).observe(svg.node());

    }).catch(error => {

        el('gs-summary').textContent =
            'Data belum dapat dimuat';

        el('gs-alert').textContent =
            error.message;

    });

})();
</script>

@endsection
