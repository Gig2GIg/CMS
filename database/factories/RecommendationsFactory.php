<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Recommendations::class, function (Faker $faker) {
    return [
        'user_id'=> $faker->numberBetween(1,2),
        'audition_id'=> $faker->numberBetween(1,3),
        'marketplace_id' => $faker->numberBetween(1,3)
    ];
});
