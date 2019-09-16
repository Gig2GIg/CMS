<?php

use Faker\Generator as Faker;
use App\Models\User;

$factory->define(App\Models\Calendar::class, function (Faker $faker) {
    return [
        'production_type' => $faker->name,
        'project_name' => $faker->name,
        'start_date' => \Carbon\Carbon::now()->toDateString(),
        'end_date' => \Carbon\CarbonImmutable::now()->add(6,'day')->toDateString(),
        'user_id' =>  factory(User::class)->create()->first()->id
    ];
});
