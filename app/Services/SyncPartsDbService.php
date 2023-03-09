<?php

namespace App\Services;

use App\Services\Mutators\MutatorsBase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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
     * @var
     */
    private $structures;

    /**
     * Run de synchronize data base parts.
     *
     * @return void
     */
    public function run($path = null)
    {
        $this->loadConfigSyncFiles($path);
        $this->prepareStructures();
        $rows = $this->syncData();

        //dd($rows);
    }

    public function syncData()
    {
        foreach ($this->configurations as $file_config => $config) {
            $rows = $this->getRowsFrom($file_config, 'A');
            $inserts = $this->insertRowsIn($file_config, 'B', $rows);
            //print_r($rows);
        }

        return $rows;
        //dd($rows);
    }

    /**
     * Prepare get rows for sync table B.
     *
     * @param        $file_config
     * @param string $from
     * @return Collection
     */
    private function getRowsFrom($file_config, string $from = 'A')
    {
        $config_a = explode(':', $this->configurations[$file_config]['connections']['db-' . $from]);
        $query = DB::connection($config_a[0])->table($config_a[1]);

        //dd($this->structures);
        foreach ($this->configurations[$file_config]['structure'] as $fieldA => $fielB) {
            if ($fielB instanceof MutatorsBase) {
                $fielB = $fielB->name;
            }
            $query = $query->addSelect([$fieldA . ' AS ' . $fielB]);
            //dd([$fieldA . ' AS ' . $fielB]);
        }
        return $query->get();
    }

    /**
     * Prepare get rows for sync table B.
     *
     * @param        $file_config
     * @param string $from
     * @param        $rows
     * @return Collection
     */
    private function insertRowsIn($file_config, string $from, $rows)
    {
        $config_a = explode(':', $this->configurations[$file_config]['connections']['db-' . $from]);
        $rows_insert = array();
        foreach ($rows as $value) {
            $rows_insert[] = (array) $value;
        }
        dd($rows_insert);
        //$rows = (array) $rows;

        DB::connection($config_a[0])->table($config_a[1])->insert($rows_insert);
    }

    /**
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
}
