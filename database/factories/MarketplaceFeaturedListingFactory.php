<?php

use Faker\Generator as Faker;

$factory->define(App\Models\MarketplaceFeaturedListing::class, function (Faker $faker) {
    return [
        'business_name' =>  $faker->title(),
        'email' =>  $faker->safeEmail(),
      
    ];
});
