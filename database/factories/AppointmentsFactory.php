<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Appointments::class, function (Faker $faker) {
    return [
        'date' => $faker->date(),
        'time' => $faker->time(),
        'location' => json_encode([
            "latitude"=> $faker->latitude,
            "latitudeDelta"=> $faker->latitude,
            "longitude"=>$faker->longitude,
            "longitudeDelta"=>$faker->longitude,
        ]),
        'slots'=>$faker->numberBetween(1,10),
        'type'=>$faker->numberBetween(1,2),
        'length'=>($faker->numberBetween(1,6) * 10),
        'start' =>$faker->date('H'),
        'end' =>$faker->date('H'),
        'round'=>1,
        'status'=>true,
        'auditions_id'=>$faker->numberBetween(1,2),
    ];
});
