<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DockerDownCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'docker:down';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop Docker containers';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle()
    {
        shell_exec("docker compose -p arbolado down");
    }
}
