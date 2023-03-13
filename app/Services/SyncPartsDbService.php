<?php

namespace App\Services;

use App\Services\Mutators\MutatorsBase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SyncPartsDbService
{
    /**
     * Configurations files for synchronize db.
     *
     * @var array
     */
    private array $configurations = array();

    /**
     * Table structure A-B
     *
     * @var array
     */
    private array $structures;

    /**
     * Run de synchronize data base parts.
     *
     * @return array
     */
    public function run($path = null)
    {
        $this->loadConfigSyncFiles($path);
        $this->prepareStructures();
        return $this->syncData();
    }

    /**
     * @return array
     */
    public function syncData()
    {
        foreach ($this->configurations as $file_config => $config) {
            $syncHandler = new SyncHandler($config['connections']['db-A']['table'], $config['connections']['db-B']['table']);
            $last_sync_id = $syncHandler->getLastSyncId();
            $rows = $this->getRowsFrom($file_config, 'A', $last_sync_id);
            $last_insert_id = $this->insertRowsIn($file_config, 'B', $rows);
            if ($last_insert_id) {
                $syncHandler->setLastSyncId($last_insert_id);
            }
        }

        return [
            'init_sync_id'=> $last_sync_id,
            'end_sync_id'=> $last_insert_id,
            'rows'=> $rows,
        ];
    }

    /**
     * Prepare get rows for sync table B.
     *
     * @param        $file_config
     * @param string $from
     * @return Collection
     */
    private function getRowsFrom($file_config, string $from = 'A', $last_id = null)
    {
        $config_key = $this->getConfigKey($from);
        $config = $this->getConnectionsConfig($file_config, $from);
        Config::set('database.connections.' . $config_key, $config);
        $query = DB::connection($config_key)->table($config['table']);

        foreach ($this->configurations[$file_config]['structure'] as $fieldA => $fielB) {
            if ($fielB instanceof MutatorsBase) {
                $fielB = $fielB->name;
            }
            $query = $query->addSelect([$fieldA . ' AS ' . $fielB]);
            //dd([$fieldA . ' AS ' . $fielB]);
        }
        return $query->where('id', '>', $last_id)->get();
    }

    /**
     * Prepare get rows for sync table B.
     *
     * @param        $file_config
     * @param string $from
     * @param        $rows
     * @return bool
     */
    private function insertRowsIn($file_config, string $from, $rows)
    {
        $config_key = $this->getConfigKey($from);
        $config_a = $this->getConnectionsConfig($file_config, $from);
        $rows_insert = array();
        $last_id = 0;
        foreach ($rows as $value) {
            $rows_insert[] = (array) $value;
            $last_id = $value->id;
        }
        //dd($rows_insert);
        //$rows = (array) $rows;
        Config::set('database.connections.' . $config_key, $config_a);
        DB::connection($config_key)->table($config_a['table'])->insert($rows_insert);

        return $last_id;
    }

    /**
     * Prepare structure array table A (select) and table B (insert).
     *
     * @return void
     */
    private function prepareStructures()
    {
        $structure = array();

        foreach ($this->configurations as $file_config => $config) {
            foreach ($config['structure'] as $field_a => $field_b) {
                $structure[$file_config]['A'][$field_a] = $field_a;
                $fieldB = $field_b;
                if ($field_b instanceof Mutators\MutatorsBase) {
                    $fieldB = $field_b->name;
                }
                $structure[$file_config]['B'][$fieldB] = $field_b;
            }
        }

        $this->structures = $structure;
    }

    /**
     * Load configs files configurations data base sync.
     *
     * @param $path
     * @return void
     */
    public function loadConfigSyncFiles($path = null)
    {
        $path = $path ?? 'config/sync-parts';

        foreach (glob($path . "/*.php") as $filename) {
            $config = include $filename;
            $config_name = str_replace($path. '/', '', $filename);
            $this->configurations[$config_name] = $config;
        }
    }

    /**
     * @param $file_config
     * @param string $from
     * @return mixed
     */
    private function getConnectionsConfig($file_config, string $from): mixed
    {
        return $this->configurations[$file_config]['connections']['db-' . $from];
    }

    /**
     * @param string $from
     * @return string
     */
    private function getConfigKey(string $from): string
    {
        return 'sync-parts-' . $from;
    }
}
