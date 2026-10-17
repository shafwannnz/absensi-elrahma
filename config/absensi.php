<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Sistem Absensi
|--------------------------------------------------------------------------
| 'defaults' hanya dipakai sekali untuk mengisi tabel campus_settings.
| Setelah itu admin mengubahnya lewat menu "Pengaturan Lokasi".
*/
return [

    'defaults' => [
        'name'          => 'Kampus STIKOM El Rahma',
        'latitude'      => -6.590043695245298,
        'longitude'     => 106.78561680228029,
        'radius_m'      => 500,      // jarak maksimal dari kampus (meter)
        'start_time'    => '08:00',  // absensi dibuka
        'on_time_until' => '09:00',  // lewat jam ini = terlambat
        'end_time'      => '12:00',  // absen masuk ditutup
        'checkout_start' => '15:00', // absen pulang dibuka
        'checkout_end'   => '16:00', // absen pulang ditutup
    ],

    'face' => [
        // Jarak Euclidean maksimal antar descriptor wajah agar dianggap orang yang sama.
        'threshold'  => (float) env('FACE_MATCH_THRESHOLD', 0.5),

        // Library & model face-api (dijalankan di browser). Bisa diganti ke file lokal,
        // mis. '/vendor/face-api/face-api.js' dan '/vendor/face-api/models/'.
        'script_url' => env('FACE_API_SCRIPT_URL', 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js'),
        'models_url' => env('FACE_API_MODELS_URL', 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
    ],

    // Titik GPS dengan akurasi lebih buruk dari ini ditolak (anti lokasi "kira-kira").
    'max_gps_accuracy_m' => (int) env('MAX_GPS_ACCURACY_M', 200),
];
