<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\GenerarPMTiles;

class PMTilesCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'pmtiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PMTiles file';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle()
    {
        if (config('queue.default') === 'sync') {
            $this->comment('Iniciando generación de archivo PMTiles (esto puede demorar unos minutos)...');
        }
        GenerarPMTiles::dispatch();
        if (config('queue.default') === 'sync') {
            $this->info('Generación de archivo PMTiles finalizada.');
        } else {
            $this->info('Generación de archivo PMTiles iniciada.');
        }
    }
}
