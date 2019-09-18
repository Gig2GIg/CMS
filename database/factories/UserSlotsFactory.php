<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserSlots::class, function (Faker $faker) {
    return [

        'user_id'=>$faker->numberBetween(1,3),
        'auditions_id'=>$faker->numberBetween(1,3),
        'roles_id'=>$faker->numberBetween(1,3),
        'status'=> 'reserved', //'checked'
        'favorite'=> 1, //'checked'
    ];
});
