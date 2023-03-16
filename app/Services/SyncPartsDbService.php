<?php

namespace App\Services;

use App\Services\Mutators\MutatorsBase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class SyncPartsDbService
{
    /**
     * Limit of turns that the synchronization would give in one minute.
     *  If the value is 0 it runs forever.
     *
     * @var int
     */
    private int $limit_laps = 0;

    /**
     * Interval of seconds between each lap that the synchronization would give in one minute.
     *
     * @var int
     */
    public int $interval_laps = 15;

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
     * @var Command
     */
    public Command $command;

    /**
     * @var int
     */
    private int $laps = 1;

    /**
     * Run de synchronize data base parts.
     *
     * @return SyncPartsDbService
     */
    public function run($path = null)
    {
        $this->loadConfigSyncFiles($path);
        $this->prepareStructures();
        $this->syncData();

        return $this;
    }

    /**
     * @return array
     */
    public function syncData($laps = 1)
    {
        $result = array();
        $this->setLaps($laps);

        foreach ($this->configurations as $file_config => $config) {
            $key = $this->getKey($file_config);
            $processRunHandle = new SyncProcessRunHandle($key, $file_config, $this);
            $processRunHandle->start();

            $syncHandler = new SyncHandler($key, $config['connections']['db-A']['table'], $config['connections']['db-B']['table']);
            $last_sync_id = $syncHandler->getLastSyncId();
            $rows = $this->getRowsFrom($file_config, 'A', $last_sync_id);
            $last_insert_id = $this->insertRowsIn($file_config, 'B', $rows);
            if ($last_insert_id) {
                $syncHandler->setLastSyncId($last_insert_id);
            }

            $result = [
                'init_sync_id'=> $last_sync_id,
                'end_sync_id'=> $last_insert_id,
                //'rows'=> $rows,
            ];

            $processRunHandle->finish($result);

            $this->command->info('Sync complete:');
            $this->command->info('  Last Status         : ' . $processRunHandle->last_status);
            $this->command->info('  Last Status (system): ' . $processRunHandle->last_system_status);
            $this->command->info('  Sync Init Id        : ' . $result['init_sync_id']);
            $this->command->info('  Sync End Id         : ' . $result['end_sync_id']);
            $this->command->info('  Laps Number         : ' . $laps);
            $processRunHandle->wait();
            sleep($this->interval_laps);

            if ($laps <= $this->limit_laps || $this->limit_laps ==0) {
                return $this->syncData($laps +1);
            }
        }

        return $result;
    }

    /**
     * Prepare get rows for sync table B.
     *
     * @param        $file_config
     * @param string $from
     * @param null   $last_id
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
        if (!$last_id) {
            return $query->latest('id')->limit(100)->get();
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
        $filed_sync_point = $this->getFieldSyncPoint($file_config);
        foreach ($rows as $value) {
            //dd($value);
            $rows_insert[] = (array) $value;
            $last_id = $value->{$filed_sync_point};
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

        //dd($structure);

        $this->structures = $structure;
    }

    /**
     * Load configs files configurations data base sync.
     *
     * @param $path
     * @return SyncPartsDbService
     */
    public function loadConfigSyncFiles($path = null)
    {
        $path = $path ?? '/config/sync-parts';
        //$path = '/' . $path;

        $this->command->info('Load configs in: ' . $path);

        $this->extractedConfigFile($path, $path);

        return $this;
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
     * @param $file_config
     * @return mixed
     */
    public function getKey($file_config): mixed
    {
        return $this->configurations[$file_config]['key'];
    }

    /**
     * The first field of the A table is the synchronization reference point.
     *
     * @param $file_config
     * @return mixed
     */
    private function getFieldSyncPoint($file_config): string
    {
        return array_values($this->configurations[$file_config]['structure'])[0];
    }

    /**
     * @param string $from
     * @return string
     */
    private function getConfigKey(string $from): string
    {
        return 'sync-parts-' . $from;
    }

    /**
     * @param Command $command
     * @return SyncPartsDbService
     */
    public function setCommand(Command $command): SyncPartsDbService
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @param mixed $filename
     * @param mixed $path
     * @return void
     */
    private function extractedConfigFile(mixed $filename, mixed $path): void
    {
        $config = include $filename;
        $config_name = str_replace($path . '/', '', $filename);
        $this->configurations[$config_name] = $config;
    }

    /**
     * @param int $laps
     */
    public function setLaps(int $laps): void
    {
        $this->laps = $laps;
    }

    /**
     * @return int
     */
    public function getLaps(): int
    {
        return $this->laps;
    }
}
