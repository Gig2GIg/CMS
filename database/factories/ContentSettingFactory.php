<?php

use Faker\Generator as Faker;
use App\Models\ContentSetting;

$factory->define(App\Models\ContentSetting::class, function (Faker $faker) {
    $text = "Dolor et sea lorem clita aliquyam. At sit et sit dolores, aliquyam invidunt consetetur sit accusam et lorem, diam aliquyam.";
    return [
        'term_of_use' => $text,
        'privacy_policy' => $text,
        'app_info' => $text,
        'contact_us' => $text
    ];
});
