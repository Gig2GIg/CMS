<?php
use App\Models\UserUnionMembers;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
$factory->define(UserUnionMembers::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'user_id' => $faker->numberBetween(1,3),
    ];
});
