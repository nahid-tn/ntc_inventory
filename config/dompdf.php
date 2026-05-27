<?php

return [
    'driver' => 'dompdf',
    'temp_dir' => public_path('app/dompdf'),
    'font_dir' => public_path('fonts/'),
    'font_cache' => public_path('fonts/'),
    'default_font' => 'SolaimanLipi',
    'isPhpEnabled' => true,
    'fonts' => [
        'SolaimanLipi' => ['R' => public_path('fonts/SolaimanLipi.ttf')],
        'SiyamRupali'  => ['R' => public_path('fonts/SiyamRupaliRegular.ttf')],
        'NikoshBAN'    => ['R' => public_path('fonts/NikoshBAN.ttf')],
    ],
    'dpi' => 96,
    'is_remote_enabled' => true,
    'isHtml5ParserEnabled' => true,
    'isFontSubsettingEnabled' => false,
    'default_paper_size' => 'a4',
    'default_paper_orientation' => 'portrait',
];
