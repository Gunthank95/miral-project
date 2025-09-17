<?php
// config/roles.php

return [
    /*
    |--------------------------------------------------------------------------
    | Grup Peran Utama (Untuk Logika Backend & Hak Akses)
    |--------------------------------------------------------------------------
    | Ini digunakan untuk memeriksa grup peran secara cepat, misal: notifikasi.
    */
    'contractor' => [
        10, // Drafter
        20, // Admin
    ],

    'mk' => [
        30, // Supervisor
        40, // Engineer
        50, // Site Manager
        60, // Project Manager
    ],

    'owner' => [
        70, // Tamu (View Only) / Perwakilan Owner
        80, // Direktur
    ],
    
    'super_admin' => 999,

    /*
    |--------------------------------------------------------------------------
    | Definisi Detail Level Jabatan (Untuk Tampilan UI & Form)
    |--------------------------------------------------------------------------
    | Ini digunakan untuk menampilkan pilihan jabatan di form registrasi.
    */
    'definitions' => [
        // --- Peran Operasional Lapangan ---
        'drafter'         => ['level' => 10, 'display_name' => 'Drafter'],
        'admin'           => ['level' => 20, 'display_name' => 'Admin'],
        'supervisor'      => ['level' => 30, 'display_name' => 'Supervisor'],
        'engineer'        => ['level' => 40, 'display_name' => 'Engineer'],
        'site_manager'    => ['level' => 50, 'display_name' => 'Site Manager'],
        'project_manager' => ['level' => 60, 'display_name' => 'Project Manager'],
        
        // --- Peran Non-Operasional / Viewer ---
        'perwakilan_owner' => ['level' => 70, 'display_name' => 'Perwakilan Owner'],
        'direktur'         => ['level' => 80, 'display_name' => 'Direktur'],
    ],
];