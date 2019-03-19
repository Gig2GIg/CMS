<?php

use Faker\Generator as Faker;

$factory->define(App\Models\AuditionsDate::class, function (Faker $faker) {
    return [
       'type'=>$faker->numberBetween(1,2),
       'to'=>$faker->date(),
       'from'=>$faker->date(),
       'auditions_id'=>$faker->numberBetween(1,2)
    ];
});
