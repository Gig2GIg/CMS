<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Comments::class, function (Faker $faker) {
    return [
        'body' =>  $faker->paragraph(),
        'post_id' => $faker->numberBetween(0,100)
    ];
});
