<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */


use Faker\Generator as Faker;

$factory->define(\App\Models\UserSettings::class, function (Faker $faker) {
    $setting = [
        'FEEDBACK',
        'RECOMMENDATION'
    ];
    return [
        'user_id'=>$faker->numberBetween(1,2),
        'setting'=>$setting[$faker->numberBetween(0,1)],
        'value'=>$faker->boolean
    ];
});
