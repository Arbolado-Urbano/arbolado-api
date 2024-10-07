<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\ProcessUtils;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;

class DockerUpCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'docker:up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Docker containers';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle()
    {
        shell_exec("docker compose -p arbolado -f ./docker-compose.yml up -d");
    }
}
