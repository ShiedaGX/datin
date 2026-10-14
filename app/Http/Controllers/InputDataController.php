<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreWilayahRequest;
use App\Models\DataPemda;
use App\Models\DokumenPengajuan;
use App\Models\KabKota;
use App\Models\Moda;
use App\Models\Provinsi;
use App\Models\RuteSubsidi;
use App\Models\Wilayah;
use App\Support\FormOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InputDataController extends Controller
{
    public function create()
    {
        return view('input-data.create', [
            'provinsi'    => Provinsi::orderBy('nama_provinsi')->get(),
            'wilayah'     => Wilayah::orderBy('nama_wilayah')->get(),
            'moda'        => Moda::orderBy('id_moda')->get(),
            'klasifikasi' => FormOptions::KLASIFIKASI,
            'keterangan'  => FormOptions::KETERANGAN,
            'dokumen'     => FormOptions::DOKUMEN,
        ]);
    }

    public function store(StoreWilayahRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        // Format sama dengan data Excel: "1. A; 2. B; 3. C"
        $potensi = collect($data['potensi'])
            ->map(fn ($v) => trim(str_replace(';', ',', (string) $v)))
            ->filter()
            ->values()
            ->map(fn ($v, $i) => ($i + 1) . '. ' . $v)
            ->implode('; ');

        // Format sama dengan data Excel: "3TP, KSPEAN, ..."
        $keterangan = collect($data['keterangan'] ?? [])
            ->map(fn ($key) => FormOptions::KETERANGAN[$key]['nilai'])
            ->implode(', ');

        $tersimpan = []; // path file, untuk dibersihkan jika transaksi gagal

        try {
            DB::transaction(function () use ($request, $data, $user, $potensi, $keterangan, &$tersimpan) {
                $kab = KabKota::create([
                    'id_wilayah'    => $data['id_wilayah'],
                    'id_provinsi'   => $data['id_provinsi'],
                    'nama_kab_kota' => trim($data['nama_kab_kota']),
                ]);

                $pemda = DataPemda::create([
                    'id_user'                => $user->id_user,
                    'id_kab_kota'            => $kab->id_kab_kota,
                    'potensi_pemda'          => $potensi,
                    'klasifikasi_pemda'      => $data['klasifikasi_pemda'],
                    'keterangan_pemda'       => $keterangan !== '' ? $keterangan : null,
                    'kebutuhan_konektivitas' => $data['kebutuhan_konektivitas'] ?? null,
                ]);

                // Satu pengajuan = satu rute_subsidi. Tujuan belum diketahui pada tahap ini.
                $rute = RuteSubsidi::create([
                    'id_pemda'           => $pemda->id_pemda,
                    'id_moda'            => $data['id_moda'],
                    'id_kab_kota_asal'   => $kab->id_kab_kota,
                    'id_kab_kota_tujuan' => null,
                    'status_pengajuan'   => FormOptions::STATUS_AWAL,
                    'tanggal_pengajuan'  => now()->toDateString(),
                ]);

                foreach (FormOptions::DOKUMEN as $field => $jenis) {
                    $file = $request->file($field);
                    $path = $file->store("dokumen_pengajuan/{$rute->id_rute_subsidi}", 'local');
                    $tersimpan[] = $path;

                    DokumenPengajuan::create([
                        'id_rute_subsidi' => $rute->id_rute_subsidi,
                        'id_user'         => $user->id_user,
                        'jenis_dokumen'   => $jenis,
                        'nama_file'       => $file->getClientOriginalName(),
                        'path_file'       => $path,
                        'versi_dokumen'   => 1,
                        'tanggal_upload'  => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($tersimpan);
            report($e);

            return back()->withInput()->with('error', 'Data gagal disimpan. Silakan coba lagi atau hubungi administrator.');
        }

        return redirect()->route('data-wilayah.index')
            ->with('success', 'Data wilayah "' . trim($data['nama_kab_kota']) . '" berhasil ditambahkan.');
    }
}
