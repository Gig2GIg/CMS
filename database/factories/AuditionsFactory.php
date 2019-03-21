<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Auditions::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(4),
        'date' => $faker->date(),
        'time' => $faker->time(),
        'location' => $faker->address(),
        'description' => $faker->paragraph(),
        'url' => $faker->url(),
        'union' => $faker->word(),
        'contract' => $faker->word(),
        'production' => $tags = $faker->word() . ',' . $faker->word(),
        'status' => $faker->boolean(),
        'user_id' => $faker->numberBetween(1, 4)
    ];
});
