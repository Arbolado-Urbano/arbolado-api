<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return "Arbolado Urbano API V1.0.0";
});

$router->get('/fuentes/{slug}', 'ArbolesController@getSource');
$router->get('/especies', 'EspeciesController@list');
$router->get('/arboles', 'ArbolesController@list');
$router->get('/arboles/{id}', 'ArbolesController@get');
$router->post('/arboles', 'ArbolesController@add');
$router->post('/identificar', 'IdentifyController@post');

$router->options('{all:.*}', ['middleware' => 'cors.options', function () {
    return response('');
}]);
