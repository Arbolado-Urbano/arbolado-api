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
    protected $name = 'pmtiles:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PMTiles file from the database';

    /**
     * The console command options.
     *
     * @var string
     */
    protected $signature = 'pmtiles:generate {--force : Fully regenerate the CSV from all DB records instead of applying incremental changes}';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle()
    {
        $force = $this->option('force');
        $sync  = config('queue.default') === 'sync';

        if ($sync) {
            $this->comment(
                $force
                    ? 'Iniciando regeneración completa del archivo PMTiles (esto puede demorar unos minutos)...'
                    : 'Iniciando actualización del archivo PMTiles (esto puede demorar unos minutos)...'
            );
        }

        GenerarPMTiles::dispatch($force);

        if ($sync) {
            $this->info(
                $force
                    ? 'Regeneración del archivo PMTiles finalizada.'
                    : 'Actualización del archivo PMTiles finalizada.'
            );
        } else {
            $this->info(
                $force
                    ? 'Regeneración del archivo PMTiles iniciada.'
                    : 'Actualización del archivo PMTiles iniciada.'
            );
        }
    }
}