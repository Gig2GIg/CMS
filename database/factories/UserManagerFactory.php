<?php

use Faker\Generator as Faker;

$factory->define(App\Models\UserManager::class, function (Faker $faker) {
    return [
        'name'=>$faker->name(),
        'company'=>$faker->company(),
        'email'=>$faker->safeEmail(),
        'type'=>$faker->numberBetween(1,2),
        'notifications'=>$faker->boolean(),
        'user_id'=>$faker->numberBetween(1,2),
    ];
});
