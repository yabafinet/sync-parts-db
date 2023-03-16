<?php

namespace App\Services;

use App\Services\Helpers\DateTimeHelper;
use Illuminate\Support\Facades\DB;

class SyncProcessRunHandle
{
    /**
     * @var int
     */
    private int $last_process_id = 0;

    /**
     * @var string
     */
    private $key = '';

    /**
     * @var mixed
     */
    public mixed $last_time_status;

    /**
     * @var mixed
     */
    public mixed $last_status;

    /**
     * @var string
     */
    private $file = '';

    /**
     * @var SyncPartsDbService
     */
    private SyncPartsDbService $syncPertsService;


    /**
     * Last send status from system.
     *
     * @var mixed
     */
    public mixed $last_system_status;

    public function __construct($key, $file, SyncPartsDbService $syncPartsDbService)
    {
        $this->key = $key ?? $syncPartsDbService->getKey($file);
        $this->last_time_status = now()->format('Y-m-d H:i:s');
        $this->last_status = SyncStatus::FINISH;
        $this->last_system_status = SyncStatus::START;
        $this->file = $file;
        $this->syncPertsService = $syncPartsDbService;
    }

    /**
     * @param array $data
     * @return void
     */
    public function start(array $data = array())
    {
        if ($this->getLastSystemStatus() == SyncStatus::STOP) {
            $this->syncPertsService->command->error('exit status :' . $this->last_system_status . "");
            exit();
        }
        $last_status = $this->getLastStatus();
        $elapsed_seconds = DateTimeHelper::diffInSeconds($this->last_time_status);
        if ($last_status == SyncStatus::WAIT && ($elapsed_seconds < $this->syncPertsService->interval_laps && $this->syncPertsService->getLaps() ==1 )) {
            $this->syncPertsService->command->error(
                "\n\nexit : " . $this->last_status
                . "\nelapsed_seconds : " . $elapsed_seconds
                . "\nelaps :" . $this->syncPertsService->getLaps()
                . "\nlast_time_status : " . $this->last_time_status
            );
            exit();
        }
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::START, $data, $this->last_process_id);
    }

    /**
     * @param array $data
     * @return void
     */
    public function wait(array $data = array())
    {
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::WAIT, $data, $this->last_process_id);
    }

    /**
     * Send command start from system.
     *
     * @param array $data
     * @return void
     */
    public function sendStart(array $data = array())
    {
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::START, $data, $this->last_process_id, 2);
        $this->syncPertsService->command->comment('Send Command Start...');
    }

    /**
     * Send command Stop from system.
     *
     * @param array $data
     * @return void
     */
    public function stop(array $data = array())
    {
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::STOP, $data, $this->last_process_id, 2);
    }

    /**
     * @param array $data
     * @return void
     */
    public function finish(array $data = array())
    {
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::FINISH, $data, $this->last_process_id);
    }

    /**
     * @param       $status
     * @param array $data
     * @param null  $process_id
     * @return void
     */
    private function insertNewProcess($status, array $data = array(), $process_id = null, $type = 1)
    {
        $this->last_process_id = DB::table('sync_process_run')->insertGetId([
           'key' => $this->key,
           'status' => $status,
           'config_file' => $this->file,
           'process_id' => $process_id,
           'data' => json_encode($data),
           'type' => $type,
           'created_at' => now(),
           'updated_at' => now(),
        ]);
    }

    /**
     * @return string
     */
    public function getLastStatus()
    {
        $last = DB::table('sync_process_run')
            ->where('key', $this->key)
            ->where('type', 1)
            ->orderByDesc('id')->first(['status', 'created_at']);
        if (!isset($last->status)) {
            return 'none';
        }
        $this->last_status = $last->status;
        $this->last_time_status = $last->created_at;

        return $last->status;
    }

    /**
     * @return string
     */
    private function getLastSystemStatus()
    {
        $last = DB::table('sync_process_run')
            ->where('key', $this->key)
            ->where('type', 2)
            ->orderByDesc('id')->first(['status', 'created_at']);
        if (!isset($last->status)) {
            return 'none';
        }

        $this->last_system_status = $last->status;
        //$this->last_time_status = $last->created_at;

        return $last->status;
    }

    /**
     * @return string
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getConfigFile(): string
    {
        return $this->file;
    }
}
