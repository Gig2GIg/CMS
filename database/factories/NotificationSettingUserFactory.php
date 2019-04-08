<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Notifications\NotificationSettingUser::class, function (Faker $faker) {
    return [
        'notification_setting_id' => $faker->numberBetween(1, 4),
        'user_id' => $faker->numberBetween(1, 4),
        'status' => 'on'
    ];
});

