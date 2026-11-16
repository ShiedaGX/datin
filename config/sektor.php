<?php

/**
 * Kamus sektor potensi. Dipakai untuk:
 *  - saran (hint) saat Pemda mengisi "Potensi Unggulan Daerah" pada halaman Input Data, dan
 *  - mendeteksi sektor suatu daerah dari teks potensinya: sektor dihitung 1 bila minimal satu
 *    kata kunci ditemukan (tanpa membedakan huruf besar/kecil).
 *
 * Format: 'Nama Sektor' => ['kata kunci', ...]. Menambah sektor cukup menambah satu baris.
 * (Daftar nama saja juga didukung, tetapi deteksi otomatis hanya memakai kata dari nama sektornya.)
 */
return [
    'Perikanan & Kelautan'       => ['perikanan', 'kelautan', 'udang', 'kepiting', 'rumput laut', 'tct', 'tuna', 'cakalang', 'teri', 'roa', 'kembung', 'akuatik', 'patin', 'ekonomi biru'],
    'Pertanian & Pangan'         => ['pertanian', 'padi', 'jagung', 'ubi', 'sagu', 'singkong', 'kedelai', 'cabe', 'pangan', 'hortikultura', 'holtikultura', 'pisang', 'nanas', 'agribisnis'],
    'Perkebunan'                 => ['perkebunan', 'kopi', 'kakao', 'kelapa', 'karet', 'sawit', 'pala', 'kemiri', 'vanili', 'mete'],
    'Peternakan'                 => ['peternakan', 'sapi', 'babi', 'kambing', 'unggas'],
    'Pariwisata'                 => ['pariwisata', 'wisata'],
    'Industri, Tambang & Energi' => ['industri', 'pertambangan', 'penggalian', 'gas alam', 'energi', 'pupuk', 'hilirisasi', 'pltb', 'manufaktur'],
    'Perdagangan & Jasa'         => ['perdagangan', 'perdangan', 'jasa'],
    'Garam'                      => ['garam'],
    'Konstruksi'                 => ['konstruksi'],
    'Perbatasan & Pertahanan'    => ['perbatasan', 'plbn', 'pksn', 'pertahanan'],
    'Tanaman Hias & Obat'        => ['tanaman hias', 'anggrek', 'bunga potong', 'tanaman obat', 'biofarmaka', 'herbal', 'jamu'],
];
