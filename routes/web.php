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
    return view('home');
});

$router->get('interactive-map', 'MapController@show');

$router->get('graph-history', 'HistoryController@show');

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('cases/{year}/{diseaseName}', ['uses' => 'MapController@cases']);
    $router->get('history/{diseaseName}/{stateIso}', ['uses' => 'HistoryController@history']);
});
