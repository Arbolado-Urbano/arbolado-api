<?php

namespace App\Console;

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
        Commands\ServeCommand::class,
        Commands\KeyGenerateCommand::class,
        Commands\DockerUpCommand::class,
        Commands\DockerDownCommand::class,
        Commands\DockerPullCommand::class,
        Commands\DockerBuildCommand::class,
        Commands\DockerPushCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //
    }
}
