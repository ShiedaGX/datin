@extends('layouts.app')

@section('title', 'Peta Konektivitas Regional')
@section('heading', 'Peta Konektivitas Regional')
@section('subheading', 'Pengajuan Verifikasi Final')

@section('content')
<style>
  .pk-shell{display:grid;grid-template-columns:305px minmax(0,1fr);min-height:650px;height:min(790px,calc(100vh - 150px));font-family:'Barlow Semi Condensed',Arial,sans-serif;border-radius:9px;overflow:hidden;border:1px solid #c9d4df}
  .pk-panel{background:#15263f;color:#f7f9fa;padding:20px;overflow:auto}
  .pk-panel h2{font-size:22px;margin:0 0 8px}.pk-panel p{font-size:13px;color:#b6c6db;line-height:1.45}
  .pk-field{margin-top:17px}.pk-field label{display:block;color:#d7e2ee;font-size:13px;margin-bottom:6px;font-weight:600}
  .pk-field select{display:block;width:100%;padding:9px;background:#24395a;color:white;border:1px solid #4d688d;border-radius:5px;font:inherit}
  .pk-toggles{display:flex;gap:6px;flex-wrap:wrap}.pk-toggle{border:1px solid #4b6485;border-radius:5px;background:#24395a;color:white;padding:7px 9px;cursor:pointer;font:inherit;font-size:12px}
  .pk-toggle[aria-pressed=false]{opacity:.45}.pk-summary{margin:18px 0;font-size:13px;color:#d5e0ec}
  .pk-detail{background:#f7f9fa;color:#15263f;border-radius:6px;padding:14px;line-height:1.4;font-size:13px}
  .pk-detail h3{font-size:18px;margin:0 0 6px}.pk-detail p{color:#526780;margin:5px 0}
  .pk-detail table{width:100%;border-collapse:collapse}.pk-detail th,.pk-detail td{text-align:left;vertical-align:top;border-bottom:1px solid #d7dfe8;padding:6px 3px}
  .pk-detail .pk-muted{color:#526780}.pk-canvas{background:#cfDDE3;position:relative;overflow:hidden}
  #pk-map{width:100%;height:100%;display:block}.pk-land{fill:#efe4c2;stroke:#b9a77a;stroke-width:.65;vector-effect:non-scaling-stroke}
  .pk-line{stroke:#3f5a78;stroke-opacity:.25;stroke-width:1.3;vector-effect:non-scaling-stroke;cursor:pointer}
  .pk-line:hover,.pk-line.pk-selected{stroke:#15263f;stroke-opacity:1;stroke-width:3}
  .pk-point{stroke:#fff;stroke-width:1.1;cursor:pointer}.pk-label{fill:#15263f;font-size:11px;paint-order:stroke;stroke:#cfdde3;stroke-width:3px;pointer-events:none}
  .pk-legend{position:absolute;left:13px;bottom:13px;background:#f7f9faec;color:#15263f;border-radius:5px;padding:10px;font-size:12px;line-height:1.9;max-width:290px}
  .pk-dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:5px}.pk-zoom{position:absolute;right:12px;bottom:12px;display:flex;flex-direction:column;gap:5px}
  .pk-zoom button{width:35px;height:35px;border:1px solid #b8c6d3;background:#fff;border-radius:4px;cursor:pointer;font-size:20px}
  .pk-empty{position:absolute;top:14px;left:14px;right:14px;background:#fffde8;color:#384d60;padding:12px;border-radius:5px;font-size:13px;display:none}
  @media(max-width:950px){.pk-shell{grid-template-columns:1fr;height:auto}.pk-panel{max-height:460px}.pk-canvas{height:65vh;min-height:450px}}
</style>

<div class="pk-shell">
  <aside class="pk-panel">
    <h2>Peta Kebutuhan Angkutan Bersubsidi</h2>
    <p>Data berstatus <strong>Verifikasi Final</strong> sesuai hak akses akun yang sedang login. Titik menggunakan koordinat kabupaten/kota dalam database DATIN.</p>
    <div class="pk-field"><label>Klasifikasi daerah</label><div id="pk-klas" class="pk-toggles"></div></div>
    <div class="pk-field"><label for="pk-moda">Moda transportasi</label><select id="pk-moda" @if($modaTerkunci) disabled @endif><option value="">{{ $modaTerkunci ? 'Moda akun saya' : 'Semua moda' }}</option></select></div>
    <div class="pk-field"><label for="pk-prov">Provinsi asal</label><select id="pk-prov"><option value="">Semua provinsi</option></select></div>
    <div class="pk-field"><label for="pk-sektor">Sektor komoditas tujuan</label><select id="pk-sektor"><option value="">Semua sektor</option></select></div>
    <div class="pk-field"><label for="pk-jarak">Kelas jarak lurus</label><select id="pk-jarak"><option value="">Semua jarak</option></select></div>
    <div class="pk-summary" id="pk-summary"></div>
    <div class="pk-detail" id="pk-detail" aria-live="polite"><h3>Detail konektivitas</h3><p>Pilih garis atau titik pada peta untuk melihat rute dan komoditasnya.</p></div>
  </aside>
  <section class="pk-canvas" aria-label="Peta konektivitas Indonesia">
    <svg id="pk-map" role="img" aria-label="Peta asal dan tujuan konektivitas regional"></svg>
    <div class="pk-empty" id="pk-empty"></div>
    <div class="pk-legend">
      <div><span class="pk-dot" style="background:#c8372d"></span>Prioritas &amp; Strategis (PS)</div>
      <div><span class="pk-dot" style="background:#e3a812"></span>Prioritas (P)</div>
      <div><span class="pk-dot" style="background:#1f7a58"></span>Strategis (S)</div>
      <div><span class="pk-dot" style="background:#15263f"></span>Simpul tujuan</div>
      <small>Garis = kebutuhan konektivitas, bukan lintasan transportasi aktual. Jarak = garis lurus Haversine.</small>
    </div>
    <div class="pk-zoom"><button type="button" id="pk-plus" aria-label="Perbesar">+</button><button type="button" id="pk-minus" aria-label="Perkecil">−</button><button type="button" id="pk-reset" aria-label="Atur ulang">⟲</button></div>
  </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js"></script>
<script>
(() => {
  'use strict';
  const points = @json($titikPeta);
  const routes = @json($rutePeta);
  const byId = new Map(points.map(p => [p.id, p]));
  const color = k => /prioritas.*strategis|strategis.*prioritas|\bPS\b/i.test(k) ? '#c8372d' : /prioritas|^P$/i.test(k) ? '#e3a812' : /strategis|^S$/i.test(k) ? '#1f7a58' : '#6c82a0';
  const esc = s => String(s ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  const select = id => document.getElementById(id);
  const unique = xs => [...new Set(xs.filter(Boolean))].sort((a,b) => a.localeCompare(b, 'id'));
  const options = (id, values) => {const element=select(id); for(const v of values){const o=document.createElement('option');o.value=v;o.textContent=v;element.appendChild(o);}};
  options('pk-moda', unique(routes.map(r => r.moda)));
  options('pk-prov', unique(routes.map(r => byId.get(r.asal)?.provinsi)));
  options('pk-sektor', unique(routes.flatMap(r => r.sektor)));
  options('pk-jarak', ['<100 km','100–300 km','300–700 km','700–1500 km','>1500 km']);
  const classes = unique(routes.map(r => r.klasifikasi));
  const enabled = new Set(classes);
  classes.forEach(k => {const b=document.createElement('button');b.type='button';b.className='pk-toggle';b.textContent=k;b.style.borderLeft='4px solid '+color(k);b.setAttribute('aria-pressed','true');b.addEventListener('click',()=>{enabled.has(k)?enabled.delete(k):enabled.add(k);b.setAttribute('aria-pressed',String(enabled.has(k)));update();});select('pk-klas').appendChild(b);});
  const svg=d3.select('#pk-map');const root=svg.append('g');const land=root.append('g');const links=root.append('g');const dots=root.append('g');
  const projection=d3.geoMercator();const path=d3.geoPath(projection);
  const zoom=d3.zoom().scaleExtent([1,12]).on('zoom',e=>root.attr('transform',e.transform));svg.call(zoom);
  let selected=null;let geo=null;
  const shown=()=>routes.filter(r => enabled.has(r.klasifikasi) && (!select('pk-moda').value || r.moda===select('pk-moda').value) && (!select('pk-prov').value || byId.get(r.asal)?.provinsi===select('pk-prov').value) && (!select('pk-sektor').value || r.sektor.includes(select('pk-sektor').value)) && (!select('pk-jarak').value || r.kelasJarak===select('pk-jarak').value));
  function detail(r){if(!r){select('pk-detail').innerHTML='<h3>Detail konektivitas</h3><p>Pilih garis atau titik pada peta untuk melihat rute dan komoditasnya.</p>';return;}
    const a=byId.get(r.asal),b=byId.get(r.tujuan);
    const rows=r.komoditas.length?r.komoditas.map(k=>`<tr><td>${esc(k.nama)}<div class="pk-muted">${esc(k.sektor||'Sektor belum terpetakan')}</div></td><td>${k.jumlah===null?'–':new Intl.NumberFormat('id-ID',{maximumFractionDigits:2}).format(k.jumlah)} ${esc(k.satuan||'(satuan belum tersedia)')}</td></tr>`).join(''):'<tr><td colspan="2">Belum ada detail komoditas</td></tr>';
    select('pk-detail').innerHTML=`<h3>${esc(a?.nama)} → ${esc(b?.nama)}</h3><p>${esc(r.laporan)} · ${esc(r.klasifikasi)} · ${esc(r.moda)}</p><p>Jarak lurus: <strong>${esc(r.jarak)} km</strong></p><table><thead><tr><th>Komoditas / sektor</th><th>Jumlah</th></tr></thead><tbody>${rows}</tbody></table>`;
  }
  function update(){const rs=shown();const origins=new Set(rs.map(r=>r.asal));const destinations=new Set(rs.map(r=>r.tujuan));const ids=new Set([...origins,...destinations]);
    select('pk-summary').textContent=`${rs.length} hubungan konektivitas · ${origins.size} daerah asal · ${destinations.size} simpul tujuan`;
    select('pk-empty').style.display=rs.length?'none':'block';select('pk-empty').textContent='Belum ada rute Verifikasi Final yang sesuai filter dan memiliki koordinat lengkap.';
    const line=links.selectAll('line').data(rs,r=>`${r.id}:${r.tujuan}`);line.exit().remove();line.enter().append('line').attr('class','pk-line').on('click',(e,r)=>{selected=r;detail(r);update();}).append('title').text(r=>`${byId.get(r.asal)?.nama} → ${byId.get(r.tujuan)?.nama}: ${r.jarak} km`);
    links.selectAll('line').attr('x1',r=>projection([byId.get(r.asal).lon,byId.get(r.asal).lat])[0]).attr('y1',r=>projection([byId.get(r.asal).lon,byId.get(r.asal).lat])[1]).attr('x2',r=>projection([byId.get(r.tujuan).lon,byId.get(r.tujuan).lat])[0]).attr('y2',r=>projection([byId.get(r.tujuan).lon,byId.get(r.tujuan).lat])[1]).classed('pk-selected',r=>selected?.id===r.id&&selected?.tujuan===r.tujuan);
    const p=points.filter(p=>ids.has(p.id));const circles=dots.selectAll('circle').data(p,p=>p.id);circles.exit().remove();circles.enter().append('circle').attr('class','pk-point').on('click',(e,p)=>{
      // Titik yang sekaligus menjadi asal dan tujuan rute lain harus
      // memprioritaskan pengajuan dengan titik ini sebagai DAERAH ASAL.
      // Contoh: klik Labuhanbatu membuka Labuhanbatu → Toba,
      // bukan rute Toba → Labuhanbatu yang ditemukan lebih dulu.
      const r=rs.find(r=>r.asal===p.id) ?? rs.find(r=>r.tujuan===p.id);
      selected=r||null;detail(selected);update();
    }).append('title').text(p=>p.nama);
    dots.selectAll('circle').attr('cx',p=>projection([p.lon,p.lat])[0]).attr('cy',p=>projection([p.lon,p.lat])[1]).attr('r',p=>destinations.has(p.id)?5:4).attr('fill',p=>origins.has(p.id)?color(rs.find(r=>r.asal===p.id)?.klasifikasi||''):'#15263f');
    if(selected&&!rs.some(r=>r.id===selected.id&&r.tujuan===selected.tujuan)){selected=null;detail(null);}
  }
  function render(){if(!geo)return;const w=svg.node().clientWidth||900,h=svg.node().clientHeight||650;svg.attr('viewBox',`0 0 ${w} ${h}`);projection.fitExtent([[20,20],[w-20,h-20]],geo);land.selectAll('path').data(geo.features).join('path').attr('class','pk-land').attr('d',path).append('title').text(d=>d.properties?.provinsi||'');update();}
  for(const id of ['pk-moda','pk-prov','pk-sektor','pk-jarak'])select(id).addEventListener('change',update);
  select('pk-plus').addEventListener('click',()=>svg.transition().call(zoom.scaleBy,1.5));select('pk-minus').addEventListener('click',()=>svg.transition().call(zoom.scaleBy,1/1.5));select('pk-reset').addEventListener('click',()=>svg.transition().call(zoom.transform,d3.zoomIdentity));
  fetch(@json(asset('data/indonesia-provinsi.json'))).then(r=>{if(!r.ok)throw Error('GeoJSON tidak ditemukan');return r.json()}).then(g=>{geo=g;render();new ResizeObserver(render).observe(svg.node());}).catch(err=>{select('pk-empty').style.display='block';select('pk-empty').textContent='Gagal memuat GeoJSON peta: '+err.message;});
})();
</script>
@endsection
