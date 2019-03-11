<?php
use App\Models\UserUnionMember;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
$factory->define(UserUnionMember::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'user_id' => $faker->numberBetween(1,3),
    ];
});
