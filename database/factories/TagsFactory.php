<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Tags::class, function (Faker $faker) {
    return [
        'title'=> 'HIGH',
        'audition_id' =>  $faker->numberBetween(1,2),
        'user_id' =>  $faker->numberBetween(1,2)
    ];
});
