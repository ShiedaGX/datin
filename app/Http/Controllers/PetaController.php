<?php

namespace App\Http\Controllers;

use App\Models\RuteSubsidi;
use App\Support\StatusPengajuan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PetaController extends Controller
{
    public function index(): View
    {
        $pengguna = auth()->user();

        // Pembatasan wajib dilakukan pada query server, bukan hanya pada filter JS.
        // Super Admin dan INTRAM dapat melihat seluruh pengajuan final.
        $query = RuteSubsidi::query()
            ->where('status_pengajuan', StatusPengajuan::FINAL);

        if ($pengguna->isModa()) {
            // Akun Moda tanpa penugasan id_moda tidak dapat melihat rute apa pun.
            $pengguna->id_moda === null
                ? $query->whereRaw('1 = 0')
                : $query->where('id_moda', $pengguna->id_moda);
        } elseif (! $pengguna->isSuperAdmin() && ! $pengguna->isIntram()) {
            // Pemda: pakai kepemilikan data_pemda.id_user, bukan asal kab/kota saja.
            $query->whereHas('pemda', fn ($pemda) => $pemda->where('id_user', $pengguna->id_user));
        }

        // Nama komoditas adalah satu-satunya penghubung master saat ini.
        // Jika kelak ada id_komoditas pada tabel detail, gunakan FK tersebut.
        $sektorPerKomoditas = DB::table('master_komoditas as k')
            ->join('master_sektor as s', 's.id_sektor', '=', 'k.id_sektor')
            ->get(['k.nama_komoditas', 's.nama_sektor'])
            ->groupBy(fn ($row) => mb_strtolower(trim($row->nama_komoditas)))
            ->map(fn ($rows) => $rows->pluck('nama_sektor')->unique()->values()->all());

        $titik = [];
        $rutePeta = [];

        $query->with(['moda', 'kabKotaAsal.provinsi', 'tujuan.provinsi', 'detailKomoditasTujuan'])
            ->orderBy('id_rute_subsidi')
            ->chunk(150, function ($pengajuan) use (&$titik, &$rutePeta, $sektorPerKomoditas) {
                foreach ($pengajuan as $rute) {
                    $asal = $rute->kabKotaAsal;
                    if (! $this->punyaKoordinat($asal)) {
                        continue;
                    }

                    $titik[$asal->id_kab_kota] = $this->titik($asal);

                    foreach ($rute->tujuan as $tujuan) {
                        if (! $this->punyaKoordinat($tujuan)) {
                            continue;
                        }

                        $titik[$tujuan->id_kab_kota] = $this->titik($tujuan);
                        $komoditas = $rute->detailKomoditasTujuan
                            ->where('id_kab_kota_tujuan', $tujuan->id_kab_kota)
                            ->map(function ($item) use ($sektorPerKomoditas) {
                                $nama = trim((string) $item->nama_komoditas);
                                $sektor = $sektorPerKomoditas->get(mb_strtolower($nama), []);

                                return [
                                    'nama' => $nama,
                                    'jumlah' => $item->jumlah === null ? null : (float) $item->jumlah,
                                    'satuan' => $item->satuan_data ?: null,
                                    // Ganda/ambigu tidak dipaksakan menjadi satu sektor.
                                    'sektor' => count($sektor) === 1 ? $sektor[0] : null,
                                ];
                            })->values()->all();

                        $jarak = $this->jarakKm($asal, $tujuan);
                        $rutePeta[] = [
                            'id' => (int) $rute->id_rute_subsidi,
                            'laporan' => $rute->nomor_laporan,
                            'moda' => $rute->moda?->nama_moda ?: (match ((int) $rute->id_moda) {
                                1 => 'Darat', 2 => 'Laut', 3 => 'Udara', 4 => 'Kereta Api',
                                default => 'Moda #' . $rute->id_moda,
                            }),
                            'id_moda' => (int) $rute->id_moda,
                            'asal' => (int) $asal->id_kab_kota,
                            'tujuan' => (int) $tujuan->id_kab_kota,
                            'klasifikasi' => $rute->klasifikasi_label,
                            'jarak' => round($jarak, 1),
                            'kelasJarak' => $jarak < 100 ? '<100 km' : ($jarak < 300 ? '100–300 km' : ($jarak < 700 ? '300–700 km' : ($jarak <= 1500 ? '700–1500 km' : '>1500 km'))),
                            'komoditas' => $komoditas,
                            'sektor' => array_values(array_unique(array_filter(array_column($komoditas, 'sektor')))),
                        ];
                    }
                }
            });

        return view('peta.index', [
            'titikPeta' => array_values($titik),
            'rutePeta' => $rutePeta,
            'modaTerkunci' => $pengguna->isModa(),
        ]);
    }

    private function punyaKoordinat($kab): bool
    {
        return $kab !== null
            && is_numeric($kab->latitude) && is_numeric($kab->longitude)
            && abs((float) $kab->latitude) <= 90
            && abs((float) $kab->longitude) <= 180;
    }

    private function titik($kab): array
    {
        $nama = trim((string) $kab->nama_kab_kota);
        $jenis = trim((string) $kab->jenis);
        // Nama kabupaten/kota pada beberapa baris sudah mengandung awalan
        // "Kabupaten" atau "Kota". Jangan sampai tampil ganda.
        if ($jenis !== '' && ! preg_match('/^(kabupaten|kota)\b/i', $nama)) {
            $nama = $jenis . ' ' . $nama;
        }

        return [
            'id' => (int) $kab->id_kab_kota,
            'nama' => $nama,
            'provinsi' => $kab->provinsi?->nama_provinsi ?? '',
            'lat' => (float) $kab->latitude,
            'lon' => (float) $kab->longitude,
        ];
    }

    /** Jarak garis lurus, bukan jarak jalan atau lintasan pelayaran/penerbangan. */
    private function jarakKm($a, $b): float
    {
        $lat1 = deg2rad((float) $a->latitude);
        $lat2 = deg2rad((float) $b->latitude);
        $dlat = $lat2 - $lat1;
        $dlon = deg2rad((float) $b->longitude - (float) $a->longitude);
        $h = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;

        return 6371.0088 * 2 * asin(min(1, sqrt($h)));
    }
}
