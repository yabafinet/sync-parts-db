<?php

use App\Services\Mutators\Number;
use App\Services\Mutators\TimeNow;

return [
    'type' => 'sync-tables-update',
    'key' => 'sync-tables-update-example',
    'description' => 'Table-A update rows test with Table-B.',
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
     * Update if fields in table B are identical.
     */
    'update_if_identical' => [
        'lottery_id' => 'product_id',
        'play_type' => 'type',
    ],

    /**
     * Update if fields in table B are not identical.
     */
    'update_if_not_identical' => [
        'lottery_id' => 'product_id',
        'play_type' => 'type',
    ],

    /**
     * db-A (select) => db-B (UPDATE)
     *  The first field(id) of table A is the synchronization reference point.
     */
    'structure' => [
        'id' => 'id_b',
        'name' => 'name_b',
        'note' => 'note_b',
        'price' => 'price_b',
        'price_total' => Number::make('price_total_b'),
        'type' => 'type_b',
        'status' => 'status_b',
        'created_at' => 'created_at',
        'updated_at' => TimeNow::make('updated_at'),
    ]
];
