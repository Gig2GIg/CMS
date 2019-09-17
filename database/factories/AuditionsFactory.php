<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Auditions::class, function (Faker $faker) {
    $data= [
        'UNION',
        'NONUNION',
        'ANY'
    ];

    $dataContract= [
        'ANY',
        'PAID',
        'UNPAID'
    ];

    $dataProd = [
        'FILM,THEATHER,TVVIDEO',
        'VOICEOVER,FILM',
        'MODELING,COMMERCIALS',
        'COMMERCIALS,TVVIDEO'
    ];
    $randNumber = rand(0,2);
    $randNumber1 = rand(0,3);
    return [
        'title' => $faker->colorName." ".$faker->domainWord,
        'date' => $faker->date(),
        'time' => $faker->time(),
        'location' => json_encode([
            "latitude"=> $faker->latitude,
            "latitudeDelta"=> $faker->latitude,
            "longitude"=>$faker->longitude,
            "longitudeDelta"=>$faker->longitude,
        ]),
        'description' => 'Sed tempora itaque iusto. Praesentium explicabo',
        'url' => $faker->url(),
        'personal_information'=>'Sed tempora itaque iusto. Praesentium explicabo',
        'phone'=>$this->faker->phoneNumber,
        'email'=>$this->faker->companyEmail,
        'other_info'=>$this->faker->word,
        'additional_info'=>'Sed tempora itaque iusto. Praesentium explicabo',
        'union' => $data[$randNumber],
        'contract' => $dataContract[$randNumber],
        'production' => $dataProd[$randNumber1],
        'status' => $faker->numberBetween(0,2),
        'user_id' => $faker->numberBetween(1, 4)
    ];
});
