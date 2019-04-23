<?php
use App\Models\UserDetails;
use Illuminate\Support\Str;
use Faker\Generator as Faker;


$factory->define(UserDetails::class,function (Faker $faker){
    return [
        'type' => $faker->numberBetween(1,3),
        'first_name' => $faker->firstName(),
        'last_name' => $faker->lastName(),
        'address' => $faker->address(),
        'city' =>$faker->city(),
        'state' => $faker->numberBetween(1,50),
        'birth' =>$faker->date(),
        'user_id'=>$faker->numberBetween(1,3),
//        'location' => json_encode([
//            "latitude"=> $faker->latitude,
//            "latitudeDelta"=> $faker->latitude,
//            "longitude"=>$faker->longitude,
//            "longitudeDelta"=>$faker->longitude,
//        ]),
        'profesion'=>$faker->jobTitle(),
        'stage_name'=>$faker->title(),
        'zip'=>$faker->numberBetween(1000,10000),
    ];
});
