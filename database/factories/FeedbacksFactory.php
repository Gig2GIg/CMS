<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Feedbacks::class, function (Faker $faker) {
    $work = [
        'vocals',
        'acting',
        'dancing'
    ];
    return [
        'auditions_id'=>$faker->numberBetween(1,2),
        'user_id'=>$faker->numberBetween(1,2),
        'evaluator_id'=>$faker->numberBetween(1,2),
        'evaluation'=>$faker->numberBetween(1,5),
        'callback'=>$faker->boolean(),
        'work'=>$work[$faker->numberBetween(0,2)],
        'favorite'=>$faker->boolean()
    ];
});
