<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\ProcessUtils;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;

class DockerPullCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'docker:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull latest Docker images';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle()
    {
        shell_exec("docker compose -p arbolado pull");
    }
}
