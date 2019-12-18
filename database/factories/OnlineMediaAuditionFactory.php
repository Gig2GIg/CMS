<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(\App\Models\OnlineMediaAudition::class, function (Faker $faker) {
    return [
       'appointment_id'=>1,
       'performer_id'=>1,
       'url'=>$faker->url,
       'type'=>$faker->word,
       'name'=>$faker->word
    ];
});
