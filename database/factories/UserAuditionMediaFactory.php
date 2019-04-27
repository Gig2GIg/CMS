<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserAuditionMedia::class, function (Faker $faker) {
    return [
        'user_id'=>$faker->numberBetween(1,2),
        'auditions_id'=>$faker->numberBetween(1,2)
    ];
});
