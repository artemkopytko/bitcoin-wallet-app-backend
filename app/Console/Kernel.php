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
        // Prune telescope logs older than 48 hours
        $schedule->command('telescope:prune --hours=48')->daily();

        $schedule->command('app:update-fiat-currencies')
            ->everyFifteenMinutes()
            ->runInBackground()
            ->withoutOverlapping();

        $schedule->command('app:update-crypto-currencies')
            ->everyMinute()
            ->runInBackground()
            ->withoutOverlapping();
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
