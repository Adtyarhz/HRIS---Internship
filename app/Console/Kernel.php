<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\CloseExpiredAssessments::class,
        \App\Console\Commands\ImportEmployeesCommand::class,
        \App\Console\Commands\ExportEmployeeTemplateCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Jalan tiap hari jam 00:00
        $schedule->command('kpi:close-expired')->dailyAt('00:00');
        // Jalan tiap pagi jam 08:00
        $schedule->command('kpi:send-reminder')->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
