@extends('layouts.app')

@section('title', 'Peta Konektivitas Regional')
@section('heading', 'Peta Konektivitas Regional')
@section('subheading', 'Pengajuan Verifikasi Final')

@section('content')
<style>
  /* DATIN | Modern geospatial workspace */
  .pk-shell{--pk-ink:#14243b;--pk-muted:#718198;--pk-border:#e3eaf2;--pk-blue:#2563eb;display:grid;grid-template-columns:326px minmax(0,1fr);height:min(820px,calc(100vh - 145px));min-height:660px;font-family:Inter,'Plus Jakarta Sans','Barlow Semi Condensed',system-ui,sans-serif;border:1px solid #e0e8f1;border-radius:20px;overflow:hidden;background:#fff;box-shadow:0 14px 46px rgba(22,47,82,.09)}
  .pk-panel{background:#fff;color:var(--pk-ink);overflow:auto;padding:0;scrollbar-width:thin;scrollbar-color:#d8e1ec transparent;border-right:1px solid var(--pk-border)}
  .pk-panel-head{padding:25px 23px 21px;background:linear-gradient(150deg,#10243e 0%,#1c4167 100%);color:#fff;position:relative;overflow:hidden}
  .pk-panel-head:after{content:'';position:absolute;width:180px;height:180px;border-radius:50%;border:1px solid rgba(255,255,255,.09);right:-72px;top:-83px;box-shadow:0 0 0 35px rgba(255,255,255,.025),0 0 0 70px rgba(255,255,255,.025);pointer-events:none}
  .pk-eyebrow{font-size:10px;font-weight:800;letter-spacing:2px;color:#a9d3ff;text-transform:uppercase;display:flex;align-items:center;gap:8px;margin-bottom:12px}
  .pk-eyebrow:before{content:'';width:7px;height:7px;background:#52d5b3;border-radius:50%;box-shadow:0 0 0 4px rgba(82,213,179,.15)}
  .pk-panel h2{font-size:21px;line-height:1.25;font-weight:750;letter-spacing:-.5px;margin:0 0 10px;color:#fff}
  .pk-panel-head p{font-size:12px;color:#c3d3e6;line-height:1.65;margin:0}
  .pk-panel-head strong{color:#fff;font-weight:650}
  .pk-panel-body{padding:22px 21px 26px}
  .pk-section-title{font-size:10px;font-weight:800;letter-spacing:1.4px;text-transform:uppercase;color:#8b9bb0;margin:3px 0 15px;display:flex;align-items:center;gap:9px}
  .pk-section-title:after{content:'';flex:1;height:1px;background:#e9eef4}
  .pk-field{margin:0 0 17px}
  .pk-field label{display:block;color:#334760;font-size:12px;margin-bottom:7px;font-weight:700}
  .pk-field select{display:block;width:100%;min-height:41px;padding:9px 34px 9px 12px;background-color:#f8fafc;color:#1d344f;border:1px solid #dfe7f0;border-radius:10px;font:inherit;font-size:12px;font-weight:550;outline:none;transition:border-color .18s,box-shadow .18s;cursor:pointer}
  .pk-field select:hover{border-color:#afc4dc;background-color:#fff}
  .pk-field select:focus{border-color:#4c88df;box-shadow:0 0 0 3px rgba(37,99,235,.11);background-color:#fff}
  .pk-field select:disabled{opacity:.65;cursor:not-allowed}
  .pk-toggles{display:flex;gap:7px;flex-wrap:wrap}
  .pk-toggle{border:1px solid #dce6f0;border-radius:9px;background:#f5f8fc;color:#263b54;padding:8px 10px;cursor:pointer;font:inherit;font-weight:650;font-size:11px;transition:background .2s,border-color .2s,opacity .2s,transform .2s}
  .pk-toggle:hover{background:#eaf2fd;transform:translateY(-1px)}
  .pk-toggle[aria-pressed=false]{opacity:.45;background:#fff}
  .pk-summary{margin:21px 0 15px;padding:14px 15px;background:#eff6ff;border:1px solid #d9e8fc;border-radius:12px;font-size:12px;color:#315579;line-height:1.65;font-weight:650}
  .pk-detail{background:#fff;color:#1b3049;border:1px solid #e2e9f2;border-radius:12px;padding:16px;line-height:1.5;font-size:12px;box-shadow:0 3px 13px rgba(19,49,83,.035)}
  .pk-detail h3{font-size:15px;line-height:1.4;font-weight:750;margin:0 0 8px;letter-spacing:-.2px}
  .pk-detail p{color:#687c95;margin:7px 0;line-height:1.55}
  .pk-detail table{width:100%;border-collapse:collapse;margin-top:10px}
  .pk-detail th,.pk-detail td{text-align:left;vertical-align:top;border-bottom:1px solid #e7edf5;padding:9px 3px;font-size:11px}
  .pk-detail th{color:#687c95;font-weight:700}
  .pk-detail .pk-muted{color:#8392a4;font-size:10px;margin-top:3px}
  .pk-canvas{background:radial-gradient(ellipse at 50% 42%,#e6f5fa 0%,#d8ebf5 64%,#cfe5f2 100%);position:relative;overflow:hidden}
  #pk-map{width:100%;height:100%;display:block;cursor:grab}
  #pk-map:active{cursor:grabbing}
  .pk-land{fill:#f6f2e6;stroke:#bdcfc6;stroke-width:.75;vector-effect:non-scaling-stroke;transition:fill .18s}
  .pk-land:hover{fill:#f9f5e9}
  .pk-line{stroke-opacity:.68;stroke-width:2;vector-effect:non-scaling-stroke;cursor:pointer;transition:stroke-opacity .18s,stroke-width .18s}
  .pk-line:hover,.pk-line.pk-selected{stroke-opacity:1;stroke-width:3.6}
  .pk-cluster{fill:#173b63;fill-opacity:.92;stroke:#fff;stroke-width:2;vector-effect:non-scaling-stroke;cursor:pointer}.pk-cluster:hover{fill:#0f2a47}
  .pk-cluster-n{fill:#fff;font:700 11px Inter,system-ui,sans-serif;text-anchor:middle;dominant-baseline:central;pointer-events:none}
  .pk-point{stroke:#fff;stroke-width:1.7;vector-effect:non-scaling-stroke;cursor:pointer;filter:drop-shadow(0 1px 2px rgba(23,42,65,.32))}
  .pk-label{fill:#243951;font-size:11px;paint-order:stroke;stroke:#d8ebf5;stroke-width:3px;pointer-events:none}
  .pk-city-dot{fill:#52677a;stroke:#fff;stroke-width:1.2;vector-effect:non-scaling-stroke;pointer-events:none}
  .pk-city-label{fill:#405268;font-size:10px;font-weight:650;paint-order:stroke;stroke:#f6f2e6;stroke-width:3px;stroke-linejoin:round;pointer-events:none}
  .pk-city-label.pk-city-major{font-size:12px;font-weight:800;fill:#20334a}
  .pk-water-label{fill:#4386aa;font-size:11px;font-weight:650;font-style:italic;letter-spacing:.5px;text-anchor:middle;paint-order:stroke;stroke:#dcecf6;stroke-width:3px;stroke-linejoin:round;pointer-events:none}
  .pk-water-label.pk-water-ocean{font-size:13px;letter-spacing:1.4px;fill:#2b759e}
  .pk-water-label.pk-water-strait{font-size:9px;letter-spacing:0;fill:#5594b4}
  .pk-map-header{position:absolute;left:22px;top:20px;z-index:5;pointer-events:none;background:rgba(255,255,255,.93);border:1px solid rgba(222,233,243,.9);box-shadow:0 7px 25px rgba(33,66,101,.08);border-radius:13px;padding:12px 16px;backdrop-filter:blur(10px)}
  .pk-map-header strong{display:block;color:#1b344d;font-size:13px;letter-spacing:-.1px}
  .pk-map-header span{display:block;margin-top:4px;color:#8193a7;font-size:10px;font-weight:550}
  .pk-map-chip{position:absolute;right:17px;top:20px;z-index:5;background:rgba(255,255,255,.92);border:1px solid #e0eaf3;border-radius:9px;padding:8px 11px;color:#506782;font-size:10px;font-weight:750;letter-spacing:.5px;box-shadow:0 4px 15px rgba(20,55,88,.07)}
  .pk-map-chip:before{content:'';display:inline-block;width:6px;height:6px;border-radius:50%;background:#34b999;margin-right:7px;vertical-align:middle}
  .pk-legend{position:absolute;left:18px;right:76px;bottom:17px;display:flex;flex-direction:column;align-items:flex-start;gap:8px;background:rgba(255,255,255,.95);color:#243950;border:1px solid rgba(221,231,242,.9);border-radius:13px;padding:13px 17px;font-size:10px;line-height:1.5;box-shadow:0 9px 30px rgba(35,68,100,.10);backdrop-filter:blur(10px);z-index:10}
  .pk-legend-row{display:flex;flex-wrap:wrap;align-items:center;column-gap:15px;row-gap:7px;width:100%}
  .pk-legend-heading{font-size:10px;font-weight:800;min-width:115px;white-space:nowrap;color:#52667d}
  .pk-legend-item{display:inline-flex;align-items:center;gap:6px;white-space:nowrap;color:#4d6178;font-weight:600}
  .pk-dot{display:inline-block;width:9px;height:9px;border-radius:50%;flex-shrink:0;margin:0;box-shadow:0 0 0 2px rgba(0,0,0,.035)}
  .pk-line-legend{display:inline-block;width:20px;height:0;border-top:3px solid;border-radius:3px;flex-shrink:0;margin:0}
  .pk-legend-note{width:100%;padding-top:8px;border-top:1px solid #e9eef4;color:#8795a7;font-size:10px;line-height:1.5}
  .pk-zoom{position:absolute;right:17px;bottom:17px;display:flex;flex-direction:column;gap:5px;z-index:11;background:rgba(255,255,255,.95);border:1px solid #e1e9f2;border-radius:12px;padding:5px;box-shadow:0 8px 22px rgba(34,66,98,.12)}
  .pk-zoom button{width:35px;height:35px;border:0;background:transparent;color:#38536d;border-radius:8px;cursor:pointer;font-size:20px;font-weight:500;transition:background .2s,color .2s}
  .pk-zoom button:hover{background:#e9f2ff;color:#1d64bb}
  .pk-zoom button:focus-visible{outline:2px solid #3b82f6;outline-offset:1px}
  .pk-empty{position:absolute;top:87px;left:22px;right:22px;background:#fff8e9;color:#785a22;border:1px solid #f2dfae;padding:13px 15px;border-radius:10px;font-size:12px;display:none;z-index:8;box-shadow:0 4px 16px rgba(0,0,0,.05)}
  @media(max-width:1050px){.pk-shell{grid-template-columns:292px minmax(0,1fr)}.pk-legend{right:69px}}
  @media(max-width:850px){.pk-shell{grid-template-columns:1fr;height:auto;min-height:0;border-radius:15px}.pk-panel{max-height:440px;border-right:0;border-bottom:1px solid #e5edf5}.pk-panel-head{padding:21px}.pk-panel-body{padding:19px 21px}.pk-canvas{height:65vh;min-height:460px}.pk-legend{left:10px;right:60px;bottom:10px;padding:10px 12px;max-height:170px;overflow-y:auto}.pk-legend-row{column-gap:10px;row-gap:6px}.pk-legend-heading{min-width:100%}.pk-map-header{left:12px;top:12px}.pk-map-chip{right:12px;top:12px}.pk-zoom{right:10px;bottom:10px}}
  @media(max-width:520px){.pk-map-chip{display:none}.pk-map-header{padding:9px 12px}.pk-map-header strong{font-size:11px}.pk-legend{font-size:9px}.pk-legend-item{white-space:normal}}
</style>

<div class="pk-shell">
  <aside class="pk-panel">
    <div class="pk-panel-head">
    <div class="pk-eyebrow">DATIN · GEOSPATIAL INTELLIGENCE</div>
    <h2>Peta Kebutuhan Angkutan Bersubsidi</h2>
    <p>Data berstatus <strong>Verifikasi Final</strong> sesuai hak akses akun yang sedang login. Titik menggunakan koordinat kabupaten/kota dalam database DATIN.</p>
    </div>
    <div class="pk-panel-body">
    <div class="pk-section-title">Filter konektivitas</div>
    <div class="pk-field"><label>Klasifikasi daerah</label><div id="pk-klas" class="pk-toggles"></div></div>
    <div class="pk-field"><label for="pk-moda">Moda transportasi</label><select id="pk-moda" @if($modaTerkunci) disabled @endif><option value="">{{ $modaTerkunci ? 'Moda akun saya' : 'Semua moda' }}</option></select></div>
    <div class="pk-field"><label for="pk-prov">Provinsi asal</label><select id="pk-prov"><option value="">Semua provinsi</option></select></div>
    <div class="pk-field"><label for="pk-sektor">Sektor komoditas tujuan</label><select id="pk-sektor"><option value="">Semua sektor</option></select></div>
    <div class="pk-field"><label for="pk-jarak">Kelas jarak lurus</label><select id="pk-jarak"><option value="">Semua jarak</option></select></div>
    <div class="pk-section-title" style="margin-top:23px">Tampilan peta</div>
    <div class="pk-field"><label for="pk-kota">Label kota pada peta</label><select id="pk-kota"><option value="otomatis">Otomatis mengikuti zoom</option><option value="utama">Hanya kota utama</option><option value="semua">Semua kota referensi</option><option value="sembunyi">Sembunyikan label kota</option></select></div>
    <div class="pk-field"><label for="pk-perairan">Label perairan pada peta</label><select id="pk-perairan"><option value="otomatis">Otomatis mengikuti zoom</option><option value="utama">Samudra dan laut utama</option><option value="semua">Semua nama perairan</option><option value="sembunyi">Sembunyikan label perairan</option></select></div>
    <div class="pk-summary" id="pk-summary"></div>
    <div class="pk-detail" id="pk-detail" aria-live="polite"><h3>Detail konektivitas</h3><p>Pilih garis atau titik pada peta untuk melihat rute dan komoditasnya.</p></div>
    </div>
  </aside>
  <section class="pk-canvas" aria-label="Peta konektivitas Indonesia">
    <div class="pk-map-header"><strong>Eksplorasi Konektivitas Indonesia</strong><span>Jelajahi rute, simpul wilayah, dan potensi komoditas</span></div>
    <div class="pk-map-chip">PETA INTERAKTIF</div>
    <svg id="pk-map" role="img" aria-label="Peta asal dan tujuan konektivitas regional"></svg>
    <div class="pk-empty" id="pk-empty"></div>
    <div class="pk-legend">
      <div class="pk-legend-row"><strong class="pk-legend-heading">Klasifikasi Daerah:</strong><span class="pk-legend-item"><span class="pk-dot" style="background:#c8372d"></span>Prioritas &amp; Strategis (PS)</span><span class="pk-legend-item"><span class="pk-dot" style="background:#e3a812"></span>Prioritas (P)</span><span class="pk-legend-item"><span class="pk-dot" style="background:#1f7a58"></span>Strategis (S)</span><span class="pk-legend-item"><span class="pk-dot" style="background:#15263f"></span>Simpul tujuan</span></div>
      <div class="pk-legend-row"><strong class="pk-legend-heading">Moda Transportasi:</strong><span class="pk-legend-item"><span class="pk-line-legend" style="border-color:#e67e22"></span>Darat</span><span class="pk-legend-item"><span class="pk-line-legend" style="border-color:#1688dc"></span>Laut</span><span class="pk-legend-item"><span class="pk-line-legend" style="border-color:#8b5cf6"></span>Udara</span><span class="pk-legend-item"><span class="pk-line-legend" style="border-color:#159b68"></span>Kereta Api</span></div>
      <div class="pk-legend-row"><strong class="pk-legend-heading">Referensi Geografis:</strong><span class="pk-legend-item"><span class="pk-dot" style="background:#435469;width:7px;height:7px"></span>Label kota (bukan pengajuan)</span></div>
      <div class="pk-legend-note">Garis menunjukkan kebutuhan konektivitas, bukan lintasan transportasi aktual. Jarak = garis lurus Haversine. Label kota dan perairan digunakan sebagai orientasi geografis.</div>
    </div>
    <div class="pk-zoom"><button type="button" id="pk-plus" aria-label="Perbesar">+</button><button type="button" id="pk-minus" aria-label="Perkecil">−</button><button type="button" id="pk-reset" aria-label="Atur ulang">⟲</button></div>
  </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js"></script>
<script src="{{ asset('js/peta-cluster.js') }}"></script>
<script>
(() => {
  'use strict';
  const points = @json($titikPeta);
  const routes = @json($rutePeta);
  const byId = new Map(points.map(p => [p.id, p]));

  // Titik penempatan label bersifat kartografis (perkiraan), bukan batas perairan.
  // Prioritas "utama" terlihat pada skala nasional; selat muncul saat zoom lebih dekat.
  const perairanReferensi = [
    {nama:'Samudra Hindia',lon:103.0,lat:-10.8,jenis:'samudra',utama:true},
    {nama:'Samudra Pasifik',lon:137.5,lat:2.8,jenis:'samudra',utama:true},
    {nama:'Laut Jawa',lon:111.5,lat:-5.25,jenis:'laut',utama:true},
    {nama:'Laut Banda',lon:126.5,lat:-5.1,jenis:'laut',utama:true},
    {nama:'Laut Sulawesi',lon:122.0,lat:3.1,jenis:'laut',utama:true},
    {nama:'Laut Arafura',lon:135.0,lat:-8.0,jenis:'laut',utama:true},
    {nama:'Laut Flores',lon:120.0,lat:-7.3,jenis:'laut'},
    {nama:'Laut Seram',lon:129.0,lat:-2.6,jenis:'laut'},
    {nama:'Laut Halmahera',lon:129.5,lat:1.0,jenis:'laut'},
    {nama:'Laut Natuna',lon:108.0,lat:3.5,jenis:'laut'},
    {nama:'Laut Timor',lon:126.5,lat:-11.1,jenis:'laut'},
    {nama:'Selat Malaka',lon:99.5,lat:3.1,jenis:'selat'},
    {nama:'Selat Sunda',lon:105.85,lat:-5.95,jenis:'selat'},
    {nama:'Selat Karimata',lon:108.4,lat:-2.0,jenis:'selat'},
    {nama:'Selat Makassar',lon:118.2,lat:-2.7,jenis:'selat'},
    {nama:'Selat Lombok',lon:115.75,lat:-8.5,jenis:'selat'},
    {nama:'Selat Bali',lon:114.5,lat:-8.3,jenis:'selat'},
    {nama:'Selat Alas',lon:116.75,lat:-8.45,jenis:'selat'},
    {nama:'Selat Ombai',lon:124.5,lat:-8.5,jenis:'selat'}
  ];

  // Koordinat pendekatan pusat kota, hanya untuk orientasi peta.
  // Level utama muncul pada zoom nasional; level provinsi muncul saat zoom diperbesar.
  const kotaReferensi = [
    {nama:'Jakarta',lon:106.8456,lat:-6.2088,utama:true},
    {nama:'Medan',lon:98.6722,lat:3.5952,utama:true},
    {nama:'Surabaya',lon:112.7521,lat:-7.2575,utama:true},
    {nama:'Makassar',lon:119.4327,lat:-5.1477,utama:true},
    {nama:'Bandung',lon:107.6191,lat:-6.9175,utama:true},
    {nama:'Semarang',lon:110.4203,lat:-6.9667,utama:true},
    {nama:'Palembang',lon:104.7566,lat:-2.9761,utama:true},
    {nama:'Denpasar',lon:115.2126,lat:-8.6705,utama:true},
    {nama:'Banda Aceh',lon:95.3238,lat:5.5483},
    {nama:'Padang',lon:100.3543,lat:-0.9471},
    {nama:'Pekanbaru',lon:101.4478,lat:0.5071},
    {nama:'Tanjungpinang',lon:104.4550,lat:0.9186},
    {nama:'Jambi',lon:103.6131,lat:-1.6101},
    {nama:'Bengkulu',lon:102.2655,lat:-3.7928},
    {nama:'Pangkalpinang',lon:106.1099,lat:-2.1291},
    {nama:'Bandar Lampung',lon:105.2667,lat:-5.4292},
    {nama:'Serang',lon:106.1503,lat:-6.1200},
    {nama:'Yogyakarta',lon:110.3695,lat:-7.7956},
    {nama:'Mataram',lon:116.1167,lat:-8.5833},
    {nama:'Kupang',lon:123.6070,lat:-10.1772},
    {nama:'Pontianak',lon:109.3414,lat:-0.0263},
    {nama:'Palangka Raya',lon:113.9213,lat:-2.2096},
    {nama:'Banjarmasin',lon:114.5908,lat:-3.3186},
    {nama:'Samarinda',lon:117.1537,lat:-0.5022},
    {nama:'Tanjung Selor',lon:117.3736,lat:2.8375},
    {nama:'Manado',lon:124.8421,lat:1.4748},
    {nama:'Gorontalo',lon:123.0595,lat:0.5435},
    {nama:'Palu',lon:119.8707,lat:-0.8917},
    {nama:'Kendari',lon:122.5149,lat:-3.9985},
    {nama:'Mamuju',lon:118.8867,lat:-2.6806},
    {nama:'Ambon',lon:128.1814,lat:-3.6954},
    {nama:'Sofifi',lon:127.5690,lat:0.7373},
    {nama:'Jayapura',lon:140.7181,lat:-2.5337},
    {nama:'Manokwari',lon:134.0620,lat:-0.8615},
    {nama:'Sorong',lon:131.2549,lat:-0.8762},
    {nama:'Nabire',lon:135.4950,lat:-3.3682},
    {nama:'Wamena',lon:138.9490,lat:-4.0950},
    {nama:'Merauke',lon:140.4018,lat:-8.4932}
  ];

  const warnaModa = {1:'#e67e22',2:'#1688dc',3:'#8b5cf6',4:'#159b68'};
  const warnaGaris = r => warnaModa[r.id_moda] || '#64748b';
  const color = k => /prioritas.*strategis|strategis.*prioritas|\bPS\b/i.test(k) ? '#c8372d' : /prioritas|^P$/i.test(k) ? '#e3a812' : /strategis|^S$/i.test(k) ? '#1f7a58' : '#6c82a0';
  const esc = s => String(s ?? '').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  const select = id => document.getElementById(id);
  const unique = xs => [...new Set(xs.filter(Boolean))].sort((a,b)=>a.localeCompare(b,'id'));
  const options = (id,values) => {const element=select(id);for(const v of values){const o=document.createElement('option');o.value=v;o.textContent=v;element.appendChild(o)}};
  options('pk-moda',unique(routes.map(r=>r.moda)));
  options('pk-prov',unique(routes.map(r=>byId.get(r.asal)?.provinsi)));
  options('pk-sektor',unique(routes.flatMap(r=>r.sektor || [])));
  options('pk-jarak',['<100 km','100–300 km','300–700 km','700–1500 km','>1500 km']);

  const classes = unique(routes.map(r=>r.klasifikasi));
  const enabled = new Set(classes);
  classes.forEach(k=>{const b=document.createElement('button');b.type='button';b.className='pk-toggle';b.textContent=k;b.style.borderLeft='4px solid '+color(k);b.setAttribute('aria-pressed','true');b.addEventListener('click',()=>{enabled.has(k)?enabled.delete(k):enabled.add(k);b.setAttribute('aria-pressed',String(enabled.has(k)));update()});select('pk-klas').appendChild(b)});

  const svg = d3.select('#pk-map');
  const root = svg.append('g');
  const land = root.append('g');
  const waterLayer = root.append('g'); // Di atas daratan, di bawah garis dan titik.
  const links = root.append('g');
  const cityLayer = root.append('g');
  const dots = root.append('g'); // Titik pengajuan berada di atas label kota.
  const projection = d3.geoMercator();
  const path = d3.geoPath(projection);
  let currentZoom = 1, currentT = d3.zoomIdentity, pkState = null, raf = 0;
  const zoom = d3.zoom().scaleExtent([1,12]).on('zoom',e=>{root.attr('transform',e.transform);currentZoom=e.transform.k;currentT=e.transform;if(!raf)raf=requestAnimationFrame(()=>{raf=0;drawDots();gambarLabelKota();gambarLabelPerairan()})});
  svg.call(zoom);
  let selected=null;
  let geo=null;

  const shown = () => routes.filter(r=>
    enabled.has(r.klasifikasi)
    && (!select('pk-moda').value || r.moda===select('pk-moda').value)
    && (!select('pk-prov').value || byId.get(r.asal)?.provinsi===select('pk-prov').value)
    && (!select('pk-sektor').value || (r.sektor || []).includes(select('pk-sektor').value))
    && (!select('pk-jarak').value || r.kelasJarak===select('pk-jarak').value)
  );

  function detail(r){
    if(!r){select('pk-detail').innerHTML='<h3>Detail konektivitas</h3><p>Pilih garis atau titik pada peta untuk melihat rute dan komoditasnya.</p>';return}
    const a=byId.get(r.asal),b=byId.get(r.tujuan);
    const rows=(r.komoditas || []).length ? r.komoditas.map(k=>`<tr><td>${esc(k.nama)}<div class="pk-muted">${esc(k.sektor || 'Sektor belum terpetakan')}</div></td><td>${k.jumlah===null?'–':new Intl.NumberFormat('id-ID',{maximumFractionDigits:2}).format(k.jumlah)} ${esc(k.satuan || '(satuan belum tersedia)')}</td></tr>`).join('') : '<tr><td colspan="2">Belum ada detail komoditas</td></tr>';
    select('pk-detail').innerHTML=`<h3>${esc(a?.nama)} → ${esc(b?.nama)}</h3><p>${esc(r.laporan)} · ${esc(r.klasifikasi)} · ${esc(r.moda)}</p><p>Jarak lurus: <strong>${esc(r.jarak)} km</strong></p><table><thead><tr><th>Komoditas / sektor</th><th>Jumlah</th></tr></thead><tbody>${rows}</tbody></table>`;
  }

  function update(){
    const rs=shown();
    const origins=new Set(rs.map(r=>r.asal));
    const destinations=new Set(rs.map(r=>r.tujuan));
    const ids=new Set([...origins,...destinations]);
    select('pk-summary').textContent=`${rs.length} hubungan konektivitas · ${origins.size} daerah asal · ${destinations.size} simpul tujuan`;
    select('pk-empty').style.display=rs.length?'none':'block';
    select('pk-empty').textContent='Belum ada rute Verifikasi Final yang sesuai filter dan memiliki koordinat lengkap.';

    const line=links.selectAll('line').data(rs,r=>`${r.id}:${r.tujuan}`);
    line.exit().remove();
    line.enter().append('line').attr('class','pk-line').on('click',(e,r)=>{selected=r;detail(r);update()}).append('title');
    links.selectAll('line')
      .attr('x1',r=>projection([byId.get(r.asal).lon,byId.get(r.asal).lat])[0])
      .attr('y1',r=>projection([byId.get(r.asal).lon,byId.get(r.asal).lat])[1])
      .attr('x2',r=>projection([byId.get(r.tujuan).lon,byId.get(r.tujuan).lat])[0])
      .attr('y2',r=>projection([byId.get(r.tujuan).lon,byId.get(r.tujuan).lat])[1])
      .attr('stroke',r=>warnaGaris(r))
      .classed('pk-selected',r=>selected?.id===r.id&&selected?.tujuan===r.tujuan)
      .select('title').text(r=>`${byId.get(r.asal)?.nama} → ${byId.get(r.tujuan)?.nama}: ${r.jarak} km`);

    const p=points.filter(p=>ids.has(p.id));
    pkState={p,origins,destinations,rs};drawDots();
    if(selected&&!rs.some(r=>r.id===selected.id&&r.tujuan===selected.tujuan)){selected=null;detail(null)}
  }

  const xy = d => projection([d.lon,d.lat]);
  // Titik dikelompokkan pada zoom rendah; ukuran titik/label tetap di layar (dibagi k).
  function drawDots(){
    if(!pkState||!geo)return;
    const {p,origins,destinations,rs}=pkState,k=currentZoom,w=svg.node().clientWidth||900,h=svg.node().clientHeight||650;
    const g=PetaCluster.group(p,xy,{k,tx:currentT.x,ty:currentT.y,w,h,cell:38,pinned:selected?(selected.asal):null});
    const markers=[...g.clusters.map(c=>({key:'c:'+c.key,type:'c',x:c.x,y:c.y,c})),...g.singles.map(d=>{const [x,y]=xy(d);return {key:'p:'+d.id,type:'p',x,y,d}})];
    const nodes=dots.selectAll('g.pk-mk').data(markers,d=>d.key).join(en=>{const n=en.append('g').attr('class','pk-mk');n.append('circle');n.append('text').attr('class','pk-cluster-n');n.append('title');return n});
    nodes.attr('transform',d=>`translate(${d.x},${d.y}) scale(${1/k})`);
    nodes.select('circle').attr('class',d=>d.type==='c'?'pk-cluster':'pk-point')
      .attr('r',d=>d.type==='c'?11+Math.min(6,Math.log2(d.c.items.length)*1.5):(destinations.has(d.d.id)?5:4))
      .attr('fill',d=>d.type==='c'?null:(origins.has(d.d.id)?color(rs.find(r=>r.asal===d.d.id)?.klasifikasi||''):'#15263f'));
    nodes.select('text').text(d=>d.type==='c'?(d.c.items.length>999?'999+':d.c.items.length):'');
    nodes.select('title').text(d=>d.type==='c'?`${d.c.items.length} titik. Klik untuk memperbesar`:d.d.nama);
    nodes.on('click',(e,d)=>{
      e.stopPropagation();
      if(d.type==='p'){selected=rs.find(r=>r.asal===d.d.id)??rs.find(r=>r.tujuan===d.d.id)??null;detail(selected);update();return}
      const [x0,y0,x1,y1]=PetaCluster.bounds(d.c.items,xy),k2=Math.min(12,Math.max(k*1.9,.8*Math.min(w/Math.max(x1-x0,1e-3),h/Math.max(y1-y0,1e-3))));
      if(k2<=k*1.05){select('pk-detail').innerHTML=`<h3>${d.c.items.length} titik berdekatan</h3><ul>${d.c.items.slice(0,40).map(i=>`<li>${esc(i.nama)}</li>`).join('')}</ul>`;return}
      svg.transition().duration(450).call(zoom.transform,d3.zoomIdentity.translate(w/2-k2*(x0+x1)/2,h/2-k2*(y0+y1)/2).scale(k2));
    });
  }

  // Penempatan label di koordinat layar: label yang bertumpuk disembunyikan (yang utama didahulukan).
  function penempat(){
    const k=currentZoom,w=svg.node().clientWidth||900,h=svg.node().clientHeight||650,placed=[];
    return (d,size,centered)=>{
      const [x,y]=xy(d),sx=currentT.x+k*x,sy=currentT.y+k*y;
      if(sx<-80||sy<-30||sx>w+80||sy>h+30)return false;
      const tw=d.nama.length*size*.6+(centered?0:8),b=centered?[sx-tw/2,sy-size,sx+tw/2,sy+4]:[sx+4,sy-size-6,sx+4+tw,sy+3];
      if(placed.some(q=>b[0]<q[2]&&b[2]>q[0]&&b[1]<q[3]&&b[3]>q[1]))return false;
      placed.push(b);return true;
    };
  }
  let tempat=null;

  function gambarLabelKota(){
    if(!geo)return;
    const mode=select('pk-kota').value,k=currentZoom,tp=penempat();tempat=tp;
    const tampil=(mode==='sembunyi'?[]:kotaReferensi.filter(d=>mode==='semua'||(mode==='utama'&&d.utama)||(mode==='otomatis'&&(d.utama||currentZoom>=2.2))))
      .slice().sort((a,b)=>(b.utama?1:0)-(a.utama?1:0)).filter(d=>tp(d,d.utama?12:10,false));
    cityLayer.selectAll('circle').data(tampil,d=>d.nama).join('circle')
      .attr('class','pk-city-dot').attr('cx',d=>xy(d)[0]).attr('cy',d=>xy(d)[1]).attr('r',d=>(d.utama?3:2)/k);
    cityLayer.selectAll('text').data(tampil,d=>d.nama).join('text')
      .attr('class',d=>'pk-city-label'+(d.utama?' pk-city-major':''))
      .attr('x',d=>xy(d)[0]+6/k).attr('y',d=>xy(d)[1]-5/k)
      .style('font-size',d=>(d.utama?12:10)/k+'px').style('stroke-width',3/k+'px')
      .text(d=>d.nama);
  }

  function gambarLabelPerairan(){
    if(!geo)return;
    const mode=select('pk-perairan').value,k=currentZoom,tp=penempat();
    const ukuran=d=>d.jenis==='samudra'?13:(d.jenis==='selat'?9:11);
    const tampil=(mode==='sembunyi'?[]:perairanReferensi.filter(d=>
      mode==='semua' ||
      (mode==='utama' && d.utama) ||
      (mode==='otomatis' && (d.utama || (d.jenis==='laut' && currentZoom>=1.8) || (d.jenis==='selat' && currentZoom>=3.2)))
    )).slice().sort((a,b)=>(b.utama?1:0)-(a.utama?1:0)).filter(d=>tp(d,ukuran(d),true));
    waterLayer.selectAll('text').data(tampil,d=>d.nama).join('text')
      .attr('class',d=>'pk-water-label'+(d.jenis==='samudra'?' pk-water-ocean':'')+(d.jenis==='selat'?' pk-water-strait':''))
      .attr('x',d=>xy(d)[0]).attr('y',d=>xy(d)[1])
      .style('font-size',d=>ukuran(d)/k+'px').style('stroke-width',3/k+'px')
      .text(d=>d.nama);
  }

  function render(){
    if(!geo)return;
    const w=svg.node().clientWidth||900,h=svg.node().clientHeight||650;
    svg.attr('viewBox',`0 0 ${w} ${h}`);
    projection.fitExtent([[20,20],[w-20,h-20]],geo);
    land.selectAll('path').data(geo.features).join('path').attr('class','pk-land').attr('d',path).selectAll('title').data(d=>[d]).join('title').text(d=>d.properties?.provinsi||'');
    update();
    gambarLabelKota();
    gambarLabelPerairan();
  }

  for(const id of ['pk-moda','pk-prov','pk-sektor','pk-jarak'])select(id).addEventListener('change',update);
  select('pk-kota').addEventListener('change',gambarLabelKota);
  select('pk-perairan').addEventListener('change',gambarLabelPerairan);
  select('pk-plus').addEventListener('click',()=>svg.transition().call(zoom.scaleBy,1.5));
  select('pk-minus').addEventListener('click',()=>svg.transition().call(zoom.scaleBy,1/1.5));
  select('pk-reset').addEventListener('click',()=>svg.transition().call(zoom.transform,d3.zoomIdentity));
  fetch(@json(asset('data/indonesia-provinsi.json')))
    .then(r=>{if(!r.ok)throw Error('GeoJSON tidak ditemukan');return r.json()})
    .then(g=>{geo=g;render();new ResizeObserver(render).observe(svg.node())})
    .catch(err=>{select('pk-empty').style.display='block';select('pk-empty').textContent='Gagal memuat GeoJSON peta: '+err.message});
})();
</script>
@endsection
