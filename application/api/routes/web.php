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
    return $router->app->version();
});

$router->get('loginUserByToken','UsersController@loginUserByToken');

$router->group(['middleware' => 'hasToken'], function () use ($router) {
    $router->get('getAllCustomers','CustomersController@getAllCustomers');
});

//     $router->get('{resource}/definition','ApiController@getDefinition');

//     $router->get('{resource}[/{id}]','ApiController@show');
//     $router->post('{resource}','ApiController@create');
//     $router->put('{resource}/{id}','ApiController@update');
//     $router->delete('{resource}','ApiController@delete');

//     $router->put('{resource}/send/{id}','ApiController@send');

//     $router->get('/', function () use ($router) {
//         return json_encode(array('error' => true, 'message'=>"Missing Resource"));
//     });

 // });
