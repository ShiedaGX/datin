<?php

namespace App\Http\Controllers;

use App\Models\Moda;
use App\Models\RekomendasiTeknis;
use App\Models\RuteSubsidi;
use App\Models\VerifikasiPengajuan;
use App\Support\Paginasi;
use App\Support\Permohonan;
use App\Support\StatusPengajuan;
use App\Support\SuratOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Hasil Unggahan (INTRAM): INTRAM melihat unggahan Pemda dan Surat CHR moda untuk setiap laporan, dan
 * setelah Surat CHR DISETUJUI mengunggah Surat Rekomendasi Teknis. Surat Rekomendasi Teknis kemudian
 * tampil pada Pemda pemilik laporan dan pada Moda yang bersangkutan.
 */
class RekomendasiController extends Controller
{
    private const PER_HALAMAN = 10;

    /** kunci filter => label */
    private const STATUS_OPSI = [
        'menunggu-chr' => 'Menunggu CHR disetujui',
        'menunggu-rt'  => 'Menunggu Rekomendasi Teknis',
        'terunggah'    => 'Rekomendasi Teknis terunggah',
    private const STATUS_OPSI = [
        'menunggu-chr' => 'Menunggu CHR disetujui',
        'menunggu-rt' => 'Menunggu Rekomendasi Teknis',
        'terunggah' => 'Rekomendasi Teknis terunggah',
    ];

    public function index(Request $request)
    {
        $f = $this->filter($request);

        $data = RuteSubsidi::query()
            ->whereIn('status_pengajuan', StatusPengajuan::GRUP['disetujui'])
            ->with('kabKotaAsal.provinsi', 'kabKotaAsal.wilayah', 'moda', 'pengajuanSurat', 'rekomendasi')
            ->when($f['q'] !== '', function ($q) use ($f) {
                $like = '%' . addcslashes($f['q'], '\\%_') . '%';
                $q->where(function ($w) use ($f, $like) {
                    $w->whereHas('kabKotaAsal', function ($k) use ($like) {
                        $k->where('nama_kab_kota', 'like', $like)
                          ->orWhereHas('provinsi', fn ($p) => $p->where('nama_provinsi', 'like', $like));
                    });
                    if (preg_match('/^(?:LP-?)?(?:\d{4}-?)?(\d{1,9})$/i', $f['q'], $m)) {
                        $w->orWhere('id_rute_subsidi', (int) $m[1]);
                    }
                });
            })
            ->when($f['moda'], fn ($q, $m) => $q->where('id_moda', $m))
            ->when($f['status'] === 'menunggu-chr', fn ($q) => $q->whereDoesntHave('pengajuanSurat', fn ($p) => $p->where('status', SuratOptions::DISETUJUI)))
            ->when($f['status'] === 'menunggu-rt', fn ($q) => $q
                ->whereHas('pengajuanSurat', fn ($p) => $p->where('status', SuratOptions::DISETUJUI))
                ->whereDoesntHave('rekomendasi'))
            ->when($f['status'] === 'terunggah', fn ($q) => $q->whereHas('rekomendasi'))
            ->orderByDesc('updated_at')->orderByDesc('id_rute_subsidi')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        return view('hasil.index', [
            'data'       => $data,
            'filter'     => $f,
            'modaOpsi'   => Moda::orderBy('id_moda')->get(),
            'statusOpsi' => self::STATUS_OPSI,
            'halaman'    => Paginasi::nomor($data->currentPage(), $data->lastPage()),
            'bisaUnggah' => $request->user()->bisa('rekomendasi_teknis.unggah'),
        ]);
    }

            ->whereIn(
                'status_pengajuan',
                StatusPengajuan::GRUP['disetujui']
            )
            ->with(
                'kabKotaAsal.provinsi',
                'kabKotaAsal.wilayah',
                'moda',
                'pengajuanSurat',
                'rekomendasi'
            )
            ->when($f['q'] !== '', function ($q) use ($f) {
                $like = '%'
                    . addcslashes($f['q'], '\\%_')
                    . '%';

                $q->where(function ($w) use ($f, $like) {
                    $w->whereHas(
                        'kabKotaAsal',
                        function ($k) use ($like) {
                            $k->where(
                                'nama_kab_kota',
                                'like',
                                $like
                            )->orWhereHas(
                                'provinsi',
                                fn ($p) => $p->where(
                                    'nama_provinsi',
                                    'like',
                                    $like
                                )
                            );
                        }
                    );

                    if (preg_match(
                        '/^(?:LP-?)?(?:\d{4}-?)?(\d{1,9})$/i',
                        $f['q'],
                        $m
                    )) {
                        $w->orWhere(
                            'id_rute_subsidi',
                            (int) $m[1]
                        );
                    }
                });
            })
            ->when(
                $f['moda'],
                fn ($q, $m) => $q->where('id_moda', $m)
            )
            ->when(
                $f['status'] === 'menunggu-chr',
                fn ($q) => $q->whereDoesntHave(
                    'pengajuanSurat',
                    fn ($p) => $p->where(
                        'status',
                        SuratOptions::DISETUJUI
                    )
                )
            )
            ->when(
                $f['status'] === 'menunggu-rt',
                fn ($q) => $q
                    ->whereHas(
                        'pengajuanSurat',
                        fn ($p) => $p->where(
                            'status',
                            SuratOptions::DISETUJUI
                        )
                    )
                    ->whereDoesntHave('rekomendasi')
            )
            ->when(
                $f['status'] === 'terunggah',
                fn ($q) => $q->whereHas('rekomendasi')
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id_rute_subsidi')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('hasil.index', [
            'data' => $data,
            'filter' => $f,
            'modaOpsi' => Moda::orderBy('id_moda')->get(),
            'statusOpsi' => self::STATUS_OPSI,
            'halaman' => Paginasi::nomor(
                $data->currentPage(),
                $data->lastPage()
            ),
            'bisaUnggah' => $request->user()
                ->bisa('rekomendasi_teknis.unggah'),
        ]);
    }

    /**
     * Detail laporan dan rekomendasi teknis.
     */
    public function show(Request $request, RuteSubsidi $rute)
    {
        $this->pastikanLaporan($rute);

        $rute->load('pengajuanSurat.dokumen', 'pengajuanSurat.user', 'rekomendasi.user');
        $chr = $rute->pengajuanSurat->sortByDesc('id_pengajuan_surat')->first(fn ($p) => $p->status === SuratOptions::DISETUJUI);

        return view('hasil.show', Permohonan::detail($rute) + [
            'chr'          => $chr,
            'chrTerakhir'  => $rute->chr_terbaru,
            'rt'           => $rute->rekomendasi_terbaru,
            'bisaUnggah'   => $request->user()->bisa('rekomendasi_teknis.unggah'),
            'maksMb'       => intdiv(SuratOptions::MAKS_KB, 1024),
        ]);
    }

    public function simpan(Request $request, RuteSubsidi $rute)
    {
        $user = $request->user();
        $this->pastikanLaporan($rute);

        $data = $request->validate([
            'nomor_surat' => ['required', 'string', 'max:100'],
            'berkas'  => ['required', 'file', 'mimes:pdf', 'max:' . SuratOptions::MAKS_KB],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'nomor_surat.max'      => 'Nomor surat maksimal :max karakter.',
            'berkas.required' => 'Pilih berkas Surat Rekomendasi Teknis (PDF).',
            'berkas.mimes'    => 'Berkas harus berformat PDF.',
            'berkas.max'      => 'Ukuran berkas maksimal ' . intdiv(SuratOptions::MAKS_KB, 1024) . ' MB.',
            'berkas.uploaded' => 'Berkas gagal diunggah. Periksa batas upload_max_filesize pada server.',
            'catatan.max'     => 'Catatan maksimal :max karakter.',
        ]);

        $file = $request->file('berkas');
        $baru = null;
        $usang = null;

        try {
            DB::transaction(function () use ($rute, $user, $data, $file, &$baru, &$usang) {
                // Hanya untuk laporan yang Surat CHR-nya sudah DISETUJUI INTRAM
                $chr = $rute->pengajuanSurat()->where('status', SuratOptions::DISETUJUI)->orderByDesc('id_pengajuan_surat')->lockForUpdate()->first();
                if (! $chr) {
                    throw ValidationException::withMessages(['berkas' => 'Surat Rekomendasi Teknis hanya dapat diunggah setelah Surat CHR laporan ini disetujui.']);
                }

                $baru = $file->store("rekomendasi_teknis/{$chr->id_pengajuan_surat}", 'local');
                $catatan = trim((string) ($data['catatan'] ?? ''));
                $atribut = [
                    'id_user'   => $user->id_user,
                    'nama_file' => $file->getClientOriginalName(),
                    'nomor_surat' => trim($data['nomor_surat']),
                    'path_file' => $baru,
                    'ukuran'    => $file->getSize(),
                    'catatan'   => $catatan !== '' ? $catatan : null,
                ];

                $lama = RekomendasiTeknis::where('id_pengajuan_surat', $chr->id_pengajuan_surat)->first();
                if ($lama) {
                    $usang = $lama->path_file;
                    $lama->update($atribut);
                } else {
                    RekomendasiTeknis::create($atribut + ['id_pengajuan_surat' => $chr->id_pengajuan_surat]);

                    // Surat Rekomendasi Teknis pertama kali diunggah -> status laporan menjadi "Verifikasi Final"
                    $laporan = RuteSubsidi::where('id_rute_subsidi', $rute->id_rute_subsidi)->lockForUpdate()->first();
                    if ($laporan && in_array($laporan->status_pengajuan, [StatusPengajuan::V1, StatusPengajuan::V2], true)) {
                        $laporan->update(['status_pengajuan' => StatusPengajuan::FINAL]);
                        VerifikasiPengajuan::create([
                            'id_rute_subsidi'    => $laporan->id_rute_subsidi,
                            'id_user'            => $user->id_user,
                            'tahap_verifikasi'   => StatusPengajuan::TAHAP_INTRAM,
                            'status_verifikasi'  => StatusPengajuan::FINAL,
                            'catatan_verifikasi' => 'Surat Rekomendasi Teknis diunggah INTRAM.',
                            'tanggal_verifikasi' => now(),
                        ]);
                    }
                }
            });
        } catch (ValidationException $e) {
            if ($baru) {
                Storage::disk('local')->delete($baru);
            }

            throw $e;
        } catch (\Throwable $e) {
            if ($baru) {
                Storage::disk('local')->delete($baru);
            }
            report($e);

            return back()->with('error', 'Surat Rekomendasi Teknis gagal disimpan. Silakan coba lagi atau hubungi administrator.');
        }

        if ($usang) {
            Storage::disk('local')->delete($usang);
        }

        return redirect()->route('hasil.show', $rute->id_rute_subsidi)
            ->with('success', 'Surat Rekomendasi Teknis untuk ' . $rute->nomor_laporan . ' berhasil diunggah dan kini tampil pada Pemda dan Moda. Status laporan menjadi Verifikasi Final.');
    }

    /** Pratinjau (default) atau unduh (?unduh=1): INTRAM, Super Admin, Moda yang sama, dan Pemda pemilik laporan. */
    public function dokumen(Request $request, RekomendasiTeknis $rekomendasi)
    {
        $user = $request->user();
        $pengajuan = $rekomendasi->pengajuan;
        $rute = $pengajuan->rute;

        $boleh = $user->isSuperAdmin()
            || ($user->isIntram() && ($user->bisa('rekomendasi_teknis.unggah') || $user->bisa('cakupan.lihat_unggahan')))
            || ($user->isModa() && $user->id_moda !== null && (int) $pengajuan->id_moda === (int) $user->id_moda)
            || ($rute && $rute->pemda && $rute->pemda->id_user !== null && (int) $rute->pemda->id_user === (int) $user->id_user);

        abort_unless($boleh, 404);
        abort_unless(Storage::disk('local')->exists($rekomendasi->path_file), 404);

        return $request->boolean('unduh')
            ? Storage::disk('local')->download($rekomendasi->path_file, $rekomendasi->nama_file)
            : Storage::disk('local')->response($rekomendasi->path_file, $rekomendasi->nama_file);
    }

    // ------------------------------------------------------------------

    /** Hanya laporan yang sudah disetujui moda yang masuk ke Hasil Unggahan. */
    private function pastikanLaporan(RuteSubsidi $rute): void
    {
        abort_unless($rute->grup_status === 'disetujui', 404);
        $rute->load(
            'pengajuanSurat.dokumen',
            'pengajuanSurat.user',
            'rekomendasi.user',
            'tujuan',
            'detailKomoditasTujuan'
        );

        $chr = $rute->pengajuanSurat
            ->sortByDesc('id_pengajuan_surat')
            ->first(
                fn ($p) => $p->status
                    === SuratOptions::DISETUJUI
            );

        return view(
            'hasil.show',
            Permohonan::detail($rute) + [
                'chr' => $chr,
                'chrTerakhir' => $rute->chr_terbaru,
                'rt' => $rute->rekomendasi_terbaru,
                'bisaUnggah' => $request->user()
                    ->bisa('rekomendasi_teknis.unggah'),
                'maksMb' => intdiv(
                    SuratOptions::MAKS_KB,
                    1024
                ),
            ]
        );
    }

    /**
     * Unggah atau perbarui Surat Rekomendasi Teknis.
     */
    public function simpan(
        Request $request,
        RuteSubsidi $rute
    ) {
        $user = $request->user();

        $this->pastikanLaporan($rute);

        $data = $request->validate([
            'nomor_surat' => [
                'required',
                'string',
                'max:100',
            ],
            'berkas' => [
                'required',
                'file',
                'mimes:pdf',
                'max:' . SuratOptions::MAKS_KB,
            ],
            'catatan' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'nomor_surat.max' => 'Nomor surat maksimal :max karakter.',
            'berkas.required' => 'Pilih berkas Surat Rekomendasi Teknis (PDF).',
            'berkas.mimes' => 'Berkas harus berformat PDF.',
            'berkas.max' => 'Ukuran berkas maksimal '
                . intdiv(SuratOptions::MAKS_KB, 1024)
                . ' MB.',
            'berkas.uploaded' => 'Berkas gagal diunggah. Periksa batas upload_max_filesize pada server.',
            'catatan.max' => 'Catatan maksimal :max karakter.',
        ]);

        $nomorSurat = trim((string) $data['nomor_surat']);

        $catatan = trim((string) ($data['catatan'] ?? ''));

        if ($nomorSurat === '') {
            return back()->withInput()->withErrors([
                'nomor_surat' => 'Nomor surat wajib diisi.',
            ]);
        }

        $file = $request->file('berkas');

        $baru = null;
        $usang = null;
        $mengganti = false;

        try {
            DB::transaction(function () use (
                $rute,
                $user,
                $file,
                $nomorSurat,
                $catatan,
                &$baru,
                &$usang,
                &$mengganti
            ) {
                // Kunci laporan induk terlebih dahulu.
                $laporan = RuteSubsidi::where(
                    'id_rute_subsidi',
                    $rute->id_rute_subsidi
                )->lockForUpdate()->firstOrFail();

                // Kunci CHR yang sudah disetujui.
                $chr = $laporan->pengajuanSurat()
                    ->where(
                        'status',
                        SuratOptions::DISETUJUI
                    )
                    ->orderByDesc('id_pengajuan_surat')
                    ->lockForUpdate()
                    ->first();

                if (! $chr) {
                    throw ValidationException::withMessages([
                        'berkas' => 'Surat Rekomendasi Teknis hanya dapat diunggah setelah Surat CHR laporan ini disetujui.',
                    ]);
                }

                if (
                    (int) $chr->id_moda
                    !== (int) $laporan->id_moda
                ) {
                    throw ValidationException::withMessages([
                        'berkas' => 'Moda Surat CHR tidak sesuai dengan laporan Pemda.',
                    ]);
                }

                // Periksa rekomendasi yang sudah ada.
                $lama = RekomendasiTeknis::where(
                    'id_pengajuan_surat',
                    $chr->id_pengajuan_surat
                )->lockForUpdate()->first();

                $mengganti = $lama !== null;

                // Unggahan pertama wajib melalui Verifikasi 2.
                if (
                    ! $lama
                    && $laporan->status_pengajuan
                        !== StatusPengajuan::V2
                ) {
                    throw ValidationException::withMessages([
                        'berkas' => 'Laporan harus berstatus Verifikasi 2 sebelum Surat Rekomendasi Teknis pertama diunggah.',
                    ]);
                }

                // Penggantian dokumen hanya untuk laporan final.
                if (
                    $lama
                    && $laporan->status_pengajuan
                        !== StatusPengajuan::FINAL
                ) {
                    throw ValidationException::withMessages([
                        'berkas' => 'Status laporan dan rekomendasi teknis tidak konsisten. Hubungi administrator.',
                    ]);
                }

                // Simpan berkas baru.
                $baru = $file->store(
                    "rekomendasi_teknis/{$chr->id_pengajuan_surat}",
                    'local'
                );

                $disk = Storage::disk('local');

                if (
                    ! is_string($baru)
                    || $baru === ''
                    || ! $disk->exists($baru)
                    || $disk->size($baru) <= 0
                ) {
                    throw ValidationException::withMessages([
                        'berkas' => 'Berkas gagal disimpan pada server.',
                    ]);
                }

                $atribut = [
                    'id_user' => $user->id_user,
                    'nama_file' => $file->getClientOriginalName(),
                    'nomor_surat' => $nomorSurat,
                    'path_file' => $baru,
                    'ukuran' => $disk->size($baru),
                    'catatan' => $catatan !== ''
                        ? $catatan
                        : null,
                ];

                if ($lama) {
                    // Penggantian dokumen tidak mengulang verifikasi.
                    $usang = $lama->path_file;

                    $lama->update($atribut);
                } else {
                    RekomendasiTeknis::create(
                        $atribut + [
                            'id_pengajuan_surat' =>
                                $chr->id_pengajuan_surat,
                        ]
                    );

                    // Verifikasi 2 -> Verifikasi Final.
                    $laporan->update([
                        'status_pengajuan' =>
                            StatusPengajuan::FINAL,
                    ]);

                    VerifikasiPengajuan::create([
                        'id_rute_subsidi' =>
                            $laporan->id_rute_subsidi,
                        'id_user' => $user->id_user,
                        'tahap_verifikasi' =>
                            StatusPengajuan::TAHAP_INTRAM,
                        'status_verifikasi' =>
                            StatusPengajuan::FINAL,
                        'catatan_verifikasi' =>
                            'Surat Rekomendasi Teknis diunggah INTRAM.',
                        'tanggal_verifikasi' => now(),
                    ]);
                }
            });
        } catch (ValidationException $e) {
            $this->bersihkanBerkas($baru);

            throw $e;
        } catch (\Throwable $e) {
            $this->bersihkanBerkas($baru);

            report($e);

            return back()->with(
                'error',
                'Surat Rekomendasi Teknis gagal disimpan. Silakan coba lagi atau hubungi administrator.'
            );
        }

        // Hapus berkas lama hanya setelah transaksi berhasil.
        if ($usang && $usang !== $baru) {
            $this->bersihkanBerkas($usang);
        }

        $pesan = $mengganti
            ? 'Surat Rekomendasi Teknis untuk '
                . $rute->nomor_laporan
                . ' berhasil diperbarui. Status laporan tetap Verifikasi Final.'
            : 'Surat Rekomendasi Teknis untuk '
                . $rute->nomor_laporan
                . ' berhasil diunggah dan kini tampil pada Pemda dan Moda. Status laporan menjadi Verifikasi Final.';

        return redirect()
            ->route(
                'hasil.show',
                $rute->id_rute_subsidi
            )
            ->with('success', $pesan);
    }

    /**
     * Pratinjau atau unduh rekomendasi teknis.
     */
    public function dokumen(
        Request $request,
        RekomendasiTeknis $rekomendasi
    ) {
        $user = $request->user();

        $pengajuan = $rekomendasi->pengajuan;

        abort_unless($pengajuan, 404);

        $rute = $pengajuan->rute;

        $boleh = $user->isSuperAdmin()
            || (
                $user->isIntram()
                && (
                    $user->bisa('rekomendasi_teknis.unggah')
                    || $user->bisa('cakupan.lihat_unggahan')
                )
            )
            || (
                $user->isModa()
                && $user->id_moda !== null
                && (int) $pengajuan->id_moda
                    === (int) $user->id_moda
            )
            || (
                $rute
                && $rute->pemda
                && $rute->pemda->id_user !== null
                && (int) $rute->pemda->id_user
                    === (int) $user->id_user
            );

        abort_unless($boleh, 404);

        abort_unless(
            Storage::disk('local')->exists(
                $rekomendasi->path_file
            ),
            404
        );

        return $request->boolean('unduh')
            ? Storage::disk('local')->download(
                $rekomendasi->path_file,
                $rekomendasi->nama_file
            )
            : Storage::disk('local')->response(
                $rekomendasi->path_file,
                $rekomendasi->nama_file
            );
    }

    /**
     * Membersihkan berkas tanpa mengganggu transaksi
     * yang sudah berhasil.
     */
    private function bersihkanBerkas($path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        try {
            Storage::disk('local')->delete($path);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Hanya laporan yang sudah disetujui Moda.
     */
    private function pastikanLaporan(
        RuteSubsidi $rute
    ): void {
        abort_unless(
            $rute->grup_status === 'disetujui',
            404
        );
    }

    private function filter(Request $r): array
    {
        $moda = (string) $r->query('moda', '');
        $status = (string) $r->query('status', '');

        return [
            'q'      => trim((string) $r->query('q', '')),
            'moda'   => ctype_digit($moda) ? (int) $moda : null,
            'status' => array_key_exists($status, self::STATUS_OPSI) ? $status : null,

        $status = (string) $r->query('status', '');

        return [
            'q' => trim((string) $r->query('q', '')),
            'moda' => ctype_digit($moda)
                ? (int) $moda
                : null,
            'status' => array_key_exists(
                $status,
                self::STATUS_OPSI
            ) ? $status : null,
        ];
    }
}
