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
    // CONTENT SETTING
    $router->get('/content-settings','ContentSettingController@getAllContentSetting');
});
$router->group(['middleware' => ['jwt.auth']], function () use ($router) {
    $router->post('/auditions/findby',['uses'=>'AuditionsController@findby']);
    $router->get('/skills/show',['uses'=>'SkillsController@list']);

    //auditions
    $router->get('/auditions/show',['uses'=>'AuditionsController@getAll']);
    $router->get('/auditions/showfull',['uses'=>'AuditionsController@getFullData']);
    $router->get('/auditions/show/{id}',['uses'=>'AuditionsController@get']);
    $router->post('/appointments/auditions',['uses'=>'AppoinmentAuditionsController@store']);
    $router->get('/appointments/auditions/{audition}',['uses'=>'AppoinmentAuditionsController@show']);

    $router->get('/appointments/show/{id}/walk',['uses'=>'AppoinmentAuditionsController@showListWalk']);
    $router->get('/appointments/show/{id}/notwalk',['uses'=>'AppoinmentAuditionsController@showListNotWalk']);
//monitor update
    $router->get('/monitor/show/{id}',['uses'=>'MonitorManagerController@list']);

});
$router->group(['prefix'=>'t','middleware' => ['jwt.auth','acl:1']], function () use ($router) {
    //user routes
    $router->post('/me', ['uses' => 'AuthController@me']);
    $router->get('/users',['uses'=>'UserController@getAll']);
    $router->get('/users/show/{id}',['uses'=>'UserController@show']);
    $router->put('/users/update/{id}',['uses'=>'UserController@updateTablet']);
    $router->delete('/users/delete/{id}',['uses'=>'UserController@delete']);

    //auditions routes
    $router->post('/auditions/create',['uses'=>'AuditionsController@store']);
    $router->get('/auditions/upcoming',['uses'=>'AuditionManagementController@getUpcomingMangement']);
    $router->get('/auditions/passed',['uses'=>'AuditionManagementController@getPassedMangement']);
    $router->put('/auditions/update/{id}',['uses'=>'AuditionsController@update']);
    $router->put('/auditions/open/{id}',['uses'=>'AuditionManagementController@openAudition']);
    $router->put('/auditions/close/{id}',['uses'=>'AuditionManagementController@closeAudition']);


   //calendar routes
   $router->get('/user/{id}/calendar',['uses'=>'CalendarController@getAll']);

   //monitor updates
    $router->post('/monitor/updates',['uses'=>'MonitorManagerController@create']);

    //feedback
    $router->post('/feedbacks/add',['uses'=>'FeedBackController@store']);
    $router->get('/feedbacks/list',['uses'=>'FeedBackController@list']);

    //TYPE PRODUCTS
    $router->get('/type-products', 'TypeProductsController@getAll');

});

$router->group(['prefix'=>'a','middleware' => ['jwt.auth','acl:2']], function () use ($router) {
    //auditions routes
    $router->get('/users/show/{id}',['uses'=>'UserController@show']);
    $router->put('/users/update/{id}',['uses'=>'UserController@update']);
    $router->post('/auditions/user',['uses'=>'AuditionManagementController@saveUserAudition']);
    $router->get('/auditions/user/upcoming',['uses'=>'AuditionManagementController@getUpcoming']);
    $router->get('/auditions/user/upcoming/det/{id}',['uses'=>'AuditionManagementController@getUpcomingDet']);
    $router->get('/auditions/user/requested',['uses'=>'AuditionManagementController@getRequested']);
    $router->put('/auditions/user/update/{id}',['uses'=>'AuditionManagementController@updateAudition']);
    $router->get('/users',['uses'=>'UserController@getAll']);
    $router->put('/users/union/update',['uses'=>'UserController@updateMemberships']);
    $router->get('/users/union/list',['uses'=>'UserController@listMemberships']);

    //credits routes
    $router->post('/credits/create',['uses'=>'CreditsController@store']);
    $router->get('/credits/show',['uses'=>'CreditsController@getAll']);
    $router->get('/credits/show/{id}',['uses'=>'CreditsController@show']);
    $router->put('/credits/update/{id}',['uses'=>'CreditsController@update']);
    $router->delete('/credits/delete/{id}',['uses'=>'CreditsController@delete']);

    //skills
    $router->get('/skills/byuser',['uses'=>'SkillsController@byUser']);
    $router->post('/skills/add',['uses'=>'SkillsController@addToUser']);
    $router->delete('/skills/delete/{id}',['uses'=>'SkillsController@deleteToUser']);
    //managers
    $router->get('/managers/byuser',['uses'=>'ManagersController@byUser']);
    $router->post('/managers',['uses'=>'ManagersController@store']);
    $router->put('/managers/update/{id}',['uses'=>'ManagersController@update']);

    //aparences
    $router->get('/aparences/byuser',['uses'=>'AparencesController@byUser']);
    $router->post('/aparences',['uses'=>'AparencesController@store']);
    $router->put('/aparences/update/{id}',['uses'=>'AparencesController@update']);
    //Education routes
    $router->post('/educations/create',['uses'=>'EducationsController@store']);
    $router->get('/educations/show',['uses'=>'EducationsController@getAll']);
    $router->get('/educations/show/{id}',['uses'=>'EducationsController@show']);
    $router->put('/educations/update/{id}',['uses'=>'EducationsController@update']);
    $router->delete('/educations/delete/{id}',['uses'=>'EducationsController@delete']);

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
    $router->put('/calendar/update/{id}',['uses'=>'CalendarController@update']);
    $router->delete('/calendar/delete/{id}',['uses'=>'CalendarController@destroy']);
    
    // NOTIFICATION SETTING
    $router->put('/notification-setting/update/{id}','NotificationManagementController@update')->where('id', '[0-9]+'); 
    $router->get('/notification-settings','NotificationManagementController@getAll');

});



/*
|--------------------------------------------------------------------------
| CMS Routes
|--------------------------------------------------------------------------
*/
$router->group(['prefix'=>'cms', 'middleware' => ['jwt.auth','acl:3']], function () use ($router) {
    Route::namespace('Cms')->group(function () use ($router) {  
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

        //TYPE PRODUCTS
        $router->get('/type-products', 'TypeProductsController@getAll');
        $router->post('/type-products/create', 'TypeProductsController@store');
        $router->get('/type-products/show/{id}','TypeProductsController@show');
        $router->delete('/type-products/delete/{id}','TypeProductsController@delete');
        $router->put('/type-products/update/{id}','TypeProductsController@update');

        //SKILL SUGGESTIONS
        $router->get('/skill-suggestions', 'SkillSuggestionsController@getAll');
        $router->post('/skill-suggestions/create', 'SkillSuggestionsController@store');
        $router->get('/skill-suggestions/show/{id}','SkillSuggestionsController@show');
        $router->delete('/skill-suggestions/delete/{id}','SkillSuggestionsController@delete');
        $router->put('/skill-suggestions/update/{id}','SkillSuggestionsController@update');

        //NOTIFICATIONS 
        $router->post('/send-notifications', 'NotificationsController@sendNotifications');
        
    });
    // AUDITIONS
    $router->get('/auditions',['uses'=>'AuditionsController@getAll']);
    $router->get('/auditions/{id}',['uses'=>'AuditionsController@get']);
    $router->get('/auditions/{id}/contributors',['uses'=>'AuditionsController@show_contributors']);

    

});
