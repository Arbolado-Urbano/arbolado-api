<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

use App\Models\Arbol;

class GenerarPMTiles implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;
    public int $timeout = 900; // 15 minutes
    public int $tries = 1;
    public int $retryAfter = 7201;
    private string $layerName = "trees";
    private string $geoJsonPath;
    private string $pmtilesPath;
    private string $tippecanoePath;

    public function __construct()
    {
        $this->geoJsonPath    = public_path("arboles.geojson");
        $this->pmtilesPath   = public_path("arboles.pmtiles");
        $this->tippecanoePath = resource_path("bin/tippecanoe");
    }

    public function handle(): void
    {
        $geoJsonTmpPath = "$this->geoJsonPath.tmp";
        $fh = fopen($geoJsonTmpPath, 'w');
        fwrite($fh, '{"type":"FeatureCollection","features":[');
        $query = DB::table('arboles')
            ->join('especies', 'arboles.especie_id', '=', 'especies.id')
            ->whereNull('arboles.removido')
            ->select('arboles.id', 'arboles.lat', 'arboles.lng', 'especies.url')
            ->orderBy('arboles.id');
        $first = true;
        foreach ($query->lazy(1000) as $arbol) {
            if (!$first) fwrite($fh, ',');
            fwrite($fh, sprintf(
                '{"type":"Feature","geometry":{"type":"Point","coordinates":[%s,%s]},"properties":{"id":%s,"species":"%s"}}',
                $arbol->lng,
                $arbol->lat,
                $arbol->id,
                $arbol->url,
            ));
            $first = false;
        }
        fwrite($fh, ']}');
        fclose($fh);
        rename($geoJsonTmpPath, $this->geoJsonPath);
        $pmtilesTmpPath = "$this->pmtilesPath.tmp";
        Process::run(sprintf(
            '%s --output=%s --layer=%s --maximum-zoom=g --drop-densest-as-needed --force %s',
            $this->tippecanoePath,
            $pmtilesTmpPath,
            $this->layerName,
            $this->geoJsonPath,
        ));
        rename($pmtilesTmpPath, $this->pmtilesPath);
    }
}