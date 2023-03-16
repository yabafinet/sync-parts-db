<?php

use App\Services\Mutators\Number;

return [
    'type' => 'sync-tables',
    'key' => 'sync-tables-samples2',
    'description' => 'Table-A synchronization test with Table-B.',
    'connections' =>[
        'db-A' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'sync-parts-db-A',
            'table' => 'table_A',
            'username' => 'user_test',
            'password' => '123456',
        ],
        'db-B' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'sync-parts-db-B',
            'table' => 'table_B',
            'username' => 'user_test',
            'password' => '123456',
        ],
    ],

    /**
     * db-A (select) => db-B (insert)
     *  The first field(id) of table A is the synchronization reference point.
     */
    'structure' => [
        'id' => 'id_b',
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
