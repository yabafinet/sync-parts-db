<?php

namespace App\Commands;

use App\Services\SyncPartsDbService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SyncNowCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'sync:now {--path=}{--laps=0}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run synchronize now';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sync = new SyncPartsDbService();
        $sync->setCommand($this);
        $path = $this->option('path');
        $limit_laps = (int) $this->option('laps');
        if ($limit_laps >0) {
            $sync->setLimitLaps($limit_laps);
        }

        $result = $sync->run($path);
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
