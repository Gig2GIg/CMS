<?php

use Faker\Generator as Faker;
use App\Models\User;

$factory->define(App\Models\Calendar::class, function (Faker $faker) {
    return [
        'production_type' => $faker->name,
        'project_name' => $faker->name,
        'start_date' => $faker->date(),
        'end_date' => $faker->date(),
        'user_id' => $faker->numberBetween(1, 4),
        'user_id' =>  factory(User::class)->create()->first()->id
    ];
});