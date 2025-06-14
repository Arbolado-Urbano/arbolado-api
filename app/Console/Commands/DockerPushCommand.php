<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DockerPushCommand extends Command
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'docker:push';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Build & push Docker image for this project';

  /**
   * Execute the console command.
   *
   * @return int
   *
   * @throws \Exception
   */
  public function handle()
  {
    $this->call("docker:build");
    shell_exec("docker push ghcr.io/arbolado-urbano/arbolado-api:latest");
  }
}
