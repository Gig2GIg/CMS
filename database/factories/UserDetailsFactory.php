<?php
use App\Models\UserDetails;
use Illuminate\Support\Str;
use Faker\Generator as Faker;


$factory->define(UserDetails::class,function (Faker $faker){
    return [
        'type' => $faker->numberBetween(1,3),
        'first_name' => $faker->name(),
        'last_name' => $faker->lastName(),
        'address' => $faker->address(),
        'city' =>$faker->city(),
        'state' => $faker->numberBetween(1,50),
        'birth' =>$faker->date(),
        'user_id'=>$faker->numberBetween(1,3),
        'location'=>$faker->latitude().' '. $faker->longitude(),
        'profesion'=>$faker->jobTitle(),
        'stage_name'=>$faker->word()
    ];
});
