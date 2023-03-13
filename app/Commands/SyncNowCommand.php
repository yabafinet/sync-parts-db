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
        $result = $sync->run();

        $this->info('Sync Init Id   : ' . $result['init_sync_id']);
        $this->info('Sync End Id    : ' . $result['end_sync_id']);
        $this->info('Sync complete!');
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
