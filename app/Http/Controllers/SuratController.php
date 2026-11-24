<?php

namespace App\Http\Controllers;

use App\Models\DokumenSurat;
use App\Models\PengajuanSurat;
use App\Models\RuteSubsidi;
use App\Support\Paginasi;
use App\Support\Permohonan;
use App\Support\PeranUser;
use App\Support\StatusPengajuan;
use App\Support\SuratOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SuratController extends Controller
{
    private const PER_HALAMAN = 10;

    private const PER_LAPORAN = 5;

    private const STATUS_TERKUNCI = [
        SuratOptions::TERKIRIM,
        SuratOptions::DALAM_REVIEW,
        SuratOptions::DISETUJUI,
    ];

    /*
    |--------------------------------------------------------------------------
    | HALAMAN DAFTAR SURAT CHR
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | DAFTAR LAPORAN YANG SUDAH DISETUJUI
        |--------------------------------------------------------------------------
        */

        $lstatus = (string) $request->query('lstatus', '');

        $lf = [
            'q' => trim((string) $request->query('lq', '')),

            'status' => array_key_exists(
                $lstatus,
                SuratOptions::STATUS_LAPORAN_OPSI
            )
                ? $lstatus
                : null,
        ];

        $laporan = Permohonan::untukModa($user)
            ->whereIn(
                'status_pengajuan',
                StatusPengajuan::GRUP['disetujui']
            )
            ->with([
                'kabKotaAsal.provinsi',
                'kabKotaAsal.wilayah',
                'pemda.user',
                'verifikasi',
                'pengajuanSurat',
            ])

            ->when($lf['q'] !== '', function ($q) use ($lf) {
                $like = '%'
                    . addcslashes($lf['q'], '\\%_')
                    . '%';

                $q->where(function ($w) use ($lf, $like) {
                    $w->whereHas(
                        'kabKotaAsal',
                        function ($k) use ($like) {
                            $k->where(
                                'nama_kab_kota',
                                'like',
                                $like
                            )
                            ->orWhereHas(
                                'provinsi',
                                fn ($p) => $p->where(
                                    'nama_provinsi',
                                    'like',
                                    $like
                                )
                            );
                        }
                    )
                    ->orWhereHas(
                        'pemda.user',
                        fn ($u) => $u->where(
                            'instansi',
                            'like',
                            $like
                        )
                    );

                    if (
                        preg_match(
                            '/^(?:LP-?)?(?:\d{4}-?)?(\d{1,9})$/i',
                            $lf['q'],
                            $m
                        )
                    ) {
                        $w->orWhere(
                            'id_rute_subsidi',
                            (int) $m[1]
                        );
                    }
                });
            })

            ->when(
                $lf['status'] === 'belum',
                fn ($q) => $q->whereDoesntHave(
                    'pengajuanSurat'
                )
            )

            ->when(
                $lf['status']
                    && $lf['status'] !== 'belum',

                function ($q) use ($lf) {
                    $q->whereRaw(
                        '(SELECT ps.status
                          FROM pengajuan_surat ps
                          WHERE ps.id_rute_subsidi =
                                rute_subsidi.id_rute_subsidi
                          ORDER BY ps.id_pengajuan_surat DESC
                          LIMIT 1) = ?',

                        [
                            SuratOptions::STATUS_OPSI[
                                $lf['status']
                            ]
                        ]
                    );
                }
            )

            ->orderByDesc('updated_at')
            ->orderByDesc('id_rute_subsidi')

            ->paginate(
                self::PER_LAPORAN,
                ['*'],
                'lpage'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | RIWAYAT SURAT CHR
        |--------------------------------------------------------------------------
        */

        $status = (string) $request->query('status', '');

        $filter = [
            'q' => trim((string) $request->query('q', '')),

            'status' => array_key_exists(
                $status,
                SuratOptions::STATUS_OPSI
            )
                ? $status
                : null,
        ];

        $riwayat = DokumenSurat::query()
            ->with('pengajuan.rute.kabKotaAsal')

            ->whereHas(
                'pengajuan',
                function ($p) use ($user, $filter) {
                    if ($user->id_moda === null) {
                        $p->whereRaw('1 = 0');
                    } else {
                        $p->where(
                            'id_moda',
                            $user->id_moda
                        );
                    }

                    if ($filter['status']) {
                        $p->where(
                            'status',
                            SuratOptions::STATUS_OPSI[
                                $filter['status']
                            ]
                        );
                    }
                }
            )

            ->when(
                $filter['q'] !== '',
                function ($q) use ($filter) {
                    $like = '%'
                        . addcslashes($filter['q'], '\\%_')
                        . '%';

                    $kodeJenis = collect(SuratOptions::JENIS)
                        ->filter(
                            fn ($j) => mb_stripos(
                                $j['nama'],
                                $filter['q']
                            ) !== false
                        )
                        ->keys()
                        ->all();

                    $q->where(
                        function ($w) use (
                            $like,
                            $kodeJenis,
                            $filter
                        ) {
                            $w->where(
                                'nama_file',
                                'like',
                                $like
                            )
                            ->orWhereIn(
                                'jenis',
                                $kodeJenis
                            );

                            if (
                                preg_match(
                                    '/^(?:SR-?)?(?:\d{4}-?)?(\d{1,9})$/i',
                                    $filter['q'],
                                    $m
                                )
                            ) {
                                $w->orWhere(
                                    'id_pengajuan_surat',
                                    (int) $m[1]
                                );
                            }
                        }
                    );
                }
            )

            ->orderByDesc('created_at')
            ->orderByDesc('id_dokumen_surat')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('surat.index', [
            'laporan' => $laporan,

            'lf' => $lf,

            'laporanOpsi' =>
                SuratOptions::STATUS_LAPORAN_OPSI,

            'halamanLaporan' => Paginasi::nomor(
                $laporan->currentPage(),
                $laporan->lastPage()
            ),

            'riwayat' => $riwayat,

            'filter' => $filter,

            'statusOpsi' => SuratOptions::STATUS_OPSI,

            'halaman' => Paginasi::nomor(
                $riwayat->currentPage(),
                $riwayat->lastPage()
            ),

            'jumlahWajib' => SuratOptions::jumlahWajib(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL LAPORAN DAN FORM CHR
    |--------------------------------------------------------------------------
    */

    public function laporan(
        Request $request,
        RuteSubsidi $rute
    ) {
        $user = $request->user();

        $this->pastikanLaporan($user, $rute);

        $semua = PengajuanSurat::with(
            'dokumen',
            'user'
        )
            ->where(
                'id_rute_subsidi',
                $rute->id_rute_subsidi
            )
            ->where('id_moda', $user->id_moda)
            ->orderByDesc('id_pengajuan_surat')
            ->get();

        $draf = $semua->firstWhere(
            'status',
            SuratOptions::DRAF
        );

        $terkunci = $semua->first(
            fn ($p) => in_array(
                $p->status,
                self::STATUS_TERKUNCI,
                true
            )
        );

        $berkasDraf = $draf
            ? $draf->dokumen->keyBy('jenis')
            : collect();

        $slot = [];

        foreach (SuratOptions::JENIS as $kode => $j) {
            $slot[] = [
                'kode' => $kode,
                'nama' => $j['nama'],
                'wajib' => $j['wajib'],
                'dokumen' => $berkasDraf->get($kode),
            ];
        }

        return view(
            'surat.laporan',

            Permohonan::detail($rute) + [
                'draf' => $draf,

                'terkunci' => $terkunci,

                'perbaikan' => $draf
                    ? null
                    : $semua->first(
                        fn ($p) => in_array(
                            $p->status,
                            [
                                SuratOptions::PERLU_PERBAIKAN,
                                SuratOptions::DITOLAK,
                            ],
                            true
                        )
                    ),

                'slot' => $slot,

                'jumlahWajib' =>
                    SuratOptions::jumlahWajib(),

                'terlampir' =>
                    $berkasDraf->count(),

                'totalUkuran' =>
                    (int) $berkasDraf->sum('ukuran'),

                'bisaUnggah' =>
                    $user->bisa('chr.unggah'),

                'maksMb' =>
                    intdiv(SuratOptions::MAKS_KB, 1024),

                'riwayatSurat' => $semua
                    ->flatMap(
                        fn ($p) => $p->dokumen->map(
                            fn ($d) => $d->setRelation(
                                'pengajuan',
                                $p
                            )
                        )
                    )
                    ->sortByDesc('id_dokumen_surat')
                    ->values(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN DRAF ATAU KIRIM CHR KE INTRAM
    |--------------------------------------------------------------------------
    */

    public function simpan(
        Request $request,
        RuteSubsidi $rute
    ) {
        $user = $request->user();

        abort_unless(
            $user->isModa()
                && $user->bisa('chr.unggah'),
            403
        );

        $this->pastikanLaporan($user, $rute);

        /*
        |--------------------------------------------------------------------------
        | VALIDASI INPUT
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'aksi' => [
                'required',
                Rule::in(['draf', 'kirim']),
            ],

            'catatan' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'berkas' => [
                'nullable',
                'array',
            ],

            'berkas.*' => [
                'file',
                'mimes:pdf',
                'max:' . SuratOptions::MAKS_KB,
            ],

            'nomor' => [
                'nullable',
                'array',
            ],

            'nomor.*' => [
                'nullable',
                'string',
                'max:100',
            ],

        ], [
            'nomor.*.max' =>
                'Nomor surat maksimal :max karakter.',

            'catatan.max' =>
                'Catatan maksimal :max karakter.',

            'berkas.*.mimes' =>
                'Berkas harus berformat PDF.',

            'berkas.*.max' =>
                'Ukuran berkas maksimal '
                . intdiv(SuratOptions::MAKS_KB, 1024)
                . ' MB.',

            'berkas.*.uploaded' =>
                'Berkas gagal diunggah. Periksa batas upload_max_filesize pada server.',

            'berkas.*.file' =>
                'Berkas tidak valid.',
        ]);

        $berkas = $request->file('berkas', []) ?? [];

        /*
        | Tolak jenis dokumen yang tidak terdaftar.
        */

        if (
            array_diff(
                array_keys($berkas),
                array_keys(SuratOptions::JENIS)
            ) !== []
        ) {
            throw ValidationException::withMessages([
                'berkas' => 'Jenis dokumen tidak valid.',
            ]);
        }

        if (
            array_diff(
                array_keys($data['nomor'] ?? []),
                array_keys(SuratOptions::JENIS)
            ) !== []
        ) {
            throw ValidationException::withMessages([
                'nomor' => 'Jenis nomor surat tidak valid.',
            ]);
        }

        $tersimpan = [];

        $usang = [];

        $nomorSurat = '';

        $disk = Storage::disk('local');

        try {
            DB::transaction(function () use (
                $user,
                $rute,
                $data,
                $berkas,
                $disk,
                &$tersimpan,
                &$usang,
                &$nomorSurat
            ) {
                /*
                |--------------------------------------------------------------------------
                | KUNCI LAPORAN INDUK
                |--------------------------------------------------------------------------
                |
                | Semua penyimpanan CHR untuk laporan yang sama
                | harus melalui penguncian ini terlebih dahulu.
                |
                */

                $laporan = RuteSubsidi::where(
                    'id_rute_subsidi',
                    $rute->id_rute_subsidi
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                | Periksa ulang kepemilikan dan status laporan
                | setelah mendapatkan penguncian.
                */

                $this->pastikanLaporan(
                    $user,
                    $laporan
                );

                /*
                |--------------------------------------------------------------------------
                | CEGAH PENGAJUAN CHR GANDA
                |--------------------------------------------------------------------------
                */

                $suratTerkunci = PengajuanSurat::where(
                    'id_rute_subsidi',
                    $laporan->id_rute_subsidi
                )
                    ->whereIn(
                        'status',
                        self::STATUS_TERKUNCI
                    )
                    ->lockForUpdate()
                    ->first();

                if ($suratTerkunci) {
                    throw ValidationException::withMessages([
                        'berkas' =>
                            'Surat CHR untuk laporan ini sudah dikirim.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | AMBIL ATAU BUAT DRAF
                |--------------------------------------------------------------------------
                */

                $draf = PengajuanSurat::where(
                    'id_rute_subsidi',
                    $laporan->id_rute_subsidi
                )
                    ->where('id_moda', $user->id_moda)
                    ->where('status', SuratOptions::DRAF)
                    ->orderByDesc('id_pengajuan_surat')
                    ->lockForUpdate()
                    ->first();

                if (!$draf) {
                    $draf = PengajuanSurat::create([
                        'id_user' => $user->id_user,

                        'id_moda' => $user->id_moda,

                        'id_rute_subsidi' =>
                            $laporan->id_rute_subsidi,

                        'tujuan' => PeranUser::INTRAM,

                        'status' => SuratOptions::DRAF,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | CATATAN PENGIRIMAN
                |--------------------------------------------------------------------------
                */

                $catatan = trim(
                    (string) ($data['catatan'] ?? '')
                );

                $draf->catatan_pengiriman =
                    $catatan !== ''
                        ? $catatan
                        : null;

                /*
                |--------------------------------------------------------------------------
                | VALIDASI NOMOR SURAT
                |--------------------------------------------------------------------------
                */

                $ambilNomor = fn (string $kode): string =>
                    trim(
                        (string) ($data['nomor'][$kode] ?? '')
                    );

                $wajibNomor = fn (string $kode) =>
                    ValidationException::withMessages([
                        'nomor.' . $kode =>
                            'Nomor surat wajib diisi untuk '
                            . (
                                SuratOptions::JENIS[$kode]['nama']
                                ?? $kode
                            )
                            . '.',
                    ]);

                /*
                |--------------------------------------------------------------------------
                | SIMPAN DOKUMEN BARU
                |--------------------------------------------------------------------------
                */

                foreach ($berkas as $jenis => $file) {
                    $nomorDokumen = $ambilNomor($jenis);

                    if ($nomorDokumen === '') {
                        throw $wajibNomor($jenis);
                    }

                    if (!$file->isValid()) {
                        throw ValidationException::withMessages([
                            'berkas.' . $jenis =>
                                'Berkas gagal diunggah atau tidak valid.',
                        ]);
                    }

                    $path = $file->store(
                        "surat_chr/{$draf->id_pengajuan_surat}",
                        'local'
                    );

                    /*
                    | Jangan simpan record database jika proses
                    | penyimpanan file gagal.
                    */

                    if (
                        !is_string($path)
                        || $path === ''
                    ) {
                        throw ValidationException::withMessages([
                            'berkas.' . $jenis =>
                                'Berkas gagal disimpan ke server.',
                        ]);
                    }

                    $tersimpan[] = $path;

                    if (!$disk->exists($path)) {
                        throw ValidationException::withMessages([
                            'berkas.' . $jenis =>
                                'Berkas tidak ditemukan setelah proses upload.',
                        ]);
                    }

                    $atribut = [
                        'nama_file' =>
                            $file->getClientOriginalName(),

                        'nomor_surat' =>
                            $nomorDokumen,

                        'path_file' =>
                            $path,

                        'ukuran' =>
                            (int) $file->getSize(),
                    ];

                    /*
                    | Jika jenis surat sudah tersedia,
                    | ganti dokumen lama dengan dokumen baru.
                    */

                    $lama = $draf->dokumen()
                        ->where('jenis', $jenis)
                        ->lockForUpdate()
                        ->first();

                    if ($lama) {
                        $usang[] = $lama->path_file;

                        $lama->update($atribut);
                    } else {
                        DokumenSurat::create(
                            $atribut + [
                                'id_pengajuan_surat' =>
                                    $draf->id_pengajuan_surat,

                                'jenis' => $jenis,
                            ]
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | PERBARUI NOMOR SURAT YANG SUDAH TERSIMPAN
                |--------------------------------------------------------------------------
                */

                foreach ($draf->dokumen()->get() as $ada) {
                    if (
                        array_key_exists(
                            $ada->jenis,
                            $berkas
                        )
                    ) {
                        continue;
                    }

                    $n = $ambilNomor($ada->jenis);

                    if ($n === '') {
                        throw $wajibNomor($ada->jenis);
                    }

                    if ($n !== $ada->nomor_surat) {
                        $ada->update([
                            'nomor_surat' => $n,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | VALIDASI SEBELUM DIKIRIM KE INTRAM
                |--------------------------------------------------------------------------
                */

                if ($data['aksi'] === 'kirim') {
                    $dokumen = $draf->dokumen()->get();

                    $dokumenPerJenis = $dokumen
                        ->keyBy('jenis');

                    /*
                    | Pastikan seluruh dokumen wajib tersedia.
                    */

                    foreach (
                        SuratOptions::JENIS as $kode => $j
                    ) {
                        if (
                            $j['wajib']
                            && !$dokumenPerJenis->has($kode)
                        ) {
                            throw ValidationException::withMessages([
                                'berkas' =>
                                    $j['nama']
                                    . ' wajib diunggah sebelum dikirim.',
                            ]);
                        }
                    }

                    /*
                    | Periksa seluruh dokumen, termasuk
                    | dokumen tambahan yang bersifat opsional.
                    */

                    foreach ($dokumen as $ada) {
                        if (
                            trim(
                                (string) $ada->nomor_surat
                            ) === ''
                        ) {
                            throw $wajibNomor($ada->jenis);
                        }

                        if (
                            !$ada->path_file
                            || !$disk->exists($ada->path_file)
                        ) {
                            throw ValidationException::withMessages([
                                'berkas.' . $ada->jenis =>
                                    'Berkas '
                                    . $ada->nama_jenis
                                    . ' tidak ditemukan. Silakan unggah ulang.',
                            ]);
                        }

                        if (
                            $disk->size($ada->path_file) <= 0
                        ) {
                            throw ValidationException::withMessages([
                                'berkas.' . $ada->jenis =>
                                    'Berkas '
                                    . $ada->nama_jenis
                                    . ' kosong. Silakan unggah ulang.',
                            ]);
                        }
                    }

                    /*
                    | Seluruh validasi berhasil.
                    | Ubah status CHR menjadi Terkirim.
                    */

                    $draf->status =
                        SuratOptions::TERKIRIM;

                    $draf->dikirim_at = now();
                }

                $draf->save();

                $nomorSurat = $draf->nomor;
            });

        } catch (ValidationException $e) {
            /*
            | Transaksi gagal karena validasi.
            | Hapus file baru yang terlanjur tersimpan.
            */

            $this->bersihkanBerkas($tersimpan);

            throw $e;

        } catch (
            \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e
        ) {
            $this->bersihkanBerkas($tersimpan);

            throw $e;

        } catch (\Throwable $e) {
            /*
            | Transaksi gagal karena kesalahan sistem.
            */

            $this->bersihkanBerkas($tersimpan);

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Surat gagal disimpan. Silakan coba lagi atau hubungi administrator.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | HAPUS FILE LAMA SETELAH TRANSAKSI BERHASIL
        |--------------------------------------------------------------------------
        */

        $this->bersihkanBerkas($usang);

        return redirect()
            ->route(
                'surat.laporan',
                $rute->id_rute_subsidi
            )
            ->with(
                'success',

                $data['aksi'] === 'kirim'

                    ? 'Surat '
                        . $nomorSurat
                        . ' untuk '
                        . $rute->nomor_laporan
                        . ' berhasil dikirim ke User INTRAM.'

                    : 'Draf '
                        . $nomorSurat
                        . ' berhasil disimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS DOKUMEN DRAF
    |--------------------------------------------------------------------------
    */

    public function hapus(
        Request $request,
        DokumenSurat $dokumen
    ) {
        $user = $request->user();

        abort_unless(
            $user->isModa()
                && $user->bisa('chr.unggah'),
            403
        );

        $pengajuan = $dokumen->pengajuan;

        abort_unless(
            $pengajuan
                && $this->milikModa($user, $pengajuan),
            404
        );

        $idPengajuan =
            $pengajuan->id_pengajuan_surat;

        $idLaporan =
            $pengajuan->id_rute_subsidi;

        $idDokumen =
            $dokumen->id_dokumen_surat;

        /*
        | Penguncian dilakukan dengan urutan yang sama
        | seperti proses simpan:
        |
        | 1. Laporan induk.
        | 2. Pengajuan CHR.
        | 3. Dokumen.
        */

        $hasil = DB::transaction(function () use (
            $user,
            $idPengajuan,
            $idLaporan,
            $idDokumen
        ) {
            if ($idLaporan !== null) {
                $laporan = RuteSubsidi::where(
                    'id_rute_subsidi',
                    $idLaporan
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    $user->id_moda !== null
                    && (int) $laporan->id_moda
                        === (int) $user->id_moda,
                    404
                );
            }

            $surat = PengajuanSurat::where(
                'id_pengajuan_surat',
                $idPengajuan
            )
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $this->milikModa($user, $surat),
                404
            );

            /*
            | Periksa ulang status setelah dikunci.
            | Dokumen hanya dapat dihapus jika masih Draf.
            */

            abort_unless(
                $surat->status === SuratOptions::DRAF,
                403,
                'Surat yang sudah dikirim tidak dapat dihapus.'
            );

            $berkas = DokumenSurat::where(
                'id_dokumen_surat',
                $idDokumen
            )
                ->where(
                    'id_pengajuan_surat',
                    $idPengajuan
                )
                ->lockForUpdate()
                ->firstOrFail();

            $hasil = [
                'path' => $berkas->path_file,

                'nama' => $berkas->nama_jenis,

                'id_rute_subsidi' =>
                    $surat->id_rute_subsidi,
            ];

            $berkas->delete();

            return $hasil;
        });

        /*
        | File fisik dihapus setelah perubahan database
        | berhasil disimpan.
        */

        $this->bersihkanBerkas([
            $hasil['path'],
        ]);

        $tujuan = $hasil['id_rute_subsidi']

            ? route(
                'surat.laporan',
                $hasil['id_rute_subsidi']
            )

            : route('surat.index');

        return redirect($tujuan)
            ->with(
                'success',
                'Berkas '
                    . $hasil['nama']
                    . ' dihapus dari draf.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PRATINJAU DAN UNDUH DOKUMEN CHR
    |--------------------------------------------------------------------------
    */

    public function dokumen(
        Request $request,
        DokumenSurat $dokumen
    ) {
        $user = $request->user();

        $pengajuan = $dokumen->pengajuan;

        abort_unless(
            $pengajuan
                && $pengajuan->bisaDiaksesOleh($user),
            404
        );

        $disk = Storage::disk('local');

        abort_unless(
            $dokumen->path_file
                && $disk->exists($dokumen->path_file),
            404
        );

        return $request->boolean('unduh')

            ? $disk->download(
                $dokumen->path_file,
                $dokumen->nama_file
            )

            : $disk->response(
                $dokumen->path_file,
                $dokumen->nama_file
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PEMERIKSAAN AKSES LAPORAN
    |--------------------------------------------------------------------------
    */

    private function pastikanLaporan(
        $user,
        RuteSubsidi $rute
    ): void {
        abort_unless(
            $user->isModa()
                && $user->id_moda !== null
                && (int) $rute->id_moda
                    === (int) $user->id_moda,
            404
        );

        abort_unless(
            $rute->grup_status === 'disetujui',
            403,
            'Surat CHR hanya dapat dibuat untuk laporan yang sudah disetujui.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PEMERIKSAAN KEPEMILIKAN MODA
    |--------------------------------------------------------------------------
    */

    private function milikModa(
        $user,
        PengajuanSurat $pengajuan
    ): bool {
        return $user->isModa()
            && $user->id_moda !== null
            && (int) $pengajuan->id_moda
                === (int) $user->id_moda;
    }

    /*
    |--------------------------------------------------------------------------
    | PEMBERSIHAN FILE
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk membersihkan:
    | - File baru ketika transaksi gagal.
    | - File lama setelah berhasil diganti.
    | - File dokumen draf setelah dihapus.
    |
    */

    private function bersihkanBerkas(array $paths): void
    {
        $paths = array_unique(
            array_filter(
                $paths,
                fn ($path) =>
                    is_string($path)
                    && $path !== ''
            )
        );

        if (empty($paths)) {
            return;
        }

        $disk = Storage::disk('local');

        foreach ($paths as $path) {
            try {
                if (
                    $disk->exists($path)
                    && !$disk->delete($path)
                ) {
                    report(
                        new \RuntimeException(
                            'Gagal membersihkan berkas CHR: '
                            . $path
                        )
                    );
                }
            } catch (\Throwable $e) {
                /*
                | Kegagalan pembersihan file dicatat
                | tanpa mengubah hasil transaksi database.
                */

                report($e);
            }
        }
    }
}
