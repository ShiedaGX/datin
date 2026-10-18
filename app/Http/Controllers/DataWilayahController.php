<?php
namespace App\Http\Controllers;

use App\Models\DataPemda;
use App\Models\DokumenPengajuan;
use App\Models\RuteSubsidi;
use App\Models\Wilayah;
use App\Support\XlsxExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataWilayahController extends Controller
{
    private const PER_HALAMAN = 8;

    /** nilai filter => label */
    private const KLASIFIKASI = [
        'prioritas-strategis' => 'Prioritas Strategis',
        'prioritas'           => 'Prioritas',
        'strategis'           => 'Strategis',
    ];

    public function index(Request $request)
    {
        $filter = $this->filter($request);
        $data = $this->query($filter)->paginate(self::PER_HALAMAN)->withQueryString();

        return view('data-wilayah.index', [
            'data'        => $data,
            'filter'      => $filter,
            'wilayah'     => Wilayah::orderBy('nama_wilayah')->get(),
            'klasifikasi' => self::KLASIFIKASI,
            'halaman'     => $this->nomorHalaman($data->currentPage(), $data->lastPage()),
            'adaFilter'   => $filter['wilayah'] !== null || $filter['klasifikasi'] !== null || $filter['q'] !== '',
        ]);
    }

    public function show(DataPemda $pemda)
    {
        $pemda->load('kabKota.provinsi', 'kabKota.wilayah', 'user', 'ruteSubsidi.moda', 'ruteSubsidi.dokumen', 'ruteSubsidi.verifikasi.verifikator');

        return view('data-wilayah.show', [
            'pemda'      => $pemda,
            'rute'       => $pemda->pengajuan_terbaru,
            'bisaUnduh'  => $pemda->dokumenBisaDiunduhOleh(auth()->user()),
        ]);
    }

    public function export(Request $request)
    {
        if (! class_exists(\ZipArchive::class)) {
            return back()->with('error', 'Ekspor Excel membutuhkan ekstensi PHP "zip". Aktifkan extension=zip di php.ini lalu restart server.');
        }

        $rows = [];
        foreach ($this->query($this->filter($request))->reorder('id_pemda')->get() as $i => $p) {
            $rows[] = [
                $i + 1,
                $p->kabKota->provinsi->nama_provinsi,
                $p->kabKota->wilayah->nama_wilayah,
                $p->kabKota->nama_kab_kota,
                $p->klasifikasi_label,
                $p->keterangan_pemda,
                $p->potensi_ringkas,
                $p->kebutuhan_konektivitas,
                $p->jumlah_tujuan,
                $p->jumlah_sektor,
                implode(', ', $p->sektor_terdeteksi),
                $p->status_label,
            ];
        }

        $header = ['No', 'Provinsi', 'Wilayah', 'Kabupaten/Kota', 'Klasifikasi Wilayah', 'Keterangan Daerah',
                   'Potensi Unggulan Daerah', 'Kebutuhan Konektivitas', 'Jumlah Tujuan', 'Jumlah Sektor', 'Sektor Potensi', 'Status'];

        $path = XlsxExporter::buat($header, $rows, 'Data Wilayah');

        return response()->download(
            $path,
            'data-wilayah-' . now()->format('Ymd-His') . '.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    /** Menghapus PENGAJUAN (rute, dokumen, verifikasi) dan melepas kepemilikan. Data wilayah (kab/kota & data pemda) tetap tersimpan. */
    public function destroy(Request $request, DataPemda $pemda)
    {
        abort_unless($pemda->bisaDiubahOleh($request->user()), 403);

        $nama = $pemda->kabKota->nama_kab_kota;
        $folder = [];

        try {
            DB::transaction(function () use ($pemda, &$folder) {
                foreach ($pemda->ruteSubsidi as $rute) {
                    $folder[] = "dokumen_pengajuan/{$rute->id_rute_subsidi}";
                    $rute->delete();   // dokumen & verifikasi ikut terhapus (cascade)
                }
                $pemda->update(['id_user' => null]);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Pengajuan gagal dihapus. Silakan coba lagi atau hubungi administrator.');
        }

        foreach ($folder as $f) {
            Storage::disk('local')->deleteDirectory($f);
        }

        return redirect()->route('data-wilayah.index')->with('success', 'Pengajuan "' . $nama . '" berhasil dihapus. Data wilayah tetap tersimpan.');
    }

    public function unduh(DokumenPengajuan $dokumen)
    {
        abort_unless($dokumen->bisaDiunduhOleh(auth()->user()), 403);
        abort_unless(Storage::disk('local')->exists($dokumen->path_file), 404);

        return Storage::disk('local')->download($dokumen->path_file, $dokumen->nama_file);
    }

    // ------------------------------------------------------------------

    private function filter(Request $r): array
    {
        $wilayah = (string) $r->query('wilayah', '');
        $klas = (string) $r->query('klasifikasi', '');

        return [
            'q'           => trim((string) $r->query('q', '')),
            'wilayah'     => ctype_digit($wilayah) ? (int) $wilayah : null,
            'klasifikasi' => array_key_exists($klas, self::KLASIFIKASI) ? $klas : null,
        ];
    }

    private function query(array $f)
    {
        return DataPemda::query()
            ->with('kabKota.provinsi', 'kabKota.wilayah', 'ruteSubsidi')
            ->when($f['q'] !== '', function ($q) use ($f) {
                $like = '%' . addcslashes($f['q'], '\\%_') . '%';
                $q->whereHas('kabKota', function ($k) use ($like) {
                    $k->where('nama_kab_kota', 'like', $like)
                      ->orWhereHas('provinsi', fn ($p) => $p->where('nama_provinsi', 'like', $like));
                });
            })
            ->when($f['wilayah'], fn ($q, $w) => $q->whereHas('kabKota', fn ($k) => $k->where('id_wilayah', $w)))
            ->when($f['klasifikasi'], function ($q, $kl) {
                if ($kl === 'prioritas-strategis') {
                    $q->where('klasifikasi_pemda', 'like', '%prioritas%')->where('klasifikasi_pemda', 'like', '%strategis%');
                } elseif ($kl === 'prioritas') {
                    $q->where('klasifikasi_pemda', 'like', '%prioritas%')->where('klasifikasi_pemda', 'not like', '%strategis%');
                } else {
                    $q->where('klasifikasi_pemda', 'like', '%strategis%')->where('klasifikasi_pemda', 'not like', '%prioritas%');
                }
            })
            ->terbaru();
    }

    /** Contoh: [1, 2, 3, '...', 24] */
    private function nomorHalaman(int $aktif, int $akhir): array
    {
        $set = collect([1, $akhir, $aktif - 1, $aktif, $aktif + 1]);
        if ($aktif <= 3) {
            $set = $set->merge([2, 3]);
        }
        if ($aktif >= $akhir - 2) {
            $set = $set->merge([$akhir - 2, $akhir - 1]);
        }
        $nomor = $set->filter(fn ($n) => $n >= 1 && $n <= $akhir)->unique()->sort()->values();

        $hasil = [];
        $prev = 0;
        foreach ($nomor as $n) {
            if ($n - $prev > 1) {
                $hasil[] = '...';
            }
            $hasil[] = $n;
            $prev = $n;
        }

        return $hasil;
    }
}
