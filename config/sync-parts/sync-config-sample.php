<?php

use App\Services\Mutators\Number;

return [
    'type' => 'sync-tables',
    'name' => 'Sync sales samples.',
    'connections' =>[
        'db-A' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'sync-parts-db-A',
            'table' => 'table_A',
            'username' => 'root',
            'password' => 'lared2001',
        ],
        'db-B' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'sync-parts-db-B',
            'table' => 'table_B',
            'username' => 'root',
            'password' => 'lared2001',
        ],
    ],

    /**
     * db-A (select) => db-B (insert)
     */
    'structure' => [
        'id' => 'id',
        'name' => 'name_b',
        'note' => 'note_b',
        'price' => 'price_b',
        'price_total' => Number::turn('price_total_b'),
        'type' => 'type_b',
        'status' => 'status_b',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ]
];
