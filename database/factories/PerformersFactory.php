<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Performers;
use Faker\Generator as Faker;

$factory->define(Performers::class, function (Faker $faker) {
    return [
        'performer_id'=>$faker->numberBetween(1,2),
        'director_id'=>$faker->numberBetween(1,2),
        'uuid'=>$this->faker->uuid,
    ];
});
