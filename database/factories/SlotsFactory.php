<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Slots::class, function (Faker $faker) {
    return [
        'time'=>$faker->time(),
        'status'=>$faker->boolean(),
        'id_appointment'=>$faker->numberBetween(1,4)
    ];
});
