<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Dates::class, function (Faker $faker) {
    return [
        'from'=>$faker->date(),
        'to'=>$faker->date(),
        'type'=>$faker->numberBetween(1,3),

    ];
});
