<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('akeneo:prepare:ee')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->then(function() {
                $this->call('akeneo:sync:ee');
            });

        $schedule->command('filerobot:sync')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->then(function() {
                     $this->call('akeneo:prepare');
                     $this->call('akeneo:sync');
                 });

        $schedule->command('akeneo:filerobot:entity-prepare')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->then(function() {
                $this->call('akeneo:sync:ee:entity');
            });

        $schedule->command('telescope:prune')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
