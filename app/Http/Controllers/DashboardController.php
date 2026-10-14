<?php
namespace App\Http\Controllers;

use App\Models\DataPemda;
use App\Models\KabKota;
use App\Models\Provinsi;
use App\Models\RuteSubsidi;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'jumlah' => [
                'Provinsi'       => Provinsi::count(),
                'Kabupaten/Kota' => KabKota::count(),
                'Data Pemda'     => DataPemda::count(),
                'Pengajuan'      => RuteSubsidi::count(),
            ],
        ]);
    }
}
