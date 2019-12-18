<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserAparence::class, function (Faker $faker) {
    return [
        'height'=>$faker->numberBetween(150,210),
        'weight'=>$faker->numberBetween(50,110),
        'hair'=>$faker->colorName(),
        'eyes'=>$faker->colorName(),
        'race'=>$faker->word(),
        'user_id'=>$faker->numberBetween(1,2),
    ];
});
