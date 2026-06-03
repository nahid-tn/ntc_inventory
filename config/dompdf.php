<?php

return [
    // ...
    'font_dir' => public_path('fonts/'),
    'font_cache' => storage_path('fonts/'),
    'default_font' => 'SolaimanLipi', // ডিফল্ট ফন্ট চেঞ্জ
    
    'options' => [
    'fontDir' => storage_path('fonts/'),
    'fontCache' => storage_path('fonts/'),
    'defaultFont' => 'SolaimanLipi',
    'isFontSubsettingEnabled' => false, // এটা false করো
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => true,
    
    'dpi' => 96,
],
    
    // কাস্টম ফন্ট ডিফাইন করো
    'font_family' => [
        'SolaimanLipi' => [
            'R' => 'SolaimanLipi.ttf', // Regular
            'B' => 'SolaimanLipi.ttf', // Bold
            'I' => 'SolaimanLipi.ttf', // Italic
            'BI' => 'SolaimanLipi.ttf',
        ]
    ],
];
