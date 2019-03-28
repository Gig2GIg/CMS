<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Credits::class, function (Faker $faker) {
    $data =[
        'tv',
        'film',
        'commercial',
        'modeling'
    ];
    return [
        'name'=>$faker->words(3,1),
        'year'=>$faker->date('y'),
        'month'=>$faker->date('m'),
        'production'=>$faker->words(2,1),
        'rol'=>$faker->words(2,1),
        'type'=>$data[$faker->numberBetween(0,3)],
        'user_id'=>$faker->numberBetween(1,2),
    ];
});
