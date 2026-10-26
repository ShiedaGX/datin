            {{-- 2. Data yang diinput Pemda --}}
            <section>
                <h3 class="rv-h"><span class="step-no">{{ $no }}</span> Data Pengajuan Pemda</h3>
                <p class="muted rv-hs">Data yang diinput Pemda pada formulir pengajuan. Cocokkan dengan isi dokumen surat.</p>
                <div class="rv-data">
                    <dl class="rv-grid">
                        <div><dt>Provinsi</dt><dd>{{ $rute->kabKotaAsal->provinsi->nama_provinsi }}</dd></div>
                        <div><dt>Kabupaten / Kota</dt><dd>{{ $rute->kabKotaAsal->nama_lengkap }}</dd></div>
                        <div><dt>Wilayah regional</dt><dd>{{ $rute->kabKotaAsal->wilayah->nama_wilayah }}</dd></div>
                        <div><dt>Moda transportasi</dt><dd>{{ $rute->moda->nama_moda ?? '-' }}</dd></div>
                        <div><dt>Klasifikasi wilayah</dt><dd><span class="badge badge-{{ \Illuminate\Support\Str::slug($pemda->klasifikasi_label) }}">{{ $pemda->klasifikasi_label }}</span></dd></div>
                    </dl>
                    <div class="rv-block">
                        <h4>Keterangan daerah</h4>
                        @forelse ($pemda->keterangan_daftar as $butir)<span class="tag">{{ $butir }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                    <div class="rv-block">
                        <h4>Potensi unggulan daerah</h4>
                        @if (count($pemda->potensi_daftar))
                            <ol class="rv-list">
                                @foreach ($pemda->potensi_daftar as $butir)<li>{{ $butir }}</li>@endforeach
                            </ol>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </div>
                    <div class="rv-block">
                        <h4>Sektor potensi ({{ $pemda->jumlah_sektor }})</h4>
                        @forelse ($pemda->sektor_terdeteksi as $sektor)<span class="tag">{{ $sektor }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                    <div class="rv-block">
                        <h4>Kebutuhan konektivitas / rute tujuan @if ($pemda->jumlah_tujuan !== null)({{ $pemda->jumlah_tujuan }} tujuan)@endif</h4>
                        @forelse ($pemda->tujuan_daftar as $tujuan)<span class="tag tag-alt">{{ $tujuan }}</span>@empty<span class="muted">-</span>@endforelse
                    </div>
                </div>
            </section>
