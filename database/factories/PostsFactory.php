<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Posts::class, function (Faker $faker) {
    return [
        'title' =>  $faker->title(),
        'body' =>  $faker->paragraph()
    ];
});
