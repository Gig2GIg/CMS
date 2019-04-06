<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Notifications\NotificationSetting::class, function (Faker $faker) {
    return [
            'status' => 'on',
            'code' => 'autidion_update',
        ];
});