<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Provinsi;
use App\Models\KabKota;

class WilayahSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Mapping kode provinsi -> wilayah besar
        |--------------------------------------------------------------------------
        */

        $mappingWilayah = [

            // Sumatera (id_wilayah = 1)
            '11' => 1, // Aceh
            '12' => 1, // Sumatera Utara
            '13' => 1, // Sumatera Barat
            '14' => 1, // Riau
            '15' => 1, // Jambi
            '16' => 1, // Sumatera Selatan
            '17' => 1, // Bengkulu
            '18' => 1, // Lampung
            '19' => 1, // Kep. Bangka Belitung
            '21' => 1, // Kep. Riau


            // Kalimantan (id_wilayah = 2)
            '61' => 2, // Kalimantan Barat
            '62' => 2, // Kalimantan Tengah
            '63' => 2, // Kalimantan Selatan
            '64' => 2, // Kalimantan Timur
            '65' => 2, // Kalimantan Utara


            // Jawa-Bali (id_wilayah = 3)
            '31' => 3, // DKI Jakarta
            '32' => 3, // Jawa Barat
            '33' => 3, // Jawa Tengah
            '34' => 3, // DI Yogyakarta
            '35' => 3, // Jawa Timur
            '36' => 3, // Banten
            '51' => 3, // Bali


            // Sulawesi (id_wilayah = 4)
            '71' => 4, // Sulawesi Utara
            '72' => 4, // Sulawesi Tengah
            '73' => 4, // Sulawesi Selatan
            '74' => 4, // Sulawesi Tenggara
            '75' => 4, // Gorontalo
            '76' => 4, // Sulawesi Barat


            // Maluku (id_wilayah = 6)
            '81' => 6, // Maluku
            '82' => 6, // Maluku Utara


            // Nusa Tenggara (id_wilayah = 7)
            '52' => 7, // Nusa Tenggara Barat
            '53' => 7, // Nusa Tenggara Timur


            // Papua (id_wilayah = 5)
            '91' => 5, // Papua
            '92' => 5, // Papua Barat
            '93' => 5, // Papua Selatan
            '94' => 5, // Papua Tengah
            '95' => 5, // Papua Pegunungan
            '96' => 5, // Papua Barat Daya
        ];


        /*
        |--------------------------------------------------------------------------
        | Ambil data Provinsi dari API
        |--------------------------------------------------------------------------
        */

        $response = Http::get(
            'https://wilayah.id/api/provinces.json'
        );


        $provinces = $response->json()['data'];


        foreach ($provinces as $province) {


            // Ambil wilayah berdasarkan kode provinsi
            $idWilayah = $mappingWilayah[$province['code']] ?? null;


            // Jika tidak ditemukan, skip
            if (!$idWilayah) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Insert Provinsi
            |--------------------------------------------------------------------------
            */

            $provinsi = Provinsi::create([
                'id_wilayah' => $idWilayah,
                'nama_provinsi' => $province['name'],
                'kode_provinsi' => $province['code'],
            ]);


            /*
            |--------------------------------------------------------------------------
            | Ambil Kabupaten/Kota
            |--------------------------------------------------------------------------
            */

            $responseKab = Http::get(
                'https://wilayah.id/api/regencies/'
                . $province['code']
                . '.json'
            );


            $kabKotas = $responseKab->json()['data'];


            foreach ($kabKotas as $kab) {


                KabKota::create([
                    'id_wilayah' => $idWilayah,
                    'id_provinsi' => $provinsi->id_provinsi,
                    'nama_kab_kota' => $kab['name'],
                    'kode_kab_kota' => $kab['code'],
                    'jenis' => str_contains($kab['name'], 'KOTA')
                        ? 'Kota'
                        : 'Kabupaten',
                ]);

            }

        }
    }
}