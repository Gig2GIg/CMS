<?php

use Faker\Generator as Faker;

$factory->define(App\Models\SkillSuggestion::class, function (Faker $faker) {
    return [
        'name' => $faker->title
    ];
});
