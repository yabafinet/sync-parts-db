<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SyncHandler
{
    /**
     * @var int|mixed
     */
    private $company;

    /**
     * @var string
     */
    private $type;

    public function __construct($company, $type)
    {
        $this->company = $company;
        $this->type = $type;
    }
    /**
     * Obtener el último punto de sincronización.
     *
     * @return integer
     */
    public function getLastSyncId($table_a, $table_b)
    {
        $sync = DB::table('sync_point')
            ->where('table_a', $table_a)
            ->where('table_b', $table_b)
            ->orderBy('id', 'DESC')
            ->first(['last_sync', 'last_sync_date']);

        return $sync->last_sync ?? null;
    }

    /**
     * Obtener la última fecha de ejecución.
     *
     * @return integer
     */
    public function getLastSyncDatetime()
    {
        $sync = DB::table('sync_point')
            ->where('company', $this->company)
            ->where('type', $this->type)
            ->orderBy('id', 'DESC')
            ->first();

        return $sync->created_at ?? null;
    }

    /**
     * Insertar el último punto de sincronización.
     *
     * @param $id
     * @return void
     */
    public function setLastSyncId($table_a, $table_b, $id)
    {
        DB::table('sync_point')
            ->insert([
               'table_a' => $table_a,
               'table_b' => $table_b,
               'last_sync' => $id ?? '00',
               'last_sync_date' => now(),
               'created_at' => now(),
            ]);
    }
}
