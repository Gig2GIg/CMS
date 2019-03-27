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
    $router->post('/users/create',['uses'=>'UserController@store']);



});
$router->group(['middleware' => ['jwt.auth']], function () use ($router) {
    $router->post('/auditions/findby',['uses'=>'AuditionsController@findby']);
});
$router->group(['prefix'=>'t','middleware' => ['jwt.auth','acl:1']], function () use ($router) {
    //user routes
    $router->post('/me', ['uses' => 'AuthController@me']);
    $router->get('/users',['uses'=>'UserController@getAll']);
    $router->get('/users/show/{id}',['uses'=>'UserController@show']);
    $router->put('/users/update/{id}',['uses'=>'UserController@update']);
    $router->delete('/users/delete/{id}',['uses'=>'UserController@delete']);

    //auditions routes
    $router->post('/auditions/create',['uses'=>'AuditionsController@store']);
    $router->get('/auditions/show/{id}',['uses'=>'AuditionsController@get']);
    $router->get('/auditions/show',['uses'=>'AuditionsController@getAll']);
    $router->get('/auditions/showfull',['uses'=>'AuditionsController@getFullData']);
    $router->put('/auditions/update/{id}',['uses'=>'AuditionsController@update']);
   // $router->post('/auditions/findby',['uses'=>'AuditionsController@findby']);

});

$router->group(['prefix'=>'a','middleware' => ['jwt.auth','acl:2']], function () use ($router) {
    //auditions routes
    $router->get('/auditions/{auditions}/media',['uses'=>'AuditionsController@media']);
    $router->get('/auditions/show/{id}',['uses'=>'AuditionsController@get']);
    $router->get('/auditions/show',['uses'=>'AuditionsController@getAll']);
    $router->get('/auditions/showfull',['uses'=>'AuditionsController@getFullData']);
   // $router->post('/auditions/findby',['uses'=>'AuditionsController@findby']);

    //marketplace by category
    $router->get('/marketplace_categories', 'MarketplaceCategoriesController@getAll');

    $router->get('/marketplaces/search', 'MarketplaceController@search_by_title');

    Route::prefix('marketplace_categories')->group(function () use ($router) {
        $router->get('/{marketplaceCategory}/marketplaces', 'MarketplaceController@getAllMarketplaceByCategory')->where('id', '[0-9]+'); 
        $router->get('/{marketplaceCategory}/marketplaces/search', 'MarketplaceController@search_by_category_by_title')->where('id', '[0-9]+'); 
    });  

    // calendar routes
    $router->post('/calendar/create_event',['uses'=>'CalendarController@store']);
    $router->get('/calendar/show',['uses'=>'CalendarController@index']);
    $router->get('/calendar/show/{id}',['uses'=>'CalendarController@show']);

});



/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
*/
$router->group(['middleware' => ['jwt.auth','acl:3']], function () use ($router) {
    Route::namespace('Cms')->prefix('cms')->group(function () use ($router) {  
        //marketplace categories
        $router->get('/marketplace_categories', 'MarketplaceCategoriesController@getAll');

        $router->post('/marketplace_categories/create', 'MarketplaceCategoriesController@store');
        
        $router->get('/marketplace_categories/show/{id}','MarketplaceCategoriesController@getMarkeplaceCategory');
        $router->delete('/marketplace_categories/delete/{id}','MarketplaceCategoriesController@deleteMarkeplaceCategory');
        $router->put('/marketplace_categories/update/{id}','MarketplaceCategoriesController@updateMarkeplaceCategory');
        
        //marketplace by category
        Route::prefix('marketplace_categories')->group(function () use ($router) {
            $router->get('/{marketplaceCategory}/marketplaces', 'MarketplaceController@getAllMarketplaceByCategory')->where('id', '[0-9]+'); 
            $router->post('/{marketplaceCategory}/marketplaces/create', 'MarketplaceController@store')->where('id', '[0-9]+');  
        });  
        //marketplace
        $router->get('/marketplaces', 'MarketplaceController@getAll')->where('id', '[0-9]+'); 
        $router->put('/marketplaces/update/{id}','MarketplaceController@updateMarkeplace')->where('id', '[0-9]+'); 
        $router->delete('/marketplaces/delete/{id}','MarketplaceController@deleteMarkeplace')->where('id', '[0-9]+'); 
        $router->get('/marketplaces/show/{id}','MarketplaceController@getMarkeplace')->where('id', '[0-9]+'); 
    });
});
