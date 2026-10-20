<?php
namespace App\Http\Controllers;

use App\Models\DataPemda;
use App\Models\KabKota;
use App\Models\Provinsi;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const URUTAN_WILAYAH = ['Papua', 'Sulawesi', 'Maluku', 'Nusa Tenggara', 'Kalimantan', 'Sumatera', 'Jawa-Bali'];

    private const WARNA_WILAYAH = [
        'Papua' => '#1e3a8a', 'Sulawesi' => '#0d9488', 'Maluku' => '#d97706', 'Nusa Tenggara' => '#0f766e',
        'Kalimantan' => '#64748b', 'Sumatera' => '#94a3b8', 'Jawa-Bali' => '#475569',
    ];

    private const WARNA_KLASIFIKASI = [
        'Prioritas Strategis' => '#e11d48', 'Prioritas' => '#d97706', 'Strategis' => '#0d9488', 'Belum diklasifikasi' => '#94a3b8',
    ];

    public function index()
    {
        $totalPemda = DataPemda::count();

        // ---- Kartu ringkasan ----
        $kpi = [
            'provinsi'  => Provinsi::count(),
            'wilayah'   => Wilayah::count(),
            'kab_kota'  => KabKota::count(),
            'sektor'    => count(config('sektor')),
        ];

        // ---- Distribusi per wilayah (jumlah kab/kota) ----
        $wilayah = Wilayah::withCount('kabKota')->get()
            ->sortBy(function ($w) {
                $i = array_search($w->nama_wilayah, self::URUTAN_WILAYAH, true);
                return $i === false ? 99 : $i;
            })->values();
        $maks = max(1, (int) $wilayah->max('kab_kota_count'));
        $distribusi = $wilayah->map(fn ($w) => [
            'nama'   => $w->nama_wilayah,
            'jumlah' => $w->kab_kota_count,
            'lebar'  => min(100, $w->kab_kota_count / ($maks * 1.25) * 100),   // sisakan ruang di ujung bar
            'warna'  => self::WARNA_WILAYAH[$w->nama_wilayah] ?? '#64748b',
        ]);

        // ---- Klasifikasi wilayah (donut) ----
        $hitung = array_fill_keys(array_keys(self::WARNA_KLASIFIKASI), 0);
        DataPemda::select('klasifikasi_pemda', DB::raw('COUNT(*) AS n'))
            ->groupBy('klasifikasi_pemda')->get()
            ->each(function ($r) use (&$hitung) {
                $hitung[DataPemda::normalisasiKlasifikasi($r->klasifikasi_pemda)] += (int) $r->n;
            });

        $donat = [];
        $awal = 0.0;
        foreach ($hitung as $label => $n) {
            if ($n === 0 || $totalPemda === 0) {
                continue;
            }
            $arc = $n / $totalPemda * 100;
            $donat[] = [
                'label'  => $label,
                'jumlah' => $n,
                'persen' => (int) round($arc),
                'warna'  => self::WARNA_KLASIFIKASI[$label],
                'arc'    => $arc,
                'awal'   => $awal,
            ];
            $awal += $arc;
        }

        // ---- 5 entri terbaru ----
        $terbaru = DataPemda::with('kabKota.provinsi', 'kabKota.wilayah')
            ->terbaru()->limit(5)->get();

        return view('dashboard', compact('kpi', 'distribusi', 'donat', 'totalPemda', 'terbaru'));
    }
}
