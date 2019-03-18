<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Appointments::class, function (Faker $faker) {
    return [
        'slots'=>$faker->numberBetween(1,10),
        'type'=>$faker->numberBetween(1,2),
        'length'=>$faker->time(),
        'start' =>\Carbon\Carbon::now(),
        'end' =>\Carbon\Carbon::tomorrow(),
        'audition_id'=>$faker->numberBetween(1,2),
    ];
});
