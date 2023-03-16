<?php

namespace App\Commands;

use App\Services\SyncPartsDbService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;

class SyncRunCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'sync {action} {path} {--limit=?}{--via=?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start all syncs configs.';

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

        if ($action == 'status') {
            $this->info('status run...');

        } elseif ($action == 'start') {
            $this->runAllSync($path);

        } elseif ($action == 'stop') {
            // stop process sync with key
        }
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
