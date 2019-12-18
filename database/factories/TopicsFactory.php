<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Topics::class, function (Faker $faker) {
     $data = ['off', 'on'];
    return [
        'title'=> 'HIGH',
        'status' =>  $data[$faker->numberBetween(0,1)]
    ];
});
