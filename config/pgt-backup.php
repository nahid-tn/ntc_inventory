<?php

return [
    'disk' => 'local',
    
    'destination' => [
        'path' => '',  // ← খালি রাখতে হবে
    ],

    'database' => [
        'connections' => [
            'mysql' => [
                'dump' => [
                    '--single-transaction',
                    '--no-tablespaces',
                ],
            ],
        ],
    ],

    'schedule' => [
        'cron' => env('BACKUP_CRON', '0 */12 * * *'),
        'keep_for_days' => env('BACKUP_KEEP_FOR_DAYS', 30),
    ],
];