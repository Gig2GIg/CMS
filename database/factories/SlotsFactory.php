<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Slots::class, function (Faker $faker) {
    return [
        'time'=>$faker->time(),
        'status'=>$faker->boolean(),
        'is_walk'=>$faker->boolean(),
        'appointment_id'=>$faker->numberBetween(1,4)
    ];
});
