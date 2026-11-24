<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterKomoditas;
use App\Models\MasterSatuanData;
use App\Models\MasterSektor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Master sektor & komoditas (tabel master_sektor dan master_komoditas), khusus Super Admin.
 * Perubahan langsung dipakai form Input Data dan deteksi sektor (cache dibersihkan oleh model).
 */
class SektorController extends Controller
{
    private const PER_HALAMAN = 12;

    public function index(Request $request)
    {
        $semuaSektor = MasterSektor::withCount('komoditas')->orderBy('urutan')->orderBy('id_sektor')->get();

        // sektor terpilih (?sektor=ID); tidak ada / tidak dikenal = tampilkan komoditas semua sektor
        $terpilih = $semuaSektor->firstWhere('id_sektor', (int) $request->query('sektor'));
        $q = trim((string) $request->query('q', ''));

        $komoditas = MasterKomoditas::with('sektor:id_sektor,nama_sektor')
            ->when($terpilih, fn ($x) => $x->where('id_sektor', $terpilih->id_sektor))
            ->when($q !== '', fn ($x) => $x->where('nama_komoditas', 'like', '%' . addcslashes($q, '\\%_') . '%'))
            ->orderBy('nama_komoditas')->orderBy('id_komoditas')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        // Hitung pemakaian dari tabel rincian tujuan yang benar-benar dipakai.
        // Nama komoditas yang sama pada beberapa sektor dihitung secara konservatif
        // pada seluruh master yang cocok, agar penghapusan tidak merusak referensi.
        [$pakaiSektor, $pakaiKomoditas] = $this->hitungPemakaianKomoditas();

        // Pencarian komoditas lintas sektor (termasuk yang belum pernah diajukan).
        $tab = $request->query('tab') === 'analisis' ? 'analisis' : 'master';
        $semuaKomoditasCari = MasterKomoditas::with('sektor:id_sektor,nama_sektor')
            ->orderBy('nama_komoditas')->orderBy('id_komoditas')->get();
        $hasilCariKomoditas = true;
        if ($tab === 'analisis' && $request->filled('cari_komoditas')) {
            $cari = mb_strtolower(trim((string) $request->query('cari_komoditas')));
            $cocok = $semuaKomoditasCari->first(fn ($k) => mb_strtolower(trim($k->nama_komoditas)) === $cari)
                ?? $semuaKomoditasCari->first(fn ($k) => mb_stripos($k->nama_komoditas, $cari) !== false);
            $hasilCariKomoditas = (bool) $cocok;
            if ($cocok) {
                $request->query->set('analisis_sektor', $cocok->id_sektor);
                $request->query->set('analisis_komoditas', $cocok->id_komoditas);
                $request->query->remove('analisis_satuan');
            }
        }
        $analisis = $this->analisisPotensi($request, $semuaSektor);

        return view('admin.sektor.index', [
            'tab' => $tab,
            'analisis' => $analisis,
            'semuaKomoditasCari' => $semuaKomoditasCari,
            'hasilCariKomoditas' => $hasilCariKomoditas,
            'semuaSektor'    => $semuaSektor,
            'terpilih'       => $terpilih,
            'q'              => $q,
            'komoditas'      => $komoditas,
            'totalKomoditas' => (int) $semuaSektor->sum('komoditas_count'),
            'pakaiSektor'    => $pakaiSektor,
            'pakaiKomoditas' => $pakaiKomoditas,
            'urutanBerikut'  => ((int) $semuaSektor->max('urutan')) + 1,
            'pilihanSatuanDefault' => MasterSatuanData::aktif()->orderBy('nama_satuan')->pluck('nama_satuan'),
        ]);
    }

    /**
     * Analisis jumlah komoditas tercatat pada rincian TUJUAN pengajuan final.
     * Sektor ditentukan dari master komoditas hanya bila nama komoditas unik
     * lintas sektor; nama ambigu tidak diklaim sebagai sektor tertentu.
     */
    private function analisisPotensi(Request $request, $semuaSektor): array
    {
        $sektorId = (int) $request->query('analisis_sektor', 0);
        $sektor = $semuaSektor->firstWhere('id_sektor', $sektorId);
        if (! $sektor) {
            $sektor = $semuaSektor->first();
        }

        $daftarKomoditas = $sektor
            ? MasterKomoditas::where('id_sektor', $sektor->id_sektor)
                ->orderBy('nama_komoditas')->get(['id_komoditas', 'nama_komoditas'])
            : collect();
        $komoditasId = (int) $request->query('analisis_komoditas', 0);
        $komoditas = $daftarKomoditas->firstWhere('id_komoditas', $komoditasId)
            ?? $daftarKomoditas->first();

        $semuaNama = MasterKomoditas::query()
            ->get(['id_sektor', 'nama_komoditas'])
            ->groupBy(fn ($k) => mb_strtolower(trim($k->nama_komoditas)));
        $ambigu = $komoditas && ($semuaNama->get(mb_strtolower(trim($komoditas->nama_komoditas)), collect())
            ->pluck('id_sektor')->unique()->count() > 1);

        $baris = collect();
        if ($komoditas && ! $ambigu) {
            $namaDicari = mb_strtolower(trim($komoditas->nama_komoditas));

            // Format terbaru: rincian per tujuan dan komoditas.
            $baru = DB::table('rute_subsidi_tujuan_komoditas as d')
                ->join('rute_subsidi as r', 'r.id_rute_subsidi', '=', 'd.id_rute_subsidi')
                ->join('kab_kota as asal', 'asal.id_kab_kota', '=', 'r.id_kab_kota_asal')
                ->where('r.status_pengajuan', 'Verifikasi Final')
                ->whereRaw('LOWER(TRIM(d.nama_komoditas)) = ?', [$namaDicari])
                ->select('asal.id_kab_kota', 'asal.nama_kab_kota', 'd.jumlah', 'd.satuan_data')
                ->get();

            // Format lama: kolom komoditas + berat pada rute_subsidi_tujuan.
            // Hanya fallback jika tujuan tersebut belum punya rincian versi baru.
            // Ini mencegah penghitungan ganda ketika data lama sudah dimigrasi.
            $lama = DB::table('rute_subsidi_tujuan as t')
                ->join('rute_subsidi as r', 'r.id_rute_subsidi', '=', 't.id_rute_subsidi')
                ->join('kab_kota as asal', 'asal.id_kab_kota', '=', 'r.id_kab_kota_asal')
                ->where('r.status_pengajuan', 'Verifikasi Final')
                ->whereRaw('LOWER(TRIM(t.komoditas)) = ?', [$namaDicari])
                ->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('rute_subsidi_tujuan_komoditas as d2')
                        ->whereColumn('d2.id_rute_subsidi', 't.id_rute_subsidi')
                        ->whereColumn('d2.id_kab_kota_tujuan', 't.id_kab_kota_tujuan');
                })
                ->select('asal.id_kab_kota', 'asal.nama_kab_kota',
                    't.berat as jumlah', DB::raw('NULL as satuan_data'))
                ->get();

            $baris = $baru->concat($lama);

        }

        // Semua satuan dibaca sekaligus, tanpa filter satuan.
        // Satuan kosong tetap terpisah agar angka yang berbeda unit tidak dijumlahkan.
        $terukur = $baris->filter(fn ($r) => $r->jumlah !== null && is_numeric($r->jumlah))
            ->map(function ($r) {
                $r->satuan_normal = trim((string) $r->satuan_data) ?: 'Satuan belum diketahui';
                return $r;
            });

        $kelompokSatuan = $terukur->groupBy('satuan_normal')->map(function ($rows, $satuan) {
            $pemda = $rows->groupBy('id_kab_kota')->map(function ($perPemda) use ($satuan) {
                return [
                    'nama' => $perPemda->first()->nama_kab_kota,
                    'total' => $perPemda->sum(fn ($r) => (float) $r->jumlah),
                    'entri' => $perPemda->count(),
                    'satuan' => $satuan,
                ];
            })->sortByDesc('total')->values();

            return [
                'satuan' => $satuan,
                'total' => $rows->sum(fn ($r) => (float) $r->jumlah),
                'jumlah_entri' => $rows->count(),
                'jumlah_pemda' => $pemda->count(),
                'top_pemda' => $pemda->take(5)->values(),
                'pemda' => $pemda,
            ];
        })->sortKeys()->values();

        $pemdaSemua = $terukur->groupBy('id_kab_kota')->map(function ($rows) {
            return [
                'nama' => $rows->first()->nama_kab_kota,
                'entri' => $rows->count(),
                'rincian' => $rows->groupBy('satuan_normal')->map(function ($unitRows, $satuan) {
                    return [
                        'satuan' => $satuan,
                        'total' => $unitRows->sum(fn ($r) => (float) $r->jumlah),
                    ];
                })->sortKeys()->values(),
            ];
        })->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return [
            'sektor' => $sektor,
            'komoditas' => $komoditas,
            'daftar_komoditas' => $daftarKomoditas,
            'ambigu' => $ambigu,
            'jumlah_pemda' => $pemdaSemua->count(),
            'jumlah_entri' => $terukur->count(),
            'jumlah_satuan' => $kelompokSatuan->count(),
            'kelompok_satuan' => $kelompokSatuan,
            'pemda_semua' => $pemdaSemua,
        ];
    }

    // ---- Sektor ----

    public function simpanSektor(Request $request)
    {
        $v = $this->validasiSektor($request);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        $baru = MasterSektor::create([
            'nama_sektor' => $v['nama_sektor'],
            'urutan'      => $v['urutan'] ?? ((int) MasterSektor::max('urutan')) + 1,
        ]);

        return redirect()->route('admin.sektor.index', ['sektor' => $baru->id_sektor])
            ->with('success', 'Sektor "' . $v['nama_sektor'] . '" ditambahkan.');
    }

    public function ubahSektor(Request $request, MasterSektor $sektor)
    {
        $v = $this->validasiSektor($request, $sektor);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        $sektor->update(['nama_sektor' => $v['nama_sektor'], 'urutan' => $v['urutan'] ?? $sektor->urutan]);

        return back()->with('success', 'Sektor "' . $sektor->nama_sektor . '" disimpan.');
    }

    public function hapusSektor(MasterSektor $sektor)
    {
        [$pakaiSektor, $pakaiKomoditas] = $this->hitungPemakaianKomoditas();
        $dipakai = (int) ($pakaiSektor[$sektor->id_sektor] ?? 0);
        if ($dipakai > 0) {
            return back()->with('error', 'Sektor "' . $sektor->nama_sektor . '" dipakai ' . $dipakai . ' pengajuan sehingga tidak dapat dihapus.');
        }

        $nama = $sektor->nama_sektor;
        $jumlah = $sektor->komoditas()->count();
        $sektor->delete();

        return redirect()->route('admin.sektor.index')
            ->with('success', 'Sektor "' . $nama . '"' . ($jumlah ? " dan {$jumlah} komoditasnya" : '') . ' dihapus.');
    }

    // ---- Komoditas ----

    public function simpanKomoditas(Request $request, MasterSektor $sektor)
    {
        $v = $this->validasiKomoditas($request, $sektor);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        $sektor->komoditas()->create([
            'nama_komoditas' => $v['nama_komoditas'],
            'satuan_default' => $v['satuan_default'] ?? null,
        ]);

        return back()->with('success', 'Komoditas "' . $v['nama_komoditas'] . '" ditambahkan ke ' . $sektor->nama_sektor . '.');
    }

    public function ubahKomoditas(Request $request, MasterKomoditas $komoditas)
    {
        $v = $this->validasiKomoditas($request, $komoditas->sektor, $komoditas);
        if ($v instanceof \Illuminate\Http\RedirectResponse) {
            return $v;
        }

        $komoditas->update([
            'nama_komoditas' => $v['nama_komoditas'],
            'satuan_default' => $v['satuan_default'] ?? null,
        ]);

        return back()->with('success', 'Komoditas "' . $komoditas->nama_komoditas . '" disimpan.');
    }

    public function hapusKomoditas(MasterKomoditas $komoditas)
    {
        [, $pakaiKomoditas] = $this->hitungPemakaianKomoditas();
        $dipakai = (int) ($pakaiKomoditas[$komoditas->id_komoditas] ?? 0);
        if ($dipakai > 0) {
            return back()->with('error', 'Komoditas "' . $komoditas->nama_komoditas . '" dipakai ' . $dipakai . ' pengajuan sehingga tidak dapat dihapus.');
        }

        $nama = $komoditas->nama_komoditas;
        $komoditas->delete();

        return back()->with('success', 'Komoditas "' . $nama . '" dihapus.');
    }

    /**
     * Hitung jumlah pengajuan unik dari rincian baru dan lama.
     * Semua status dihitung untuk perlindungan penghapusan master.
     * Data lama hanya dipakai jika belum ada rincian baru pada tujuan yang sama.
     */
    private function hitungPemakaianKomoditas(): array
    {
        $master = MasterKomoditas::query()->get(['id_komoditas', 'id_sektor', 'nama_komoditas']);
        $berdasarkanNama = $master->groupBy(fn ($m) => mb_strtolower(trim($m->nama_komoditas)));

        $baru = DB::table('rute_subsidi_tujuan_komoditas')
            ->select('id_rute_subsidi', 'nama_komoditas')->get();
        $lama = DB::table('rute_subsidi_tujuan as t')
            ->whereNotExists(function ($sub) {
                $sub->selectRaw('1')->from('rute_subsidi_tujuan_komoditas as d')
                    ->whereColumn('d.id_rute_subsidi', 't.id_rute_subsidi')
                    ->whereColumn('d.id_kab_kota_tujuan', 't.id_kab_kota_tujuan');
            })
            ->select('t.id_rute_subsidi', 't.komoditas as nama_komoditas')->get();

        $idsSektor = [];
        $idsKomoditas = [];
        foreach ($baru->concat($lama) as $rincian) {
            $nama = mb_strtolower(trim((string) $rincian->nama_komoditas));
            foreach ($berdasarkanNama->get($nama, collect()) as $m) {
                $idsSektor[$m->id_sektor][$rincian->id_rute_subsidi] = true;
                $idsKomoditas[$m->id_komoditas][$rincian->id_rute_subsidi] = true;
            }
        }

        return [
            collect($idsSektor)->map(fn ($ids) => count($ids)),
            collect($idsKomoditas)->map(fn ($ids) => count($ids)),
        ];
    }

    // ------------------------------------------------------------------

    /** @return array|\Illuminate\Http\RedirectResponse */
    private function validasiSektor(Request $request, ?MasterSektor $sektor = null)
    {
        $request->merge(['nama_sektor' => trim((string) $request->input('nama_sektor'))]);

        $v = Validator::make($request->all(), [
            'nama_sektor' => ['required', 'string', 'max:100',
                Rule::unique('master_sektor', 'nama_sektor')->ignore($sektor?->id_sektor, 'id_sektor')],
            'urutan'      => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], [
            'nama_sektor.required' => 'Nama sektor wajib diisi.',
            'nama_sektor.max'      => 'Nama sektor maksimal 100 karakter.',
            'nama_sektor.unique'   => 'Sektor dengan nama itu sudah ada.',
            'urutan.integer'       => 'Urutan harus berupa angka.',
            'urutan.min'           => 'Urutan tidak boleh negatif.',
            'urutan.max'           => 'Urutan maksimal 65535.',
        ]);

        return $v->fails()
            ? back()->withInput()->with('error', $v->errors()->first())
            : $v->validated();
    }

    /** Nama komoditas disimpan huruf kecil, seperti data awal (pencocokan teks tidak peka huruf besar/kecil). */
    private function validasiKomoditas(Request $request, MasterSektor $sektor, ?MasterKomoditas $komoditas = null)
    {
        $request->merge(['nama_komoditas' => mb_strtolower(trim((string) $request->input('nama_komoditas')))]);
        $request->merge(['satuan_default' => trim((string) $request->input('satuan_default')) ?: null]);

        $v = Validator::make($request->all(), [
            'nama_komoditas' => ['required', 'string', 'max:100',
                Rule::unique('master_komoditas', 'nama_komoditas')
                    ->where('id_sektor', $sektor->id_sektor)
                    ->ignore($komoditas?->id_komoditas, 'id_komoditas')],
            'satuan_default' => ['nullable', 'string', Rule::exists('master_satuan_data', 'nama_satuan')->where('status_aktif', true)],
        ], [
            'nama_komoditas.required' => 'Nama komoditas wajib diisi.',
            'nama_komoditas.max'      => 'Nama komoditas maksimal 100 karakter.',
            'nama_komoditas.unique'   => 'Komoditas itu sudah ada pada sektor ' . $sektor->nama_sektor . '.',
            'satuan_default.exists' => 'Satuan default harus dipilih dari master satuan yang aktif.',
        ]);

        return $v->fails()
            ? back()->withInput()->with('error', $v->errors()->first())
            : $v->validated();
    }
}
