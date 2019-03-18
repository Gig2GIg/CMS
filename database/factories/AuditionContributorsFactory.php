<?php

use Faker\Generator as Faker;

$factory->define(App\Models\AuditionContributors::class, function (Faker $faker) {
    return [
       'email'=>$faker->email(),
       'audition_id'=>$faker->numberBetween(1,3),
    ];
});
