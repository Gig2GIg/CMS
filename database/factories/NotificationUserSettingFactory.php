<?php

use Faker\Generator as Faker;

$factory->define(App\Models\NotificationUserSetting::class, function (Faker $faker) {
    return [
            'status' => 'on',
            'user_id' => $faker->numberBetween(1,2),
            'notification_id' => $faker->numberBetween(1,2)     
        ];
});
