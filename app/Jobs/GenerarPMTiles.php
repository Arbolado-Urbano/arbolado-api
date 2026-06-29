<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Carbon;

class GenerarPMTiles implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;
    public int $timeout = 1800; // 30 minutes
    public int $tries = 1;
    public int $retryAfter = 7201;
    private string $layerName = "trees";
    private string $CSVHeader = "lat,lon,id,species,sources\n";
    private string $CSVPath;
    private string $pmtilesPath;
    private string $tippecanoePath;

    /**
     * Create a new job instance.
     *
     * Handles generating the PMTiles file.
     *
     * @param bool $force Whether to fully regenerate the intermediate CSV from all DB records (true) or apply only the latest incremental changes (false).
     */
    public function __construct(public bool $force)
    {
        $this->CSVPath = public_path("arboles.csv");
        $this->pmtilesPath = public_path("arboles.pmtiles");
        $this->tippecanoePath = resource_path("bin/tippecanoe");
    }

    public function handle(): void
    {
        $updatePMTiles = $this->generateCSV();
        if ($updatePMTiles) $this->generatePMTiles();
    }

    private function generateCSV() {
        // Get all the trees in the DB
        $query = DB::table('arboles')
            ->join('especies', 'arboles.especie_id', '=', 'especies.id')
            ->join('registros', 'registros.arbol_id', '=', 'arboles.id')
            ->whereNull('arboles.removido')
            ->select(
                'arboles.id',
                'arboles.lat',
                'arboles.lng',
                'especies.id AS especieId',
                DB::raw('GROUP_CONCAT(DISTINCT registros.fuente_id ORDER BY registros.fuente_id) as fuenteIds')
            )
            ->groupBy('arboles.id', 'arboles.lat', 'arboles.lng', 'especies.id')
            ->orderBy('arboles.id');
        $mode = 'w';
        $updatePMTiles = $this->force;
        // Unles the caller is forcing a file refresh check if a CSV file already exists
        if (!$this->force && file_exists($this->CSVPath)) {
            $updatePMTiles = false;
            // If the file exists we only need to append any new trees since its date of modification
            $mode = 'a';
            $existingCSVDate = Carbon::createFromTimestamp(filemtime($this->CSVPath));
            $query->where('arboles.updated_at', '>=', $existingCSVDate);
            // We also need to remove any trees that might have been removed since the file was last modified
            $removedSinceDate = DB::table('arboles')
                ->whereNotNull('arboles.removido')
                ->where('arboles.updated_at', '>=', $existingCSVDate)
                ->select('arboles.id')->pluck('id')->flip()->all();
            if (count($removedSinceDate) > 0) {
                $this->removeExistingFromCSV($removedSinceDate);
                $updatePMTiles = true;
            }
        }

        $fh = fopen($this->CSVPath, $mode);
        if ($mode === 'w') {
            // If we're writing from scratch then write the header
            fwrite($fh, $this->CSVHeader);
        }
        foreach ($query->lazy(1000) as $arbol) {
            $updatePMTiles = true;
            fwrite($fh, sprintf(
                "%s,%s,%s,%s,\",%s,\"\n",
                $arbol->lat,
                $arbol->lng,
                $arbol->id,
                $arbol->especieId,
                $arbol->fuenteIds,
            ));
        }
        fclose($fh);
        return $updatePMTiles;
    }

    private function removeExistingFromCSV($toRemoveIds) {
        $tmpPath = $this->CSVPath . '.tmp';
        $src = fopen($this->CSVPath, 'r');
        if (!$src) return; // No CSV file
        try {
            $firstLine = fgets($src);
            if (!$firstLine) return; // CSV file is empty
            $columns = explode(',', trim($firstLine));
            $idIndex = array_find_key($columns, fn($value) => trim($value) === 'id');
            if ($idIndex === null) return; // CSV file has no 'id' column
            $dst = fopen($tmpPath, 'w');
            try {
                // Write the header in the new file
                fwrite($dst, $this->CSVHeader);
                // Go trough the original file and filter out the trees to be removed
                while (($line = fgets($src)) !== false) {
                    $trimmed = trim($line);
                    if ($trimmed === '') continue;
                    $columns = explode(',', $trimmed);
                    $id = $columns[$idIndex] ?? null;
                    // Only write to the new file if the tree is not to be removed
                    if (!isset($toRemoveIds[$id])) {
                        fwrite($dst, $line);
                    }
                }
            } finally {
                fclose($dst);
            }
            // Overwrite the original file
            rename($tmpPath, $this->CSVPath);
        } finally {
            fclose($src);
        }
    }

    private function generatePMTiles() {
        $pmtilesTmpPath = "$this->pmtilesPath.tmp.pmtiles";
        $result = Process::run(sprintf(
            '%s --output=%s --layer=%s -zg --no-tile-size-limit --no-feature-limit -r1 --force %s',
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