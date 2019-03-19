<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Roles::class, function (Faker $faker) {
    return [
        'name'=>$faker->title,
        'description'=>$faker->paragraph(),
        'auditions_id'=>$faker->numberBetween(1,3),

    ];
});
