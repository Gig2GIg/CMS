<?php

use Faker\Generator as Faker;
use App\Models\MarketplaceCategory;

$factory->define(App\Models\Marketplace::class, function (Faker $faker) {
    return [
        'address' => $faker->address,
        'title' => $faker->name,
        'phone_number' => $faker->phoneNumber(),
        'email' => $faker->safeEmail(),
        'services' => $faker->realText($maxNbChars = 200, $indexSize = 2),
        'marketplace_category_id' => $faker->numberBetween(1, 4),
        'marketplace_category_id' =>  factory(MarketplaceCategory::class)->create()->first()->id
    ];
});
