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
    $router->post('/remember', ['uses' => 'UserController@sendPassword']);
     $router->post('/users/create',['uses'=>'UserController@createUser']);

});
$router->group(['middleware' => ['jwt.auth']], function () use ($router) {
    $router->post('/me', ['uses' => 'AuthController@me']);
    $router->post('/users',['uses'=>'UserController@getAll']);
    $router->post('/users/show/{id}',['uses'=>'UserController@getUser']);
    $router->put('/users/update/{id}',['uses'=>'UserController@updateUser']);
    $router->delete('users/delete/{id}',['uses'=>'UserController@deleteUser']);


});


