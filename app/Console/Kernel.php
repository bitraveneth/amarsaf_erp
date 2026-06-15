<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('erp:notifications:sync')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        if (filter_var(config('database.backup.schedule_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
            $backup = $schedule->command('erp:backup-database')
                ->withoutOverlapping(60)
                ->runInBackground()
                ->timezone(config('app.timezone'));

            $time = config('database.backup.schedule_time', '02:00');

            if (config('database.backup.schedule', 'daily') === 'weekly') {
                $backup->weeklyOn((int) config('database.backup.schedule_day', 0), $time);
            } else {
                $backup->dailyAt($time);
            }
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
