<?php

use Faker\Generator as Faker;
use App\Models\ContentSetting;

$factory->define(App\Models\ContentSetting::class, function (Faker $faker) {
    return [
        'term_of_use' => $faker->randomHtml(2,3),
        'privacy_policy' => $faker->randomHtml(2,3),
        'app_info' => $faker->randomHtml(2,3),
        'contact_us' => $faker->randomHtml(2,3)
    ];
});
