<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserSkills::class, function (Faker $faker) {
    return [
        'user_id'=>$faker->numberBetween(1,2),
        'skills_id'=>$faker->numberBetween(1,2),
    ];
});
