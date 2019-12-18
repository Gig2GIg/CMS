<?php

use Faker\Generator as Faker;

$factory->define(App\Models\TypeProduct::class, function (Faker $faker) {
    return [
        'name' => $faker->title
    ];
});
