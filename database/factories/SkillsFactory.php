<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Skills::class, function (Faker $faker) {
    return [
        'name'=>$faker->domainWord()
    ];
});
