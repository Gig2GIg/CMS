<?php

use Faker\Generator as Faker;

$factory->define(App\Models\AuditionVideos::class, function (Faker $faker) {
    return [
        'user_id'=>$faker->numberBetween(1.2),
        'appointment_id'=>$faker->numberBetween(1.2),
        'url'=>$faker->imageUrl(),
        'contributors_id'=>$faker->numberBetween(1.2),
    ];
});
