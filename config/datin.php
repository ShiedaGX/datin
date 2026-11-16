<?php

return [
    /*
     | Batas waktu peninjauan laporan oleh Moda (hari sejak diajukan). Laporan yang masih
     | "Menunggu Persetujuan" lebih lama dari ini dihitung "melewati target waktu peninjauan".
     | Atur lewat .env: DATIN_TARGET_TINJAU_HARI=3
     */
    'target_tinjau_hari' => (int) env('DATIN_TARGET_TINJAU_HARI', 3),
];
