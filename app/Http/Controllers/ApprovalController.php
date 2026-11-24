<?php
namespace App\Http\Controllers;


namespace App\Http\Controllers;

use App\Models\DataPemda;
use App\Models\Moda;
use App\Models\PengajuanSurat;
use App\Models\PeninjauanSurat;
use App\Models\PermintaanRevisi;
use App\Models\RuteSubsidi;
use App\Models\VerifikasiPengajuan;
use App\Support\Paginasi;
use App\Support\Permohonan;
use App\Support\StatusPengajuan;
use App\Support\SuratOptions;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Approval Surat CHR oleh user INTRAM. INTRAM melihat surat CHR dari semua moda beserta laporan dan
 * data Pemda yang menjadi rujukannya, lalu menyetujui, meminta perbaikan, atau menolak.
 * INTRAM juga dapat meminta Pemda mengajukan lagi untuk moda tambahan tanpa mengubah pengajuan moda lain.
 */
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApprovalController extends Controller
{
    private const PER_HALAMAN = 10;

    public const CHECKLIST = [
        'dokumen'   => ['Dokumen CHR tersedia dan terbaca', 'Berkas utama dapat dibuka, utuh, dan sesuai jenis Surat Catatan Hasil Review.'],
        'identitas' => ['Identitas dan pengesahan sesuai', 'Nomor surat, pengirim, penerima, tanggal, dan tanda tangan telah diperiksa.'],
        'hasil'     => ['Hasil review sesuai moda dan usulan', 'Catatan layanan {moda} dan wilayah {wilayah} sesuai pengajuan yang dirujuk.'],
        'temuan'    => ['Temuan dan rekomendasi jelas', 'Kesimpulan CHR konsisten dengan lampiran pendukung yang disertakan.'],
    ];

    private const KEPUTUSAN = [
        'setuju'    => ['status' => SuratOptions::DISETUJUI,       'aksi' => SuratOptions::AKSI_SETUJU,   'pesan' => 'disetujui'],
        'perbaikan' => ['status' => SuratOptions::PERLU_PERBAIKAN, 'aksi' => SuratOptions::AKSI_PERBAIKI, 'pesan' => 'dikembalikan untuk diperbaiki'],
        'tolak'     => ['status' => SuratOptions::DITOLAK,         'aksi' => SuratOptions::AKSI_TOLAK,    'pesan' => 'ditolak'],
        'dokumen' => [
            'Dokumen CHR tersedia dan terbaca',
            'Berkas utama dapat dibuka, utuh, dan sesuai jenis Surat Catatan Hasil Review.',
        ],
        'identitas' => [
            'Identitas dan pengesahan sesuai',
            'Nomor surat, pengirim, penerima, tanggal, dan tanda tangan telah diperiksa.',
        ],
        'hasil' => [
            'Hasil review sesuai moda dan usulan',
            'Catatan layanan {moda} dan wilayah {wilayah} sesuai pengajuan yang dirujuk.',
        ],
        'temuan' => [
            'Temuan dan rekomendasi jelas',
            'Kesimpulan CHR konsisten dengan lampiran pendukung yang disertakan.',
        ],
    ];

    private const KEPUTUSAN = [
        'setuju' => [
            'status' => SuratOptions::DISETUJUI,
            'aksi' => SuratOptions::AKSI_SETUJU,
            'pesan' => 'disetujui',
        ],
        'perbaikan' => [
            'status' => SuratOptions::PERLU_PERBAIKAN,
            'aksi' => SuratOptions::AKSI_PERBAIKI,
            'pesan' => 'dikembalikan untuk diperbaiki',
        ],
        'tolak' => [
            'status' => SuratOptions::DITOLAK,
            'aksi' => SuratOptions::AKSI_TOLAK,
            'pesan' => 'ditolak',
        ],
    ];

    public function index(Request $request)
    {
        $f = $this->filter($request);

        // Ringkasan mengikuti filter periode
        $hit = PengajuanSurat::query()->where('status', '!=', SuratOptions::DRAF);
        $this->terapkanPeriode($hit, $f['periode']);
        $hit = $hit->select('status', DB::raw('COUNT(*) AS n'))->groupBy('status')->pluck('n', 'status');
        $n = fn (string $status) => (int) ($hit[$status] ?? 0);
        $kpi = [
            'masuk'     => (int) $hit->sum(),
            'terkirim'  => $n(SuratOptions::TERKIRIM),
            'review'    => $n(SuratOptions::DALAM_REVIEW),
            'disetujui' => $n(SuratOptions::DISETUJUI),
            'perbaikan' => $n(SuratOptions::PERLU_PERBAIKAN),
            'ditolak'   => $n(SuratOptions::DITOLAK),
        ];

        $periode = PengajuanSurat::where('status', '!=', SuratOptions::DRAF)->pluck('dikirim_at')->filter()
            ->map(fn ($d) => $d->format('Y-m'))->unique()->sortDesc()->values();

        $data = $this->query($f)->paginate(self::PER_HALAMAN)->withQueryString();

        // Pengajuan yang dibuka pada panel detail
        $dipilih = null;
        if ((int) $request->query('pilih') > 0) {
            $dipilih = PengajuanSurat::where('status', '!=', SuratOptions::DRAF)->find((int) $request->query('pilih'));
        }
        $dipilih ??= PengajuanSurat::where('status', SuratOptions::DALAM_REVIEW)->orderByDesc('dikirim_at')->first()
            ?? PengajuanSurat::where('status', SuratOptions::TERKIRIM)->orderBy('dikirim_at')->first();

        $detail = $dipilih ? $this->detail($dipilih) : [];

        $tanpa = fn (array $kunci) => route('approval.index', array_filter(
            \Illuminate\Support\Arr::except($request->query(), array_merge($kunci, ['page', 'pilih'])),
            fn ($v) => $v !== null && $v !== ''
        ));
        $chips = [];
        if ($f['moda']) {
            $chips[] = ['label' => 'Moda ' . (Moda::find($f['moda'])->nama_moda ?? ''), 'url' => $tanpa(['moda'])];
        }
        if ($f['status']) {
            $chips[] = ['label' => SuratOptions::STATUS_OPSI[$f['status']], 'url' => $tanpa(['status'])];
        }
        if ($f['periode']) {
            $chips[] = ['label' => Tanggal::bulanTahun($f['periode']), 'url' => $tanpa(['periode'])];
        }

        return view('approval.index', [
            'data'        => $data,
            'filter'      => $f,
            'kpi'         => $kpi,
            'modaOpsi'    => Moda::orderBy('id_moda')->get(),
            'statusOpsi'  => array_diff_key(SuratOptions::STATUS_OPSI, ['draf' => 1]),
            'periodeOpsi' => $periode->mapWithKeys(fn ($ym) => [$ym => Tanggal::bulanTahun($ym)]),
            'halaman'     => Paginasi::nomor($data->currentPage(), $data->lastPage()),
            'chips'       => $chips,
            'dipilih'     => $dipilih,
        ] + $detail);
    }

    /** Mulai meninjau: Terkirim -> Dalam Review (dicatat pada riwayat). */
    public function mulai(Request $request, PengajuanSurat $pengajuan)
    {
        $this->pastikanMasuk($pengajuan);

        DB::transaction(function () use ($request, $pengajuan) {
            $p = PengajuanSurat::where('id_pengajuan_surat', $pengajuan->id_pengajuan_surat)->lockForUpdate()->first();
            if ($p->status === SuratOptions::TERKIRIM) {
                $p->update(['status' => SuratOptions::DALAM_REVIEW]);
                PeninjauanSurat::create([
                    'id_pengajuan_surat' => $p->id_pengajuan_surat,
                    'id_user'            => $request->user()->id_user,
                    'aksi'               => SuratOptions::AKSI_MULAI,
        $hit = PengajuanSurat::query()
            ->where('status', '!=', SuratOptions::DRAF);

        $this->terapkanPeriode($hit, $f['periode']);

        $hit = $hit
            ->select('status', DB::raw('COUNT(*) AS n'))
            ->groupBy('status')
            ->pluck('n', 'status');

        $n = fn (string $status) => (int) ($hit[$status] ?? 0);

        $kpi = [
            'masuk' => (int) $hit->sum(),
            'terkirim' => $n(SuratOptions::TERKIRIM),
            'review' => $n(SuratOptions::DALAM_REVIEW),
            'disetujui' => $n(SuratOptions::DISETUJUI),
            'perbaikan' => $n(SuratOptions::PERLU_PERBAIKAN),
            'ditolak' => $n(SuratOptions::DITOLAK),
        ];

        $periode = PengajuanSurat::where(
            'status',
            '!=',
            SuratOptions::DRAF
        )->pluck('dikirim_at')
            ->filter()
            ->map(fn ($d) => $d->format('Y-m'))
            ->unique()
            ->sortDesc()
            ->values();

        $data = $this->query($f)
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        $dipilih = null;

        if ((int) $request->query('pilih') > 0) {
            $dipilih = PengajuanSurat::where(
                'status',
                '!=',
                SuratOptions::DRAF
            )->find((int) $request->query('pilih'));
        }

        $dipilih ??= PengajuanSurat::where(
            'status',
            SuratOptions::DALAM_REVIEW
        )->orderByDesc('dikirim_at')->first()
            ?? PengajuanSurat::where(
                'status',
                SuratOptions::TERKIRIM
            )->orderBy('dikirim_at')->first();

        $detail = $dipilih ? $this->detail($dipilih) : [];

        $tanpa = fn (array $kunci) => route(
            'approval.index',
            array_filter(
                \Illuminate\Support\Arr::except(
                    $request->query(),
                    array_merge($kunci, ['page', 'pilih'])
                ),
                fn ($v) => $v !== null && $v !== ''
            )
        );

        $chips = [];

        if ($f['moda']) {
            $chips[] = [
                'label' => 'Moda ' . (
                    Moda::find($f['moda'])->nama_moda ?? ''
                ),
                'url' => $tanpa(['moda']),
            ];
        }

        if ($f['status']) {
            $chips[] = [
                'label' => SuratOptions::STATUS_OPSI[$f['status']],
                'url' => $tanpa(['status']),
            ];
        }

        if ($f['periode']) {
            $chips[] = [
                'label' => Tanggal::bulanTahun($f['periode']),
                'url' => $tanpa(['periode']),
            ];
        }

        return view('approval.index', [
            'data' => $data,
            'filter' => $f,
            'kpi' => $kpi,
            'modaOpsi' => Moda::orderBy('id_moda')->get(),
            'statusOpsi' => array_diff_key(
                SuratOptions::STATUS_OPSI,
                ['draf' => 1]
            ),
            'periodeOpsi' => $periode->mapWithKeys(
                fn ($ym) => [$ym => Tanggal::bulanTahun($ym)]
            ),
            'halaman' => Paginasi::nomor(
                $data->currentPage(),
                $data->lastPage()
            ),
            'chips' => $chips,
            'dipilih' => $dipilih,
        ] + $detail);
    }

    /**
     * Mulai peninjauan Surat CHR.
     */
    public function mulai(
        Request $request,
        PengajuanSurat $pengajuan
    ) {
        $this->pastikanMasuk($pengajuan);

        DB::transaction(function () use ($request, $pengajuan) {
            if ($pengajuan->id_rute_subsidi) {
                RuteSubsidi::where(
                    'id_rute_subsidi',
                    $pengajuan->id_rute_subsidi
                )->lockForUpdate()->firstOrFail();
            }

            $p = PengajuanSurat::where(
                'id_pengajuan_surat',
                $pengajuan->id_pengajuan_surat
            )->lockForUpdate()->firstOrFail();

            $this->pastikanMasuk($p);

            if ($p->status === SuratOptions::TERKIRIM) {
                $p->update([
                    'status' => SuratOptions::DALAM_REVIEW,
                ]);

                PeninjauanSurat::create([
                    'id_pengajuan_surat' => $p->id_pengajuan_surat,
                    'id_user' => $request->user()->id_user,
                    'aksi' => SuratOptions::AKSI_MULAI,
                ]);
            }
        });

        return redirect(route('approval.index', ['pilih' => $pengajuan->id_pengajuan_surat]) . '#detail');
    }

    public function keputusan(Request $request, PengajuanSurat $pengajuan)
    {
        $user = $request->user();
        $this->pastikanMasuk($pengajuan);

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(array_keys(self::KEPUTUSAN))],
            'catatan'   => ['nullable', 'string', 'max:1000'],
            'periksa'   => ['nullable', 'array'],
            'periksa.*' => [Rule::in(array_keys(self::CHECKLIST))],
        ], [
            'keputusan.required' => 'Pilih keputusan terlebih dahulu.',
            'catatan.max'        => 'Catatan maksimal :max karakter.',
        return redirect(
            route('approval.index', [
                'pilih' => $pengajuan->id_pengajuan_surat,
            ]) . '#detail'
        );
    }

    /**
     * Menetapkan keputusan Surat CHR.
     */
    public function keputusan(
        Request $request,
        PengajuanSurat $pengajuan
    ) {
        $user = $request->user();

        $this->pastikanMasuk($pengajuan);

        $data = $request->validate([
            'keputusan' => [
                'required',
                Rule::in(array_keys(self::KEPUTUSAN)),
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'periksa' => [
                'nullable',
                'array',
            ],
            'periksa.*' => [
                'string',
                Rule::in(array_keys(self::CHECKLIST)),
            ],
        ], [
            'keputusan.required' => 'Pilih keputusan terlebih dahulu.',
            'catatan.max' => 'Catatan maksimal :max karakter.',
        ]);

        $catatan = trim((string) ($data['catatan'] ?? ''));

        if (in_array($data['keputusan'], ['perbaikan', 'tolak'], true) && $catatan === '') {
            return back()->withInput()->withErrors(['catatan' => 'Catatan wajib diisi agar pengirim mengetahui alasan keputusan ini.']);
        }
        if ($data['keputusan'] === 'setuju' && count(array_unique($data['periksa'] ?? [])) < count(self::CHECKLIST)) {
            return back()->withInput()->withErrors(['periksa' => 'Konfirmasi seluruh pemeriksaan pada checklist sebelum menyetujui surat.']);
        if (
            in_array(
                $data['keputusan'],
                ['perbaikan', 'tolak'],
                true
            )
            && $catatan === ''
        ) {
            return back()->withInput()->withErrors([
                'catatan' => 'Catatan wajib diisi agar pengirim mengetahui alasan keputusan ini.',
            ]);
        }

        if (
            $data['keputusan'] === 'setuju'
            && count(array_unique($data['periksa'] ?? []))
                < count(self::CHECKLIST)
        ) {
            return back()->withInput()->withErrors([
                'periksa' => 'Konfirmasi seluruh pemeriksaan pada checklist sebelum menyetujui surat.',
            ]);
        }

        $putusan = self::KEPUTUSAN[$data['keputusan']];

        DB::transaction(function () use ($pengajuan, $user, $putusan, $catatan) {
            $p = PengajuanSurat::where('id_pengajuan_surat', $pengajuan->id_pengajuan_surat)->lockForUpdate()->firstOrFail();

            if (! in_array($p->status, [SuratOptions::TERKIRIM, SuratOptions::DALAM_REVIEW], true)) {
                throw ValidationException::withMessages(['keputusan' => 'Pengajuan ini sudah diputuskan dan tidak dapat diubah.']);
            }
            if ($p->status === SuratOptions::TERKIRIM) {
                PeninjauanSurat::create(['id_pengajuan_surat' => $p->id_pengajuan_surat, 'id_user' => $user->id_user, 'aksi' => SuratOptions::AKSI_MULAI]);
            }

            $p->update(['status' => $putusan['status'], 'catatan_review' => $catatan !== '' ? $catatan : null]);
            PeninjauanSurat::create([
                'id_pengajuan_surat' => $p->id_pengajuan_surat,
                'id_user'            => $user->id_user,
                'aksi'               => $putusan['aksi'],
                'catatan'            => $catatan !== '' ? $catatan : null,
            ]);

            // Surat CHR disetujui INTRAM -> status laporan Pemda menjadi "Verifikasi 2"
            if ($putusan['status'] === SuratOptions::DISETUJUI && $p->id_rute_subsidi) {
                $laporan = RuteSubsidi::where('id_rute_subsidi', $p->id_rute_subsidi)->lockForUpdate()->first();
                if ($laporan && $laporan->status_pengajuan === StatusPengajuan::V1) {
                    $laporan->update(['status_pengajuan' => StatusPengajuan::V2]);
                    VerifikasiPengajuan::create([
                        'id_rute_subsidi'    => $laporan->id_rute_subsidi,
                        'id_user'            => $user->id_user,
                        'tahap_verifikasi'   => StatusPengajuan::TAHAP_INTRAM,
                        'status_verifikasi'  => StatusPengajuan::V2,
                        'catatan_verifikasi' => 'Surat CHR ' . $p->nomor . ' disetujui INTRAM.',
                        'tanggal_verifikasi' => now(),
                    ]);
                }
            }
        });

        $pesan = 'Surat ' . $pengajuan->nomor . ' ' . $putusan['pesan'] . '.';
        if ($data['keputusan'] === 'setuju' && $user->bisa('rekomendasi_teknis.unggah')) {
            $pesan .= ' Lanjutkan dengan mengunggah Surat Rekomendasi Teknis di menu Hasil Unggahan.';
        }

        return redirect(route('approval.index', ['pilih' => $pengajuan->id_pengajuan_surat]) . '#detail')->with('success', $pesan);
    }

    /**
     * Minta Pemda mengajukan lagi untuk moda tambahan (mis. laporan Darat disetujui, tetapi rute juga butuh moda Laut).
     * Pengajuan dan Surat CHR moda yang sudah ada tidak berubah.
     */
    public function revisiPemda(Request $request, PengajuanSurat $pengajuan)
    {
        $user = $request->user();
        $this->pastikanMasuk($pengajuan);

        $rute = $pengajuan->rute;
        abort_unless($rute, 422, 'Surat ini tidak terhubung ke laporan Pemda.');

        $data = $request->validateWithBag('revisi', [
            'moda'      => ['required', 'array', 'min:1'],
            'moda.*'    => ['integer', 'exists:moda,id_moda'],
            'catatan'   => ['required', 'string', 'max:1000'],
        ], [
            'moda.required'    => 'Pilih minimal satu moda tambahan.',
            'catatan.required' => 'Catatan wajib diisi agar Pemda mengetahui alasan permintaan ini.',
            'catatan.max'      => 'Catatan maksimal :max karakter.',
        ]);

        $sudah = RuteSubsidi::where('id_pemda', $rute->id_pemda)->pluck('id_moda')->map(fn ($i) => (int) $i)->all();
        $diminta = PermintaanRevisi::where('id_pemda', $rute->id_pemda)->where('status', PermintaanRevisi::MENUNGGU)->pluck('id_moda')->map(fn ($i) => (int) $i)->all();
        $pilih = collect($data['moda'])->map(fn ($i) => (int) $i)->unique()->diff($sudah)->diff($diminta)->values();

        if ($pilih->isEmpty()) {
            return back()->withInput()->withErrors(['moda' => 'Moda yang dipilih sudah pernah diajukan atau sudah diminta.'], 'revisi');
        }

        $nama = Moda::whereIn('id_moda', $pilih)->orderBy('id_moda')->pluck('nama_moda')->implode(', ');

        DB::transaction(function () use ($pilih, $rute, $pengajuan, $user, $data, $nama) {
            foreach ($pilih as $idModa) {
                PermintaanRevisi::create([
                    'id_pemda'           => $rute->id_pemda,
                    'id_rute_asal'       => $rute->id_rute_subsidi,
                    'id_pengajuan_surat' => $pengajuan->id_pengajuan_surat,
                    'id_moda'            => $idModa,
                    'id_user'            => $user->id_user,
                    'catatan'            => trim($data['catatan']),
                    'status'             => PermintaanRevisi::MENUNGGU,
                ]);
            }
            PeninjauanSurat::create([
                'id_pengajuan_surat' => $pengajuan->id_pengajuan_surat,
                'id_user'            => $user->id_user,
                'aksi'               => SuratOptions::AKSI_REVISI,
                'catatan'            => 'Moda tambahan: ' . $nama . '. ' . trim($data['catatan']),
            ]);
        });

        return redirect(route('approval.index', ['pilih' => $pengajuan->id_pengajuan_surat]) . '#detail')
            ->with('success', 'Permintaan revisi dikirim ke Pemda untuk moda ' . $nama . '. Pengajuan moda ' . ($rute->moda->nama_moda ?? 'sebelumnya') . ' tidak berubah.');
    }

    // ------------------------------------------------------------------

    private function pastikanMasuk(PengajuanSurat $pengajuan): void
    {
        abort_if($pengajuan->status === SuratOptions::DRAF, 404);
    }

    /** Data panel detail: surat, laporan Pemda rujukan, riwayat, dan daftar permintaan revisi. */
    private function detail(PengajuanSurat $p): array
    {
        $p->load('dokumen', 'user', 'moda', 'rute', 'peninjauan.user', 'rekomendasi');
        $rute = $p->rute;
        $isi = ['pengajuan' => $p, 'checklist' => []];

        $wilayah = $rute ? $rute->kabKotaAsal->nama_lengkap ?? '' : '';
        $modaNama = mb_strtolower($p->moda->nama_moda ?? 'moda');
        foreach (self::CHECKLIST as $k => [$judul, $uraian]) {
            $isi['checklist'][$k] = [$judul, strtr($uraian, ['{moda}' => $modaNama, '{wilayah}' => $wilayah ?: 'terkait'])];
        }

        // Riwayat peninjauan (terbaru di atas)
        $riwayat = collect();
        foreach ($p->peninjauan as $x) {
            $riwayat->push(['judul' => $x->aksi, 'oleh' => 'User INTRAM · ' . ($x->user->full_name ?? '-'), 'waktu' => $x->created_at, 'catatan' => $x->catatan]);
        }
        $riwayat->push(['judul' => 'Surat dikirim', 'oleh' => ($p->user->full_name ?? 'User Moda') . ' → User INTRAM', 'waktu' => $p->dikirim_at, 'catatan' => null]);
        $awal = $p->dokumen->min('created_at');
        $riwayat->push(['judul' => 'Berkas diunggah', 'oleh' => $p->dokumen->count() . ' berkas pada pengajuan ini', 'waktu' => $awal, 'catatan' => null]);
        $isi['riwayatSurat'] = $riwayat->sortByDesc(fn ($e) => optional($e['waktu'])->getTimestamp() ?? 0)->values();

        $isi['bisaPutuskan'] = in_array($p->status, [SuratOptions::TERKIRIM, SuratOptions::DALAM_REVIEW], true);
        DB::transaction(function () use (
            $pengajuan,
            $user,
            $putusan,
            $catatan
        ) {
            // Kunci laporan induk terlebih dahulu.
            $laporan = null;

            if ($pengajuan->id_rute_subsidi) {
                $laporan = RuteSubsidi::where(
                    'id_rute_subsidi',
                    $pengajuan->id_rute_subsidi
                )->lockForUpdate()->firstOrFail();
            }

            // Kemudian kunci Surat CHR.
            $p = PengajuanSurat::where(
                'id_pengajuan_surat',
                $pengajuan->id_pengajuan_surat
            )->lockForUpdate()->firstOrFail();

            if (! in_array($p->status, [
                SuratOptions::TERKIRIM,
                SuratOptions::DALAM_REVIEW,
            ], true)) {
                throw ValidationException::withMessages([
                    'keputusan' => 'Pengajuan ini sudah diputuskan dan tidak dapat diubah.',
                ]);
            }

            if (
                (int) $p->id_rute_subsidi
                !== (int) ($laporan?->id_rute_subsidi)
            ) {
                throw ValidationException::withMessages([
                    'keputusan' => 'Rujukan laporan berubah. Muat ulang halaman.',
                ]);
            }

            if ($putusan['status'] === SuratOptions::DISETUJUI) {
                if ($laporan) {
                    if (
                        $laporan->status_pengajuan
                        !== StatusPengajuan::V1
                    ) {
                        throw ValidationException::withMessages([
                            'keputusan' => 'Status laporan Pemda harus Verifikasi 1 sebelum Surat CHR disetujui.',
                        ]);
                    }

                    if (
                        (int) $laporan->id_moda
                        !== (int) $p->id_moda
                    ) {
                        throw ValidationException::withMessages([
                            'keputusan' => 'Moda Surat CHR tidak sesuai dengan moda laporan Pemda.',
                        ]);
                    }
                }

                $dokumen = $p->dokumen()
                    ->lockForUpdate()
                    ->get();

                $disk = Storage::disk('local');

                foreach (SuratOptions::JENIS as $kode => $jenis) {
                    if (! $jenis['wajib']) {
                        continue;
                    }

                    $d = $dokumen->firstWhere('jenis', $kode);

                    if (! $d) {
                        throw ValidationException::withMessages([
                            'keputusan' => $jenis['nama']
                                . ' belum tersedia.',
                        ]);
                    }

                    if (trim((string) $d->nomor_surat) === '') {
                        throw ValidationException::withMessages([
                            'keputusan' => 'Nomor '
                                . $jenis['nama']
                                . ' belum diisi.',
                        ]);
                    }

                    $path = trim((string) $d->path_file);

                    if (
                        $path === ''
                        || ! $disk->exists($path)
                        || $disk->size($path) <= 0
                    ) {
                        throw ValidationException::withMessages([
                            'keputusan' => 'Berkas '
                                . $jenis['nama']
                                . ' tidak ditemukan atau kosong.',
                        ]);
                    }
                }
            }

            // Catat awal review jika belum pernah dimulai.
            if ($p->status === SuratOptions::TERKIRIM) {
                PeninjauanSurat::create([
                    'id_pengajuan_surat' => $p->id_pengajuan_surat,
                    'id_user' => $user->id_user,
                    'aksi' => SuratOptions::AKSI_MULAI,
                ]);
            }

            // Simpan keputusan CHR.
            $p->update([
                'status' => $putusan['status'],
                'catatan_review' => $catatan !== ''
                    ? $catatan
                    : null,
            ]);

            PeninjauanSurat::create([
                'id_pengajuan_surat' => $p->id_pengajuan_surat,
                'id_user' => $user->id_user,
                'aksi' => $putusan['aksi'],
                'catatan' => $catatan !== ''
                    ? $catatan
                    : null,
            ]);

            // CHR disetujui: Verifikasi 1 -> Verifikasi 2.
            if (
                $putusan['status'] === SuratOptions::DISETUJUI
                && $laporan
            ) {
                $laporan->update([
                    'status_pengajuan' => StatusPengajuan::V2,
                ]);

                VerifikasiPengajuan::create([
                    'id_rute_subsidi' => $laporan->id_rute_subsidi,
                    'id_user' => $user->id_user,
                    'tahap_verifikasi' => StatusPengajuan::TAHAP_INTRAM,
                    'status_verifikasi' => StatusPengajuan::V2,
                    'catatan_verifikasi' =>
                        'Surat CHR '
                        . $p->nomor
                        . ' disetujui INTRAM.',
                    'tanggal_verifikasi' => now(),
                ]);
            }
        });

        $pesan = 'Surat '
            . $pengajuan->nomor
            . ' '
            . $putusan['pesan']
            . '.';

        if (
            $data['keputusan'] === 'setuju'
            && $pengajuan->id_rute_subsidi
            && $user->bisa('rekomendasi_teknis.unggah')
        ) {
            $pesan .= ' Lanjutkan dengan mengunggah Surat Rekomendasi Teknis di menu Hasil Unggahan.';
        }

        return redirect(
            route('approval.index', [
                'pilih' => $pengajuan->id_pengajuan_surat,
            ]) . '#detail'
        )->with('success', $pesan);
    }

    /**
     * Meminta Pemda mengajukan moda tambahan.
     * Pengajuan moda sebelumnya tidak berubah.
     */
    public function revisiPemda(
        Request $request,
        PengajuanSurat $pengajuan
    ) {
        $user = $request->user();

        $this->pastikanMasuk($pengajuan);

        $rute = $pengajuan->rute;

        abort_unless(
            $rute,
            422,
            'Surat ini tidak terhubung ke laporan Pemda.'
        );

        $data = $request->validateWithBag('revisi', [
            'moda' => [
                'required',
                'array',
                'min:1',
            ],
            'moda.*' => [
                'integer',
                'exists:moda,id_moda',
            ],
            'catatan' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'moda.required' => 'Pilih minimal satu moda tambahan.',
            'catatan.required' => 'Catatan wajib diisi agar Pemda mengetahui alasan permintaan ini.',
            'catatan.max' => 'Catatan maksimal :max karakter.',
        ]);

        $catatan = trim((string) $data['catatan']);

        if ($catatan === '') {
            return back()->withInput()->withErrors([
                'catatan' => 'Catatan untuk Pemda wajib diisi.',
            ], 'revisi');
        }

        $nama = DB::transaction(function () use (
            $data,
            $rute,
            $pengajuan,
            $user,
            $catatan
        ) {
            // Kunci data induk Pemda.
            $pemda = DataPemda::where(
                'id_pemda',
                $rute->id_pemda
            )->lockForUpdate()->firstOrFail();

            // Kunci laporan asal.
            $laporan = RuteSubsidi::where(
                'id_rute_subsidi',
                $rute->id_rute_subsidi
            )->lockForUpdate()->firstOrFail();

            // Kunci Surat CHR.
            $surat = PengajuanSurat::where(
                'id_pengajuan_surat',
                $pengajuan->id_pengajuan_surat
            )->lockForUpdate()->firstOrFail();

            $this->pastikanMasuk($surat);

            abort_unless(
                (int) $laporan->id_pemda === (int) $pemda->id_pemda
                && (int) $surat->id_rute_subsidi
                    === (int) $laporan->id_rute_subsidi,
                422,
                'Rujukan laporan tidak sesuai.'
            );

            // Periksa moda yang sudah diajukan.
            $sudah = RuteSubsidi::where(
                'id_pemda',
                $pemda->id_pemda
            )->pluck('id_moda')
                ->map(fn ($i) => (int) $i)
                ->all();

            // Periksa permintaan yang belum dipenuhi.
            $diminta = PermintaanRevisi::where(
                'id_pemda',
                $pemda->id_pemda
            )->where(
                'status',
                PermintaanRevisi::MENUNGGU
            )->pluck('id_moda')
                ->map(fn ($i) => (int) $i)
                ->all();

            $pilih = collect($data['moda'])
                ->map(fn ($i) => (int) $i)
                ->unique()
                ->diff($sudah)
                ->diff($diminta)
                ->values();

            if ($pilih->isEmpty()) {
                throw ValidationException::withMessages([
                    'moda' => 'Moda yang dipilih sudah pernah diajukan atau sudah diminta.',
                ])->errorBag('revisi');
            }

            $namaModa = Moda::whereIn('id_moda', $pilih)
                ->orderBy('id_moda')
                ->pluck('nama_moda')
                ->implode(', ');

            foreach ($pilih as $idModa) {
                PermintaanRevisi::create([
                    'id_pemda' => $pemda->id_pemda,
                    'id_rute_asal' => $laporan->id_rute_subsidi,
                    'id_pengajuan_surat' => $surat->id_pengajuan_surat,
                    'id_moda' => $idModa,
                    'id_user' => $user->id_user,
                    'catatan' => $catatan,
                    'status' => PermintaanRevisi::MENUNGGU,
                ]);
            }

            PeninjauanSurat::create([
                'id_pengajuan_surat' => $surat->id_pengajuan_surat,
                'id_user' => $user->id_user,
                'aksi' => SuratOptions::AKSI_REVISI,
                'catatan' => 'Moda tambahan: '
                    . $namaModa
                    . '. '
                    . $catatan,
            ]);

            return $namaModa;
        });

        return redirect(
            route('approval.index', [
                'pilih' => $pengajuan->id_pengajuan_surat,
            ]) . '#detail'
        )->with(
            'success',
            'Permintaan revisi dikirim ke Pemda untuk moda '
            . $nama
            . '. Pengajuan moda '
            . ($rute->moda->nama_moda ?? 'sebelumnya')
            . ' tidak berubah.'
        );
    }

    /**
     * Surat draf tidak dapat diakses INTRAM.
     */
    private function pastikanMasuk(
        PengajuanSurat $pengajuan
    ): void {
        abort_if(
            $pengajuan->status === SuratOptions::DRAF,
            404
        );
    }

    /**
     * Data detail Surat CHR dan laporan Pemda.
     */
    private function detail(PengajuanSurat $p): array
    {
        $p->load(
            'dokumen',
            'user',
            'moda',
            'rute',
            'peninjauan.user',
            'rekomendasi'
        );

        $rute = $p->rute;

        $isi = [
            'pengajuan' => $p,
            'checklist' => [],
        ];

        $wilayah = $rute
            ? ($rute->kabKotaAsal->nama_lengkap ?? '')
            : '';

        $modaNama = mb_strtolower(
            $p->moda->nama_moda ?? 'moda'
        );

        foreach (self::CHECKLIST as $k => [$judul, $uraian]) {
            $isi['checklist'][$k] = [
                $judul,
                strtr($uraian, [
                    '{moda}' => $modaNama,
                    '{wilayah}' => $wilayah ?: 'terkait',
                ]),
            ];
        }

        // Riwayat peninjauan.
        $riwayat = collect();

        foreach ($p->peninjauan as $x) {
            $riwayat->push([
                'judul' => $x->aksi,
                'oleh' => 'User INTRAM · '
                    . ($x->user->full_name ?? '-'),
                'waktu' => $x->created_at,
                'catatan' => $x->catatan,
            ]);
        }

        $riwayat->push([
            'judul' => 'Surat dikirim',
            'oleh' => ($p->user->full_name ?? 'User Moda')
                . ' → User INTRAM',
            'waktu' => $p->dikirim_at,
            'catatan' => null,
        ]);

        $awal = $p->dokumen->min('created_at');

        $riwayat->push([
            'judul' => 'Berkas diunggah',
            'oleh' => $p->dokumen->count()
                . ' berkas pada pengajuan ini',
            'waktu' => $awal,
            'catatan' => null,
        ]);

        $isi['riwayatSurat'] = $riwayat
            ->sortByDesc(
                fn ($e) => optional($e['waktu'])
                    ->getTimestamp() ?? 0
            )
            ->values();

        $isi['bisaPutuskan'] = in_array(
            $p->status,
            [
                SuratOptions::TERKIRIM,
                SuratOptions::DALAM_REVIEW,
            ],
            true
        );

        $isi['permintaan'] = collect();
        $isi['modaTambahan'] = collect();

        if ($rute) {
            $isi += Permohonan::detail($rute);
            $isi['permintaan'] = PermintaanRevisi::with('moda', 'ruteBaru')->where('id_pemda', $rute->id_pemda)->orderByDesc('id_permintaan_revisi')->get();
            $sudah = RuteSubsidi::where('id_pemda', $rute->id_pemda)->pluck('id_moda')->map(fn ($i) => (int) $i)->all();
            $diminta = $isi['permintaan']->where('status', PermintaanRevisi::MENUNGGU)->pluck('id_moda')->map(fn ($i) => (int) $i)->all();
            $isi['modaTambahan'] = Moda::whereNotIn('id_moda', $sudah)->orderBy('id_moda')->get()
                ->map(fn ($m) => ['id' => $m->id_moda, 'nama' => $m->nama_moda, 'diminta' => in_array((int) $m->id_moda, $diminta, true)]);
            $rute->loadMissing(
                'tujuan',
                'detailKomoditasTujuan'
            );

            $isi += Permohonan::detail($rute);

            $isi['permintaan'] = PermintaanRevisi::with(
                'moda',
                'ruteBaru'
            )->where(
                'id_pemda',
                $rute->id_pemda
            )->orderByDesc(
                'id_permintaan_revisi'
            )->get();

            $sudah = RuteSubsidi::where(
                'id_pemda',
                $rute->id_pemda
            )->pluck('id_moda')
                ->map(fn ($i) => (int) $i)
                ->all();

            $diminta = $isi['permintaan']
                ->where(
                    'status',
                    PermintaanRevisi::MENUNGGU
                )
                ->pluck('id_moda')
                ->map(fn ($i) => (int) $i)
                ->all();

            $isi['modaTambahan'] = Moda::whereNotIn(
                'id_moda',
                $sudah
            )->orderBy('id_moda')
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id_moda,
                    'nama' => $m->nama_moda,
                    'diminta' => in_array(
                        (int) $m->id_moda,
                        $diminta,
                        true
                    ),
                ]);
        }

        return $isi;
    }

    private function filter(Request $r): array
    {
        $moda = (string) $r->query('moda', '');
        $status = (string) $r->query('status', '');
        $periode = (string) $r->query('periode', '');

        return [
            'q'       => trim((string) $r->query('q', '')),
            'moda'    => ctype_digit($moda) ? (int) $moda : null,
            'status'  => ($status !== 'draf' && array_key_exists($status, SuratOptions::STATUS_OPSI)) ? $status : null,
            'periode' => preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode) ? $periode : null,
        ];
    }

    private function terapkanPeriode($query, ?string $periode): void
    {
        if ($periode) {
            $query->whereYear('dikirim_at', (int) substr($periode, 0, 4))->whereMonth('dikirim_at', (int) substr($periode, 5, 2));
            'q' => trim((string) $r->query('q', '')),
            'moda' => ctype_digit($moda)
                ? (int) $moda
                : null,
            'status' => (
                $status !== 'draf'
                && array_key_exists(
                    $status,
                    SuratOptions::STATUS_OPSI
                )
            ) ? $status : null,
            'periode' => preg_match(
                '/^\d{4}-(0[1-9]|1[0-2])$/',
                $periode
            ) ? $periode : null,
        ];
    }

    private function terapkanPeriode(
        $query,
        ?string $periode
    ): void {
        if ($periode) {
            $query->whereYear(
                'dikirim_at',
                (int) substr($periode, 0, 4)
            )->whereMonth(
                'dikirim_at',
                (int) substr($periode, 5, 2)
            );
        }
    }

    private function query(array $f)
    {
        $q = PengajuanSurat::query()->with('user', 'moda', 'rute.kabKotaAsal')->where('status', '!=', SuratOptions::DRAF);

        if ($f['q'] !== '') {
            $like = '%' . addcslashes($f['q'], '\\%_') . '%';
            $q->where(function ($w) use ($f, $like) {
                $w->whereHas('user', fn ($u) => $u->where('full_name', 'like', $like)->orWhere('instansi', 'like', $like))
                  ->orWhereHas('rute.kabKotaAsal', fn ($k) => $k->where('nama_kab_kota', 'like', $like));
                if (preg_match('/^(?:SR-?)?(?:\d{4}-?)?(\d{1,9})$/i', $f['q'], $m)) {
                    $w->orWhere('id_pengajuan_surat', (int) $m[1]);
                }
            });
        }
        if ($f['moda']) {
            $q->where('id_moda', $f['moda']);
        }
        if ($f['status']) {
            $q->where('status', SuratOptions::STATUS_OPSI[$f['status']]);
        }
        $this->terapkanPeriode($q, $f['periode']);

        return $q->orderByDesc('dikirim_at')->orderByDesc('id_pengajuan_surat');
        $q = PengajuanSurat::query()
            ->with(
                'user',
                'moda',
                'rute.kabKotaAsal'
            )
            ->where(
                'status',
                '!=',
                SuratOptions::DRAF
            );

        if ($f['q'] !== '') {
            $like = '%'
                . addcslashes($f['q'], '\\%_')
                . '%';

            $q->where(function ($w) use ($f, $like) {
                $w->whereHas('user', function ($u) use ($like) {
                    $u->where('full_name', 'like', $like)
                        ->orWhere('instansi', 'like', $like);
                })->orWhereHas(
                    'rute.kabKotaAsal',
                    fn ($k) => $k->where(
                        'nama_kab_kota',
                        'like',
                        $like
                    )
                );

                if (preg_match(
                    '/^(?:SR-?)?(?:\d{4}-?)?(\d{1,9})$/i',
                    $f['q'],
                    $m
                )) {
                    $w->orWhere(
                        'id_pengajuan_surat',
                        (int) $m[1]
                    );
                }
            });
        }

        if ($f['moda']) {
            $q->where('id_moda', $f['moda']);
        }

        if ($f['status']) {
            $q->where(
                'status',
                SuratOptions::STATUS_OPSI[$f['status']]
            );
        }

        $this->terapkanPeriode($q, $f['periode']);

        return $q
            ->orderByDesc('dikirim_at')
            ->orderByDesc('id_pengajuan_surat');
    }
}
