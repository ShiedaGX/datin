<?php

/**
 * Kamus sektor potensi. Sektor suatu daerah ditentukan dari teks "potensi_pemda":
 * sektor dihitung 1 bila minimal satu kata kunci ditemukan (tanpa membedakan huruf besar/kecil).
 * Daftar ini disalin dari rumus kolom sektor pada file Excel sumber (format_data_lengkap_web.xlsx).
 * Tambah/ubah kata kunci di sini bila ada sektor atau istilah baru.
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
    'Tanaman Hias & Obat'        => ['tanaman hias', 'tanaman obat'],
];
