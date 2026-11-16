            <section class="rv-panel">
                <h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg> Ringkasan Pengajuan</h3>
                <dl>
                    <dt>Pemohon / Instansi</dt>
                    <dd>{{ $pengaju?->instansi ?: 'Pemerintah Daerah ' . $rute->kabKotaAsal->nama_lengkap }}</dd>
                    <dt>Wilayah</dt>
                    <dd>{{ $rute->kabKotaAsal->nama_lengkap }}, Provinsi {{ $rute->kabKotaAsal->provinsi->nama_provinsi }}</dd>
                    <dt>Tanggal pengajuan</dt>
                    <dd>{{ $rute->tanggal_lengkap }}</dd>
                    <dt>Nomor laporan</dt>
                    <dd>{{ $rute->nomor_laporan }}</dd>
                    <dt>Moda transportasi</dt>
                    <dd>{{ $rute->moda->nama_moda ?? '-' }}</dd>
                </dl>
                @if ($pengaju)
                    <div class="rv-user">
                        <i><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg></i>
                        <div><strong>{{ $pengaju->full_name }}</strong><small>{{ mb_strtolower($pengaju->role->nama_role ?? 'pemda') }}</small></div>
                        <a href="mailto:{{ $pengaju->email }}" title="Kirim email" aria-label="Kirim email ke {{ $pengaju->full_name }}">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                        </a>
                    </div>
                @endif
            </section>

            <section class="rv-panel">
                <h3><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 109-9 9 9 0 00-7 3.4M3 4v4h4M12 7v5l3 2"/></svg> Riwayat Persetujuan</h3>
                <ol class="rv-timeline">
                    <li class="done">
                        <strong>Laporan diajukan</strong>
                        <span>Oleh {{ $pengaju->full_name ?? '-' }}@if ($pengaju && $pengaju->instansi), {{ $pengaju->instansi }}@endif</span>
                        <small>{{ \App\Support\Tanggal::pendek($rute->waktu_pengajuan) }} &bull; {{ $rute->jam_pengajuan }}</small>
                    </li>
                    @foreach ($riwayat as $v)
                        <li class="done s-{{ \App\Support\StatusPengajuan::grup($v->status_verifikasi) }}">
                            <strong>{{ $v->tahap_verifikasi }}: {{ $v->status_verifikasi }}</strong>
                            <span>Oleh {{ $v->verifikator->full_name ?? '-' }}</span>
                            @if ($v->catatan_verifikasi)<p class="rv-note">{{ $v->catatan_verifikasi }}</p>@endif
                            <small>{{ \App\Support\Tanggal::pendek($v->tanggal_verifikasi) }} &bull; {{ \App\Support\Tanggal::jam($v->tanggal_verifikasi) }}</small>
                        </li>
                    @endforeach
                    @if ($rute->sedang_menunggu)
                        <li class="now">
                            <strong>Peninjauan laporan <em class="now-badge">Saat ini</em></strong>
                            <span>Sedang ditinjau</span>
                            <small>Menunggu keputusan Moda {{ $rute->moda->nama_moda ?? '' }}</small>
                        </li>
                    @endif
                </ol>
            </section>
