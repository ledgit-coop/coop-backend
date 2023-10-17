<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $schedule->command('account:interest')->lastDayOfMonth('23:59');
        $schedule->command('amortization:overdue')->everyTwoMinutes();
        $schedule->command('penalty:check')->dailyAt('01:00');
        $schedule->command('loan:closing')->dailyAt('01:00');
        $schedule->command('account-transaction:posting')->dailyAt('23:59');
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
