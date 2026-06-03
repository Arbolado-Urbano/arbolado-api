<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

class GenerarPMTiles implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;
    public int $timeout = 900; // 15 minutes
    public int $tries = 1;
    public int $retryAfter = 7201;
    private string $layerName = "trees";
    private string $CSVPath;
    private string $pmtilesPath;
    private string $tippecanoePath;

    public function __construct()
    {
        $this->CSVPath = public_path("arboles.csv");
        $this->pmtilesPath = public_path("arboles.pmtiles");
        $this->tippecanoePath = resource_path("bin/tippecanoe");
    }

    public function handle(): void
    {
        $this->generateCSV();
        $this->generatePMTiles();
    }

    private function generateCSV() {
        $query = DB::table('arboles')
            ->join('especies', 'arboles.especie_id', '=', 'especies.id')
            ->whereNull('arboles.removido')
            ->select('arboles.id', 'arboles.lat', 'arboles.lng', 'especies.id AS especieId')
            ->orderBy('arboles.id');
        $CSVTmpPath = "$this->CSVPath.tmp";
        $fh = fopen($CSVTmpPath, 'w');
        fwrite($fh, "lat,lon,id,species\n");
        foreach ($query->lazy(1000) as $arbol) {
            fwrite($fh, sprintf(
                "%s,%s,%s,%s\n",
                $arbol->lat,
                $arbol->lng,
                $arbol->id,
                $arbol->especieId,
            ));
        }
        fclose($fh);
        rename($CSVTmpPath, $this->CSVPath);
    }

    private function generatePMTiles() {
        $pmtilesTmpPath = "$this->pmtilesPath.tmp.pmtiles";
        $result = Process::run(sprintf(
            '%s --output=%s --layer=%s --maximum-zoom=g --drop-densest-as-needed --extend-zooms-if-still-dropping --force %s',
            $this->tippecanoePath,
            $pmtilesTmpPath,
            $this->layerName,
            $this->CSVPath,
        ));
        if ($result->failed()) {
            throw new \RuntimeException("tippecanoe failed: " . $result->errorOutput());
        }
        rename($pmtilesTmpPath, $this->pmtilesPath);
    }
}