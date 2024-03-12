<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class IdentifyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
   * Identificar especie a partir de fotos usando la API de plantnet.org
   *
   * @return Response - JSON con la respuesta de plantnet.org.
   */
    public function post(Request $request)
    {
      if ($request->hasFile('images')) {
        $files = $request->file('images');
        $types = $request->input('types');
        $plantNetApiKey = env('PLANTNET_API_KEY');
        $client = new Client(['base_uri' => "https://my-api.plantnet.org", 'http_errors' => false]);
        $data = ['multipart' => [], 'organs' => $types];
        foreach ($files as $file) {
          if (file_exists($file)) {
            $extension = $file->getClientOriginalExtension();
            array_push($data['multipart'], [
                'name' => 'images',
                'contents' => fopen($file, 'r'),
                'filename' => mt_rand(100, 1000) . "." . $extension
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
