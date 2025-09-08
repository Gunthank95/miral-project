<?php
// config/roles.php

return [
    'levels' => [
        // --- Peran Operasional Lapangan ---
        'drafter' => ['level' => 10, 'display_name' => 'Drafter'],
        'admin' => ['level' => 20, 'display_name' => 'Admin'],
        'supervisor' => ['level' => 30, 'display_name' => 'Supervisor'],
        'engineer' => ['level' => 40, 'display_name' => 'Engineer'],
        'site_manager' => ['level' => 50, 'display_name' => 'Site Manager'],
        'project_manager' => ['level' => 60, 'display_name' => 'Project Manager'],
        
        // --- Peran Non-Operasional / Viewer ---
        'guest' => [
            'level' => 70, 
            'display_name' => 'Tamu (View Only)'
        ],
        'director' => [
            'level' => 80, 
            'display_name' => 'Direktur'
        ],
    ],
];