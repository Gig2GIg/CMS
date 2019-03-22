<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Marketplace::class, function (Faker $faker) {
    return [
        'address' => $faker->address,
        'title' => $faker->name,
        'phone_number' => $faker->phoneNumber(),
        'email' => $faker->safeEmail(),
        'services' => $faker->realText($maxNbChars = 200, $indexSize = 2),
    ];
});
