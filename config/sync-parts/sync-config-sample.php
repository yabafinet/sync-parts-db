<?php

use App\Services\Mutators\Number;

return [
    'type' => 'sync-tables',
    'name' => 'Sync sales samples.',
    'connections' =>[
        'db-A' => 'mysqlA:table_A',
        'db-B' => 'mysqlB:table_B',
    ],
    'structure' => [
        /* db-A => db-B */
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
