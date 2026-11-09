            {{-- 1. Dokumen --}}
            <section>
                <h3 class="rv-h"><span class="step-no">{{ $no }}</span> {{ $judul ?? 'Dokumen Surat' }}</h3>
                <p class="muted rv-hs">{{ $deskripsi ?? 'Buka setiap dokumen untuk memastikan berkas dapat dibaca dan sesuai dengan jenis surat.' }}</p>
                <ul class="rv-docs">
                    @forelse ($berkas as $b)
                        @php($intram = ($mode ?? 'moda') === 'intram')
                        <li class="rv-doc">
                            <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                            <div class="rv-doc-main">
                                <strong>{{ $b['model']->jenis_dokumen }}</strong>
                                <span>{{ $b['model']->nama_file }}</span>
                                <small>@if ($b['model']->nomor_surat)No. {{ $b['model']->nomor_surat }} &bull; @endif{{ $b['jenis'] }}@if ($b['ukuran']) &bull; {{ $b['ukuran'] }}@endif &bull; Diunggah {{ \App\Support\Tanggal::pendek($b['model']->tanggal_upload) }}</small>
                            </div>
                            <div class="rv-doc-act">
                                <a class="icon-act" target="_blank" rel="noopener" href="{{ $intram ? route('dokumen.unduh', [$b['model']->id_dokumen_pengajuan, 'lihat' => 1]) : route('permohonan.dokumen', [$rute->id_rute_subsidi, $b['model']->id_dokumen_pengajuan]) }}" title="Lihat dokumen" aria-label="Lihat {{ $b['model']->jenis_dokumen }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a class="icon-act" href="{{ $intram ? route('dokumen.unduh', $b['model']->id_dokumen_pengajuan) : route('permohonan.dokumen', [$rute->id_rute_subsidi, $b['model']->id_dokumen_pengajuan, 'unduh' => 1]) }}" title="Unduh dokumen" aria-label="Unduh {{ $b['model']->jenis_dokumen }}">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M8 11l4 4 4-4M5 20h14"/></svg>
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="rv-doc"><span class="muted">Belum ada dokumen pada laporan ini.</span></li>
                    @endforelse
                </ul>
            </section>
