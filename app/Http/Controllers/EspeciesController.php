<?php

namespace App\Http\Controllers;

use App\Models\Especie;

use App\Rules\CaptchaRule;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class EspeciesController extends Controller
{
    /**
     * Listar todas las especies
     *
     * @return \Illuminate\Http\Response - JSON con una lista de todas las especies.
     */
    public function list()
    {
        $especies = Especie::select([
            'nombre_cientifico',
            'nombre_comun',
            'id',
            'url',
            'comestible',
            'medicinal',
        ])->orderBy('nombre_cientifico')->get();
        $filtered = $especies->map(fn($especie) => array_filter(
            $especie->toArray(),
            fn($value) => $value !== null && $value !== '' && $value !== 0
        ));
        return response()->json($filtered);
    }

    /**
     * Identificar una especie a partir de fotos usando la API de PlantNet
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response - JSON con la respuesta de plantnet.org.
     */
    public function identify(Request $request)
    {
        $request->validate(['captcha'   => ['required', new CaptchaRule()]]);
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $types = $request->input('types');
            $plantNetApiKey = config('services.plantnet.key');
            $client = new Client(['base_uri' => 'https://my-api.plantnet.org', 'http_errors' => false]);
            $data = ['multipart' => [], 'organs' => $types];
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $extension = $file->getClientOriginalExtension();
                    array_push($data['multipart'], [
                        'name' => 'images',
                        'contents' => fopen($file, 'r'),
                        'filename' => mt_rand(100, 1000) . '.' . $extension
                    ]);
                }
            }
            $response = $client->request('POST', "/v2/identify/all?lang=es&type=kt&api-key=$plantNetApiKey", $data);
            $status = $response->getStatusCode();
            $content = $response->getBody()->getContents();
            return response($content, $status);
        }
        return response()->json([]);
    }
}
