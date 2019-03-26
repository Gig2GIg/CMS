<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Auditions::class, function (Faker $faker) {
    $data= [
        'union',
        'notunion',
        'any'
    ];

    $dataContract= [
        'any',
        'paid',
        'unpaid'
    ];

    $dataProd = [
        'film,theather,tvvideo',
        'voiceover,film',
        'modeling,commercials',
        'comercials,tvvideo'
    ];
    $randNumber = rand(0,2);
    $randNumber1 = rand(0,3);
    return [
        'title' => $faker->sentence(4),
        'date' => $faker->date(),
        'time' => $faker->time(),
        'location' => $faker->address(),
        'description' => $faker->paragraph(),
        'url' => $faker->url(),
        'union' => $data[$randNumber],
        'contract' => $dataContract[$randNumber],
        'production' => $dataProd[$randNumber1],
        'status' => $faker->boolean(),
        'user_id' => $faker->numberBetween(1, 4)
    ];
});
