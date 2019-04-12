<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Appointments::class, function (Faker $faker) {
    return [
        'slots'=>$faker->numberBetween(1,10),
        'type'=>$faker->numberBetween(1,2),
        'length'=>($faker->numberBetween(1,6) * 10),
        'start' =>$faker->date('H'),
        'end' =>$faker->date('H'),
        'auditions_id'=>$faker->numberBetween(1,2),
    ];
});
