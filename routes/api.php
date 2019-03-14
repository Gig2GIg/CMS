<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

$router->group(['middleware' => ['api']], function () use ($router) {
    $router->post('/login', ['uses' => 'AuthController@login']);
    $router->post('/logout', ['uses' => 'AuthController@logout']);
    $router->post('/remember', ['uses' => 'User\UserController@sendPassword']);
//    $router->post('/me', ['uses' => 'AuthController@me']);
     $router->post('/users/create',['uses'=>'User\UserController@createUser']);
//    $router->post('/users',['uses'=>'User\UserController@getAll']);
//    $router->post('/users/show/{id}',['uses'=>'User\UserController@getUser']);
//    $router->put('/users/update/{id}',['uses'=>'User\UserController@updateUser']);
//    $router->delete('users/delete/{id}',['uses'=>'User\UserController@deleteUser']);


});
$router->group(['middleware' => ['jwt.auth']], function () use ($router) {
   // $router->post('/remember', ['uses' => 'AuthController@remember']);
    $router->post('/me', ['uses' => 'AuthController@me']);

    //$router->post('/users/create',['uses'=>'User\UserController@createUser']);
    $router->post('/users',['uses'=>'User\UserController@getAll']);
    $router->post('/users/show/{id}',['uses'=>'User\UserController@getUser']);
    $router->put('/users/update/{id}',['uses'=>'User\UserController@updateUser']);
    $router->delete('users/delete/{id}',['uses'=>'User\UserController@deleteUser']);


});


