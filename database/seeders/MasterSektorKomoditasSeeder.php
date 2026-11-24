<?php

namespace Database\Seeders;

use App\Models\MasterKomoditas;
use App\Models\MasterSektor;
use Illuminate\Database\Seeder;

class MasterSektorKomoditasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Perikanan & Kelautan' => 'udang, kepiting, rumput laut, tct, tuna, cakalang, teri, roa, kembung, akuatik, patin, ekonomi biru, bandeng, ikan, ikan mas, kakap, kerapu, lele, nila, tongkol, perikanan budidaya, perikanan jenis pembesaran, perikanan tangkap, udang budidaya, udang perairan darat, udang tangkap',
            'Pertanian & Pangan' => 'padi, jagung, ubi, sagu, singkong, kedelai, cabe, agribisnis, beras, ubi jalar, ubi kayu',
            'Hortikultura' => 'alpukat, anggur, bawang daun, bawang merah, bayam, belimbing, buah naga, buncis, cabai besar, cabai keriting, cabai rawit, duku, durian, jambu air, jambu biji, jambu getas merah, jamur tiram, jengkol, jeruk besar, jeruk lemon, jeruk nipis, jeruk siam, kacang panjang, kangkung, labu siam, langsat, lengkeng, mangga, mangga ciemas, manggis, melon, melinjo, mentimun, nanas, nangka, pepaya, petai, pisang, rambutan, salak, sawi, sawo, semangka, sirsak, stroberi, sukun, terong, tomat',
            'Perkebunan' => 'kopi, kakao, kelapa, karet, sawit, pala, kemiri, vanili, mete, aren, cengkeh, gambir, kapuk, kayu manis, kenari, lada, nilam, pinang, tebu, teh, tembakau',
            'Peternakan' => 'sapi, babi, kambing, unggas, ayam buras, daging babi, daging domba, daging kambing, daging kerbau, daging kuda, daging sapi, domba, itik, kelinci, kerbau, kuda, sapi perah, sapi potong',
            'Pariwisata' => 'pariwisata, wisata, wisatawan',
            'Industri, Tambang & Energi' => 'industri, pertambangan, penggalian, gas alam, energi, pupuk, hilirisasi, pltb, manufaktur',
            'Perdagangan & Jasa' => 'perdagangan, jasa',
            'Garam' => 'garam',
            'Konstruksi' => 'konstruksi',
            'Perbatasan & Pertahanan' => 'perbatasan, plbn, pksn, pertahanan',
            'Tanaman Hias & Obat' => 'anggrek, bunga potong, tanaman obat, biofarmaka, herbal, jamu, jahe, kapulaga, kencur, kunyit, lengkuas, lempuyang, lidah buaya, mahkota dewa, mangkudu, sambiloto, sereh, temu ireng, temu kunci, temulawak',
            'Kehutanan' => 'kehutanan, bambu tali',
        ];

        $urutan = 0;
        foreach ($data as $nama => $daftar) {
            $sektor = MasterSektor::updateOrCreate(['nama_sektor' => $nama], ['urutan' => ++$urutan]);

            foreach (array_unique(array_map('trim', explode(',', $daftar))) as $k) {
                MasterKomoditas::firstOrCreate(['id_sektor' => $sektor->id_sektor, 'nama_komoditas' => $k]);
            }
        }
    }
}