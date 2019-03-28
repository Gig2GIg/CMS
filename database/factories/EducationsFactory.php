<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Educations::class, function (Faker $faker) {
    return [
        'school'=>$faker->words(2,1),
        'degree'=>$faker->word(),
        'instructor'=>$faker->name(),
        'location'=>$faker->address(),
        'year'=>$faker->date('Y'),
        'user_id'=>$faker->numberBetween(1,2),
    ];
});
