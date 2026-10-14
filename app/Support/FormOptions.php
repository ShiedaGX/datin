<?php
namespace App\Support;

/**
 * Pilihan statis untuk form Input Data Wilayah.
 * Nilai yang disimpan ('nilai') sengaja disamakan dengan istilah pada file Excel sumber.
 */
class FormOptions
{
    public const KLASIFIKASI = ['Prioritas', 'Strategis', 'Prioritas, Strategis'];

    public const KETERANGAN = [
        '3tp'            => ['label' => '3TP (Tertinggal, Terdepan, Terluar, Perbatasan)', 'nilai' => '3TP'],
        'kspean'         => ['label' => 'KSPEAN (Kawasan Strategis)',                      'nilai' => 'KSPEAN'],
        'kek_ki'         => ['label' => 'KEK/KI (Kawasan Ekonomi Khusus / Industri)',      'nilai' => 'KEK/KI'],
        'pariwisata'     => ['label' => 'Kawasan Pariwisata Prioritas',                    'nilai' => 'Pariwisata'],
        'komoditas_biru' => ['label' => 'Komoditas Unggulan / Ekonomi Biru',               'nilai' => 'Komoditas Unggulan / Kawasan Ekonomi Biru'],
        'transmigrasi'   => ['label' => 'Kawasan Transmigrasi / Perdesaan',                'nilai' => 'Transmigrasi / Perdesaan Prioritas'],
    ];

    /** field upload => jenis_dokumen yang disimpan */
    public const DOKUMEN = [
        'surat_permohonan'     => 'Surat Permohonan',
        'surat_pernyataan'     => 'Surat Pernyataan',
        'surat_rencana_teknis' => 'Surat Rencana Teknis',
    ];

    public const STATUS_AWAL = 'Diajukan';
}
