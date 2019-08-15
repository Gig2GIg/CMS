<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Posts::class, function (Faker $faker) {
    return [
        'title' =>  $faker->title(),
        'url_media' =>  $faker->text(),
        'body' =>  $faker->paragraph(),
        'type' => 'blog',
        'search_to' =>  'both',
        'user_id' => $faker->numberBetween(0,100)
    ];
});
