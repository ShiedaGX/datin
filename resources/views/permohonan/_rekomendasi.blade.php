<section>
    <h3 class="rv-h"><span class="step-no">{{ $no }}</span> Surat Rekomendasi Teknis</h3>
    <p class="muted rv-hs">Diunggah INTRAM setelah Surat CHR laporan ini disetujui.</p>
    @php($rt = $rute->rekomendasi_terbaru)
    @if ($rt)
        <ul class="rv-docs">
            <li class="rv-doc">
                <i class="rv-file"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg></i>
                <div class="rv-doc-main">
                    <strong>Surat Rekomendasi Teknis</strong>
                    <span>{{ $rt->nama_file }}</span>
                    <small>{{ $rt->ekstensi }} &bull; {{ $rt->ukuran_teks }} &bull; Diunggah {{ \App\Support\Tanggal::pendek($rt->updated_at) }} oleh {{ $rt->user->full_name ?? 'User INTRAM' }}</small>
                </div>
                <div class="rv-doc-act">
                    <a class="icon-act" target="_blank" rel="noopener" href="{{ route('rekomendasi.dokumen', $rt->id_rekomendasi_teknis) }}" title="Lihat dokumen" aria-label="Lihat Surat Rekomendasi Teknis">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <a class="icon-act" href="{{ route('rekomendasi.dokumen', [$rt->id_rekomendasi_teknis, 'unduh' => 1]) }}" title="Unduh dokumen" aria-label="Unduh Surat Rekomendasi Teknis">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11M8 11l4 4 4-4M5 20h14"/></svg>
                    </a>
                </div>
            </li>
        </ul>
        @if ($rt->catatan)
            <p class="rv-note">{{ $rt->catatan }}</p>
        @endif
    @else
        <div class="rv-kosong">Belum diunggah oleh INTRAM.</div>
    @endif
</section>
