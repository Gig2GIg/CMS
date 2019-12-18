<?php

use Faker\Generator as Faker;

$factory->define(App\Models\AuditionContributors::class, function (Faker $faker) {
    return [
       'user_id'=>$faker->numberBetween(1,2),
       'auditions_id'=>$faker->numberBetween(1,3),
        'status'=>$faker->boolean()
    ];
});
