<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Monitor::class, function (Faker $faker) {
    return [
        'appointment_id'=>$faker->numberBetween(1,2),
        'time'=>$faker->time(),
        'title'=>$faker->words(4,1),
    ];
});
