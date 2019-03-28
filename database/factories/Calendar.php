<?php

use Faker\Generator as Faker;
use App\Models\User;

$factory->define(App\Models\Calendar::class, function (Faker $faker) {
    return [
        'production_type' => $faker->name,
        'project_name' => $faker->name,
        'start_date' => '2019-04-20',
        'end_date' => '2019-04-27',
        'user_id' => $faker->numberBetween(1, 4),
        'user_id' =>  factory(User::class)->create()->first()->id
    ];
});