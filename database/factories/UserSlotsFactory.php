<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserSlots::class, function (Faker $faker) {
    return [
            'user_id' => $faker->numberBetween(1,4),
            'auditions_id' => $faker->numberBetween(1,4),
            'slots_id' => $faker->numberBetween(1,4),
            // 'roles_id' => $faker->numberBetween(1,4),
            'status' => 'reserved', //'checked'
            'favorite' => 1
    ];
});
