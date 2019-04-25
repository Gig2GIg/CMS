<?php

use Faker\Generator as Faker;

$factory->define(App\Models\AuditionVideos::class, function (Faker $faker) {
    return [
        'user_id'=>$faker->numberBetween(1.2),
        'auditions_id'=>$faker->numberBetween(1.2),
        'resource_id'=>$faker->numberBetween(1.2),
        'contributor_id'=>$faker->numberBetween(1.2),
    ];
});
