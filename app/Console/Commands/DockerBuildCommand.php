<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DockerBuildCommand extends Command
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'docker:build';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Build Docker image for this project';

  /**
   * Execute the console command.
   *
   * @return int
   *
   * @throws \Exception
   */
  public function handle()
  {
    shell_exec("docker build --tag ghcr.io/arbolado-urbano/arbolado-api:latest .");
  }
}
