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
    private mixed $last_time_status;

    /**
     * @var mixed
     */
    private mixed $last_status;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @param       $file
     * @param array $data
     * @return void
     */
    public function start($file, $data = array())
    {
        if ($this->getLastStatus() == SyncStatus::STOP) {
            exit(1);
        }
        $elapsed_seconds = DateTimeHelper::diffInSeconds($this->last_time_status);
        if ($this->last_status == SyncStatus::WAIT && $elapsed_seconds < 10) {
            exit( $this->last_status . " (elapsed_seconds): " . $elapsed_seconds );
        }
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::START, $file, $data, $this->last_process_id);
    }

    /**
     * @param       $file
     * @param array $data
     * @return void
     */
    public function wait($file, $data = array())
    {
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::WAIT, $file, $data, $this->last_process_id);
    }

    /**
     * @param       $file
     * @param array $data
     * @return void
     */
    public function stop($file, $data = array())
    {
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::STOP, $file, $data, $this->last_process_id);
    }

    /**
     * @param       $file
     * @param array $data
     * @return void
     */
    public function finish($file, $data = array())
    {
        $data = $data ?? array();
        $this->insertNewProcess(SyncStatus::FINISH, $file, $data, $this->last_process_id);
    }

    /**
     * @param $status
     * @param $file
     * @param array $data
     * @param $process_id
     * @return void
     */
    private function insertNewProcess($status, $file, array $data = array(), $process_id = null)
    {
        $this->last_process_id = DB::table('sync_process_run')->insertGetId([
           'key' => $this->key,
           'status' => $status,
           'config_file' => $file,
           'process_id' => $process_id,
           'data' => json_encode($data),
           'created_at' => now(),
           'updated_at' => now(),
        ]);
    }

    /**
     * @return string
     */
    private function getLastStatus()
    {
        $last = DB::table('sync_process_run')
            ->where('key', $this->key)->latest()->first(['status', 'created_at']);
        if (!isset($last->status)) {
            return 'none';
        }
        $this->last_status = $last->status;
        $this->last_time_status = $last->created_at;

        return $last->status;
    }
}
