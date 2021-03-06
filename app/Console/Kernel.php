<?php

namespace App\Console;

use App\Console\Commands\LoadCSVCommand;
use App\Console\Commands\LoadMongoDBCommand;
use App\Console\Commands\LoadNeo4jCommand;
use App\Console\Commands\LoadSqlAdbCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        LoadSqlAdbCommand::class,
        LoadNeo4jCommand::class,
        LoadMongoDBCommand::class,
        LoadCSVCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(LoadSqlAdbCommand::class)->dailyAt("00:00");

        $schedule->command(LoadMongoDBCommand::class)->dailyAt("00:30");

        $schedule->command(LoadNeo4jCommand::class)->dailyAt("01:00");
    }
}
