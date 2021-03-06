<?php

use App\Models\Notifications\NotificationHistory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(NotificationHistory::class, function (Faker $faker) {
    return [
        'title' => $faker->title(),
        'code' => 'XSHGDSDG',
        'status' => 'unread',
        'user_id' => Str::random(10),
        'message' => $faker->title
    ];
});
