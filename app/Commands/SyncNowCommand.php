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
    protected $signature = 'sync:now {--path?}';

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
        $sync->run();
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
