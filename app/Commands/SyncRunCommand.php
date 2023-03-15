<?php

namespace App\Commands;

use App\Services\SyncPartsDbService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SyncRunCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'sync:{action} {--limit=}';

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
        $path = $this->option('limit');
        $action = $this->argument('action');
        if ($action == 'status') {
            $this->info('status run...');
        }
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
