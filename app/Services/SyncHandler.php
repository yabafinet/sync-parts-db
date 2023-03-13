<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SyncHandler
{
    /**
     * @var string
     */
    private string $table_a;

    /**
     * @var string
     */
    private string $table_b;

    public function __construct($table_a, $table_b)
    {
        $this->table_a = $table_a;
        $this->table_b = $table_b;
    }

    /**
     * Obtener el último punto de sincronización.
     *
     * @return int|null
     */
    public function getLastSyncId(): ?int
    {
        $sync = DB::table('sync_point')
            ->where('table_a', $this->table_a)
            ->where('table_b', $this->table_b)
            ->orderBy('id', 'DESC')
            ->first(['last_sync', 'last_sync_date']);

        return $sync->last_sync ?? null;
    }

    /**
     * Insert last point Id.
     *
     * @param $id
     * @return void
     */
    public function setLastSyncId($id)
    {
        DB::table('sync_point')
            ->insert([
               'table_a' => $this->table_a,
               'table_b' => $this->table_b,
               'last_sync' => $id ?? '00',
               'last_sync_date' => now(),
               'created_at' => now(),
            ]);
    }
}
