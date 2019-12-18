<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Dates::class, function (Faker $faker) {
    return [
        'from' =>\Carbon\Carbon::now(),
        'to' =>\Carbon\Carbon::tomorrow(),
        'type'=>$faker->numberBetween(1,3),

    ];
});
