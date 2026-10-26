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

/**
 * Surat CHR: Moda mengunggah Surat Catatan Hasil Review untuk SATU laporan Pemda yang sudah
 * disetujui, lalu mengirimkannya ke INTRAM. Surat dikelola per moda: semua akun Moda dengan moda
 * yang sama melihat dan mengelola surat yang sama, sehingga tidak ada CHR ganda untuk satu laporan.
 */
class SuratController extends Controller
{
    private const PER_HALAMAN = 10;
    private const PER_LAPORAN = 5;

    /** Status surat yang mengunci laporan: CHR sudah dikirim dan tidak boleh dibuat lagi. */
    private const STATUS_TERKUNCI = [SuratOptions::TERKIRIM, SuratOptions::DALAM_REVIEW, SuratOptions::DISETUJUI];

    public function index(Request $request)
    {
        $user = $request->user();

        // ---- Permohonan yang sudah disetujui ----
        $lstatus = (string) $request->query('lstatus', '');
        $lf = [
            'q'      => trim((string) $request->query('lq', '')),
            'status' => array_key_exists($lstatus, SuratOptions::STATUS_LAPORAN_OPSI) ? $lstatus : null,
        ];

        $laporan = Permohonan::untukModa($user)
            ->whereIn('status_pengajuan', StatusPengajuan::GRUP['disetujui'])
            ->with('kabKotaAsal.provinsi', 'kabKotaAsal.wilayah', 'pemda.user', 'verifikasi', 'pengajuanSurat')
            ->when($lf['q'] !== '', function ($q) use ($lf) {
                $like = '%' . addcslashes($lf['q'], '\\%_') . '%';
                $q->where(function ($w) use ($lf, $like) {
                    $w->whereHas('kabKotaAsal', function ($k) use ($like) {
                        $k->where('nama_kab_kota', 'like', $like)
                          ->orWhereHas('provinsi', fn ($p) => $p->where('nama_provinsi', 'like', $like));
                    })->orWhereHas('pemda.user', fn ($u) => $u->where('instansi', 'like', $like));
                    if (preg_match('/^(?:LP-?)?(?:\d{4}-?)?(\d{1,9})$/i', $lf['q'], $m)) {
                        $w->orWhere('id_rute_subsidi', (int) $m[1]);
                    }
                });
            })
            ->when($lf['status'] === 'belum', fn ($q) => $q->whereDoesntHave('pengajuanSurat'))
            ->when($lf['status'] && $lf['status'] !== 'belum', function ($q) use ($lf) {
                // status SURAT TERBARU pada laporan itu
                $q->whereRaw(
                    '(SELECT ps.status FROM pengajuan_surat ps WHERE ps.id_rute_subsidi = rute_subsidi.id_rute_subsidi ORDER BY ps.id_pengajuan_surat DESC LIMIT 1) = ?',
                    [SuratOptions::STATUS_OPSI[$lf['status']]]
                );
            })
            ->orderByDesc('updated_at')->orderByDesc('id_rute_subsidi')
            ->paginate(self::PER_LAPORAN, ['*'], 'lpage')->withQueryString();

        // ---- Riwayat surat ----
        $status = (string) $request->query('status', '');
        $filter = [
            'q'      => trim((string) $request->query('q', '')),
            'status' => array_key_exists($status, SuratOptions::STATUS_OPSI) ? $status : null,
        ];

        $riwayat = DokumenSurat::query()
            ->with('pengajuan.rute.kabKotaAsal')
            ->whereHas('pengajuan', function ($p) use ($user, $filter) {
                $p->where('id_moda', $user->id_moda ?? 0);
                if ($filter['status']) {
                    $p->where('status', SuratOptions::STATUS_OPSI[$filter['status']]);
                }
            })
            ->when($filter['q'] !== '', function ($q) use ($filter) {
                $like = '%' . addcslashes($filter['q'], '\\%_') . '%';
                $kodeJenis = collect(SuratOptions::JENIS)->filter(fn ($j) => mb_stripos($j['nama'], $filter['q']) !== false)->keys()->all();
                $q->where(function ($w) use ($like, $kodeJenis, $filter) {
                    $w->where('nama_file', 'like', $like)->orWhereIn('jenis', $kodeJenis);
                    // Nomor pengajuan: "SR-2026-0018", "2026-0018", atau "18"
                    if (preg_match('/^(?:SR-?)?(?:\d{4}-?)?(\d{1,9})$/i', $filter['q'], $m)) {
                        $w->orWhere('id_pengajuan_surat', (int) $m[1]);
                    }
                });
            })
            ->orderByDesc('created_at')->orderByDesc('id_dokumen_surat')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        return view('surat.index', [
            'laporan'        => $laporan,
            'lf'             => $lf,
            'laporanOpsi'    => SuratOptions::STATUS_LAPORAN_OPSI,
            'halamanLaporan' => Paginasi::nomor($laporan->currentPage(), $laporan->lastPage()),
            'riwayat'        => $riwayat,
            'filter'         => $filter,
            'statusOpsi'     => SuratOptions::STATUS_OPSI,
            'halaman'        => Paginasi::nomor($riwayat->currentPage(), $riwayat->lastPage()),
            'jumlahWajib'    => SuratOptions::jumlahWajib(),
        ]);
    }

    /** Detail laporan yang sudah disetujui + form unggah Surat CHR untuk laporan itu. */
    public function laporan(Request $request, RuteSubsidi $rute)
    {
        $user = $request->user();
        $this->pastikanLaporan($user, $rute);

        $semua = PengajuanSurat::with('dokumen', 'user')
            ->where('id_rute_subsidi', $rute->id_rute_subsidi)->where('id_moda', $user->id_moda)
            ->orderByDesc('id_pengajuan_surat')->get();

        $draf = $semua->firstWhere('status', SuratOptions::DRAF);
        $terkunci = $semua->first(fn ($p) => in_array($p->status, self::STATUS_TERKUNCI, true));

        $berkasDraf = $draf ? $draf->dokumen->keyBy('jenis') : collect();
        $slot = [];
        foreach (SuratOptions::JENIS as $kode => $j) {
            $slot[] = ['kode' => $kode, 'nama' => $j['nama'], 'wajib' => $j['wajib'], 'dokumen' => $berkasDraf->get($kode)];
        }

        return view('surat.laporan', Permohonan::detail($rute) + [
            'draf'           => $draf,
            'terkunci'       => $terkunci,
            'perbaikan'      => $draf ? null : $semua->first(fn ($p) => $p->status === SuratOptions::PERLU_PERBAIKAN),
            'slot'           => $slot,
            'jumlahWajib'    => SuratOptions::jumlahWajib(),
            'terlampir'      => $berkasDraf->count(),
            'totalUkuran'    => (int) $berkasDraf->sum('ukuran'),
            'bisaUnggah'     => $user->bisa('chr.unggah'),
            'maksMb'         => intdiv(SuratOptions::MAKS_KB, 1024),
            'riwayatSurat'   => $semua->flatMap(fn ($p) => $p->dokumen->map(fn ($d) => $d->setRelation('pengajuan', $p)))->sortByDesc('id_dokumen_surat')->values(),
        ]);
    }

    /** Simpan draf (aksi=draf) atau kirim ke INTRAM (aksi=kirim) untuk satu laporan. */
    public function simpan(Request $request, RuteSubsidi $rute)
    {
        $user = $request->user();
        $this->pastikanLaporan($user, $rute);

        $data = $request->validate([
            'aksi'      => ['required', Rule::in(['draf', 'kirim'])],
            'catatan'   => ['nullable', 'string', 'max:1000'],
            'berkas'    => ['nullable', 'array'],
            'berkas.*'  => ['file', 'mimes:pdf', 'max:' . SuratOptions::MAKS_KB],
        ], [
            'catatan.max'       => 'Catatan maksimal :max karakter.',
            'berkas.*.mimes'    => 'Berkas harus berformat PDF.',
            'berkas.*.max'      => 'Ukuran berkas maksimal ' . intdiv(SuratOptions::MAKS_KB, 1024) . ' MB.',
            'berkas.*.uploaded' => 'Berkas gagal diunggah. Periksa batas upload_max_filesize pada server.',
            'berkas.*.file'     => 'Berkas tidak valid.',
        ]);

        $berkas = $request->file('berkas', []);
        abort_if(array_diff(array_keys($berkas), array_keys(SuratOptions::JENIS)) !== [], 422);

        $tersimpan = [];   // file baru, dibersihkan bila transaksi gagal
        $usang = [];       // file lama yang digantikan, dihapus setelah commit
        $nomor = '';

        try {
            DB::transaction(function () use ($user, $rute, $data, $berkas, &$tersimpan, &$usang, &$nomor) {
                // Satu laporan hanya boleh punya satu CHR yang sudah dikirim
                if (PengajuanSurat::where('id_rute_subsidi', $rute->id_rute_subsidi)->whereIn('status', self::STATUS_TERKUNCI)->exists()) {
                    throw ValidationException::withMessages(['berkas' => 'Surat CHR untuk laporan ini sudah dikirim.']);
                }

                $draf = PengajuanSurat::where('id_rute_subsidi', $rute->id_rute_subsidi)->where('id_moda', $user->id_moda)
                        ->where('status', SuratOptions::DRAF)->lockForUpdate()->first()
                    ?? PengajuanSurat::create([
                        'id_user'         => $user->id_user,
                        'id_moda'         => $user->id_moda,
                        'id_rute_subsidi' => $rute->id_rute_subsidi,
                        'tujuan'          => PeranUser::INTRAM,
                        'status'          => SuratOptions::DRAF,
                    ]);

                $catatan = trim((string) ($data['catatan'] ?? ''));
                $draf->catatan_pengiriman = $catatan !== '' ? $catatan : null;

                foreach ($berkas as $jenis => $file) {
                    $path = $file->store("surat_chr/{$draf->id_pengajuan_surat}", 'local');
                    $tersimpan[] = $path;

                    $atribut = ['nama_file' => $file->getClientOriginalName(), 'path_file' => $path, 'ukuran' => $file->getSize()];
                    $lama = $draf->dokumen()->where('jenis', $jenis)->first();
                    if ($lama) {
                        $usang[] = $lama->path_file;
                        $lama->update($atribut);
                    } else {
                        DokumenSurat::create($atribut + ['id_pengajuan_surat' => $draf->id_pengajuan_surat, 'jenis' => $jenis]);
                    }
                }

                if ($data['aksi'] === 'kirim') {
                    foreach (SuratOptions::JENIS as $kode => $j) {
                        if ($j['wajib'] && ! $draf->dokumen()->where('jenis', $kode)->exists()) {
                            throw ValidationException::withMessages(['berkas' => $j['nama'] . ' wajib diunggah sebelum dikirim.']);
                        }
                    }
                    $draf->status = SuratOptions::TERKIRIM;
                    $draf->dikirim_at = now();
                }

                $draf->save();
                $nomor = $draf->nomor;
            });
        } catch (ValidationException $e) {
            Storage::disk('local')->delete($tersimpan);

            throw $e;
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($tersimpan);
            report($e);

            return back()->withInput()->with('error', 'Surat gagal disimpan. Silakan coba lagi atau hubungi administrator.');
        }

        Storage::disk('local')->delete($usang);

        return redirect()->route('surat.laporan', $rute->id_rute_subsidi)->with(
            'success',
            $data['aksi'] === 'kirim'
                ? 'Surat ' . $nomor . ' untuk ' . $rute->nomor_laporan . ' berhasil dikirim ke User INTRAM.'
                : 'Draf ' . $nomor . ' berhasil disimpan.'
        );
    }

    /** Hapus berkas pada draf milik moda sendiri. */
    public function hapus(Request $request, DokumenSurat $dokumen)
    {
        $pengajuan = $dokumen->pengajuan;
        abort_unless($this->milikModa($request->user(), $pengajuan), 404);
        abort_unless($pengajuan->status === SuratOptions::DRAF, 403, 'Surat yang sudah dikirim tidak dapat dihapus.');

        $path = $dokumen->path_file;
        $nama = $dokumen->nama_jenis;
        $dokumen->delete();
        Storage::disk('local')->delete($path);

        $tujuan = $pengajuan->id_rute_subsidi
            ? route('surat.laporan', $pengajuan->id_rute_subsidi)
            : route('surat.index');

        return redirect($tujuan)->with('success', 'Berkas ' . $nama . ' dihapus dari draf.');
    }

    /** Pratinjau (default) atau unduh (?unduh=1). Moda pemilik; INTRAM/Super Admin hanya untuk surat yang sudah dikirim. */
    public function dokumen(Request $request, DokumenSurat $dokumen)
    {
        $user = $request->user();
        $pengajuan = $dokumen->pengajuan;

        $pemilik = $user->isModa() && $this->milikModa($user, $pengajuan);
        $penerima = $pengajuan->sudah_dikirim && ($user->isSuperAdmin() || ($user->isIntram() && $user->bisa('chr.tinjau')));
        abort_unless($pemilik || $penerima, 404);
        abort_unless(Storage::disk('local')->exists($dokumen->path_file), 404);

        return $request->boolean('unduh')
            ? Storage::disk('local')->download($dokumen->path_file, $dokumen->nama_file)
            : Storage::disk('local')->response($dokumen->path_file, $dokumen->nama_file);
    }

    // ------------------------------------------------------------------

    /** Laporan harus milik moda akun (404 bila bukan) dan sudah disetujui (403 bila belum). */
    private function pastikanLaporan($user, RuteSubsidi $rute): void
    {
        abort_unless($user->id_moda !== null && (int) $rute->id_moda === (int) $user->id_moda, 404);
        abort_unless($rute->grup_status === 'disetujui', 403, 'Surat CHR hanya dapat dibuat untuk laporan yang sudah disetujui.');
    }

    private function milikModa($user, PengajuanSurat $pengajuan): bool
    {
        return $user->id_moda !== null && (int) $pengajuan->id_moda === (int) $user->id_moda;
    }
}
