<?php

namespace App\Http\Controllers;

use App\Models\Especie;

use App\Rules\CaptchaRule;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class EspeciesController extends Controller
{
    /**
     * Listar especies
     *
     * @return \Illuminate\Http\Response - JSON con una lista de todas las especies.
     */
    public function list(Request $request)
    {
        $query = Especie::select([
            'nombre_cientifico',
            'nombre_comun',
            'id',
            'url',
            'icono',
            'color',
            'comestible',
            'medicinal',
        ])->where('nombre_cientifico', '<>', '');

        if ($request->has('comestibles')) {
            $query->where('comestible', '<>', '');
        }

        $especies = $query->orderBy('nombre_cientifico')->get();
        return response()->json($especies);
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
