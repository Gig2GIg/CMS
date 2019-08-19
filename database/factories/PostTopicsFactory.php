<?php

use Faker\Generator as Faker;

$factory->define(App\Models\PostTopics::class, function (Faker $faker) {
    return [
        'post_id' => $faker->numberBetween(0,100),
        'topic_id' => $faker->numberBetween(0,100)
    ];
});
