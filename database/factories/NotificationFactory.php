<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Notifications\Notification::class, function (Faker $faker,  $data) {
    return [
            'title' => $this->faker->name,
            'code' => 'XOWEWEW',
            'type' =>  $data['type'],
            'notificationable_type' =>  $data['notificationable_type'],
            'notificationable_id' => $faker->numberBetween(1,2)
        ];
});