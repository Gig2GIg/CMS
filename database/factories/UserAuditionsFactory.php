<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserAuditions::class, function (Faker $faker) {
    return [
        'user_id'=>$faker->numberBetween(1,210),
        'auditions_id'=>$faker->numberBetween(1,210),
        'rol_id'=>$faker->numberBetween(1,210),
        'type'=>$faker->numberBetween(1,2)
    ];
});
