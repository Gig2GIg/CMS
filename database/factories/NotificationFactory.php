<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Notification::class, function (Faker $faker, $type) {
    return [
            'title' => $this->faker->name,
            'code' => $this->faker->text(),
            'description' => $this->faker->paragraph(),
            'type' => $type['type']
        ];
});
