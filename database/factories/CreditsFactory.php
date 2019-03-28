<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Credits::class, function (Faker $faker) {
    return [
        'name'=>$faker->words(3,1),
        'date'=>$faker->date(),
        'user_id'=>$faker->numberBetween(1,2),
    ];
});
