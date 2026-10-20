<?php
namespace App\Http\Controllers;

use App\Models\DokumenSurat;
use App\Models\PengajuanSurat;
use App\Support\Paginasi;
use App\Support\PeranUser;
use App\Support\SuratOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Surat CHR: Moda mengunggah surat lalu mengirimkannya ke user INTRAM.
 * Setiap akun hanya melihat pengajuannya sendiri.
 */
class SuratController extends Controller
{
    private const PER_HALAMAN = 10;

    public function index(Request $request)
    {
        $user = $request->user();

        $draf = PengajuanSurat::with('dokumen')
            ->where('id_user', $user->id_user)->where('status', SuratOptions::DRAF)
            ->latest('id_pengajuan_surat')->first();

        $berkasDraf = $draf ? $draf->dokumen->keyBy('jenis') : collect();
        $slot = [];
        foreach (SuratOptions::JENIS as $kode => $j) {
            $slot[] = ['kode' => $kode, 'nama' => $j['nama'], 'wajib' => $j['wajib'], 'dokumen' => $berkasDraf->get($kode)];
        }

        $status = (string) $request->query('status', '');
        $filter = [
            'q'      => trim((string) $request->query('q', '')),
            'status' => array_key_exists($status, SuratOptions::STATUS_OPSI) ? $status : null,
        ];

        $riwayat = DokumenSurat::query()
            ->with('pengajuan')
            ->whereHas('pengajuan', function ($p) use ($user, $filter) {
                $p->where('id_user', $user->id_user);
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

        $terlampir = $berkasDraf->count();

        return view('surat.index', [
            'draf'          => $draf,
            'slot'          => $slot,
            'jumlahWajib'   => SuratOptions::jumlahWajib(),
            'jumlahOpsional' => SuratOptions::jumlahOpsional(),
            'terlampir'     => $terlampir,
            'totalUkuran'   => (int) $berkasDraf->sum('ukuran'),
            'bisaUnggah'    => $user->bisa('chr.unggah'),
            'maksMb'        => intdiv(SuratOptions::MAKS_KB, 1024),
            'riwayat'       => $riwayat,
            'filter'        => $filter,
            'statusOpsi'    => SuratOptions::STATUS_OPSI,
            'halaman'       => Paginasi::nomor($riwayat->currentPage(), $riwayat->lastPage()),
        ]);
    }

    /** Simpan draf (aksi=draf) atau kirim ke INTRAM (aksi=kirim). */
    public function simpan(Request $request)
    {
        $user = $request->user();

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
            DB::transaction(function () use ($user, $data, $berkas, &$tersimpan, &$usang, &$nomor) {
                $draf = PengajuanSurat::where('id_user', $user->id_user)->where('status', SuratOptions::DRAF)->lockForUpdate()->first()
                    ?? PengajuanSurat::create([
                        'id_user' => $user->id_user,
                        'id_moda' => $user->id_moda,
                        'tujuan'  => PeranUser::INTRAM,
                        'status'  => SuratOptions::DRAF,
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

        return redirect()->route('surat.index')->with(
            'success',
            $data['aksi'] === 'kirim'
                ? 'Surat ' . $nomor . ' berhasil dikirim ke User INTRAM.'
                : 'Draf ' . $nomor . ' berhasil disimpan.'
        );
    }

    /** Hapus berkas pada draf milik sendiri. */
    public function hapus(Request $request, DokumenSurat $dokumen)
    {
        $pengajuan = $dokumen->pengajuan;
        abort_unless((int) $pengajuan->id_user === (int) $request->user()->id_user, 404);
        abort_unless($pengajuan->status === SuratOptions::DRAF, 403, 'Surat yang sudah dikirim tidak dapat dihapus.');

        $path = $dokumen->path_file;
        $nama = $dokumen->nama_jenis;
        $dokumen->delete();
        Storage::disk('local')->delete($path);

        return redirect()->route('surat.index')->with('success', 'Berkas ' . $nama . ' dihapus dari draf.');
    }

    /** Pratinjau (default) atau unduh (?unduh=1). Pemilik, atau INTRAM/Super Admin untuk surat yang sudah dikirim. */
    public function dokumen(Request $request, DokumenSurat $dokumen)
    {
        $user = $request->user();
        $pengajuan = $dokumen->pengajuan;

        $pemilik = (int) $pengajuan->id_user === (int) $user->id_user;
        $penerima = $pengajuan->sudah_dikirim && ($user->isSuperAdmin() || ($user->isIntram() && $user->bisa('chr.tinjau')));
        abort_unless($pemilik || $penerima, 404);
        abort_unless(Storage::disk('local')->exists($dokumen->path_file), 404);

        return $request->boolean('unduh')
            ? Storage::disk('local')->download($dokumen->path_file, $dokumen->nama_file)
            : Storage::disk('local')->response($dokumen->path_file, $dokumen->nama_file);
    }
}
