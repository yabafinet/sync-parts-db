<?php

namespace App\Commands;

use App\Services\Helpers\DateTimeHelper;
use App\Services\SyncPartsDbService;
use App\Services\SyncProcessRunHandle;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;
use Termwind\Termwind;
use function Termwind\{render};

class SyncRunCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'sync {action} {path} {--limit=?}{--via}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start all syncs configs.';

    /**
     * @var SyncProcessRunHandle
     */
    private SyncProcessRunHandle $processRunHandle;

    /**
     * @var string
     */
    private string$via;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $this->via = $this->option('via');
        $path = $this->argument('path');
        $action = $this->argument('action');

        if ($action == 'run') {
            $this->runAllSync($path);

        } elseif (in_array($action, ['stop', 'start'])) {
            $this->processRunHandle = new SyncProcessRunHandle(null, $path, (new SyncPartsDbService())->setCommand($this)->loadConfigSyncFiles($path));
            // stop process sync with key
            if ($action == 'stop') {
                $this->stopSync();
            } elseif ($action == 'start') {
                $this->startSync();

            }
        } elseif ($action == 'status') {
            $this->status($path);
        }
    }

    /**
     * Print information
     *
     * @return void
     */
    public function status($path = 'config/sync-parts/')
    {
        //dd(glob($path . "*.php"));
        $results  = array();
        foreach (glob($path . "*.php") as $filename) {
            $processRunHandle = new SyncProcessRunHandle(null, $filename, (new SyncPartsDbService())->setCommand($this)->loadConfigSyncFiles($filename));

            $processRunHandle->getLastStatus();
            $key = $processRunHandle->getKey();
            $file = $processRunHandle->getConfigFile();
            $status = $processRunHandle->last_status;
            $last_status_date = $processRunHandle->last_time_status;
            $time_elapse = DateTimeHelper::diffInSeconds($processRunHandle->last_time_status);
            if ($time_elapse > 20) {
                $status = 'error / ' . $time_elapse;
            }

            $results[] = [$key, $file, $last_status_date . ' / ' . $time_elapse, $status];
        }
        $this->newLine();
        $this->table(['Key', 'Config', 'Last Date / Segs', 'Status'], $results);
        $this->newLine();
    }

    /**
     * @param $path
     */
    public function runAllSync($path)
    {
        if ($this->via) {
            $this->via = $this->via . ' ';
        }
        $pool = Process::pool(function (Pool $pool) use ($path) {
            foreach (glob($path . "*.php") as $filename) {
                $pool->path(base_path())->command($this->via . 'php application sync:now --path=' . $filename);
            }
        })->start(function (string $type, string $output, int $key) {
            echo "\n " . $key .'/' . $type . ':' . $output;
        });

        while ($pool->running()->isNotEmpty()) {
            // ...
        }

        $results = $pool->wait();
    }

    /**
     * @return void
     */
    public function stopSync()
    {
        $this->processRunHandle->stop(['command_send' => true]);
    }

    /**
     * @return void
     */
    public function startSync()
    {
        $this->processRunHandle->sendStart(['command_send' => true]);
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
