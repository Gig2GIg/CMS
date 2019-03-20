<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserDetails;
use Tests\TestCase;


class AuditionControllerTest extends TestCase
{
    public function test_create_audition_201()
    {
        $cont1 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id'=>$cont1->id,
        ]);
        $cont2 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id'=>$cont2->id,
        ]);
        $data = [
            'title' => $this->faker->words(3,3),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'location' => $this->faker->address(),
            'description' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'cover' => $this->faker->imageUrl(),
            'union' => $this->faker->word(),
            'contract' => $this->faker->word(),
            'production' => $this->faker->word . ", " . $this->faker->word(),
            'status' => $this->faker->boolean(),
            'dates' => [
                [
                    'to' => $this->faker->date(),
                    'from' => $this->faker->date(),
                    'type'=>1
                ],
                [
                    'to' => $this->faker->date(),
                    'from' => $this->faker->date(),
                    'type'=>2
                ]
            ],
            'rehaesal_date' => [
                'to' => $this->faker->date(),
                'from' => $this->faker->date(),
            ],
            'roles' => [
                [
                    'name' => $this->faker->firstName(),
                    'description' => $this->faker->paragraph(),
                    'cover' => $this->faker->imageUrl()
                ],
                [
                    'name' => $this->faker->firstName(),
                    'description' => $this->faker->paragraph(),
                    'cover' => $this->faker->imageUrl()
                ],
            ],
            'appointment' => [
                'spaces' => $this->faker->numberBetween(10, 20),
                'type' => $this->faker->numberBetween(1, 2),
                'length' => $this->faker->time('i'),
                'start' => $this->faker->time('H'),
                'end' => $this->faker->time('H'),
                'slots' => [
                    [
                        'time' => $this->faker->time('i'),
                        'status' => $this->faker->boolean()
                    ],
                    [
                        'time' => $this->faker->time('i'),
                        'status' => $this->faker->boolean()
                    ]
                ]
            ],
            'contributors' => [
                ['user_id'=>$cont1->id],
                ['user_id'=>$cont2->id],

            ],
            'media' => [
                ['type' => 1, 'url' => $this->faker->url()],
                ['type' => 2, 'url' => $this->faker->url()],
                ['type' => 3, 'url' => $this->faker->url()],
            ]
        ];

        $response = $this->json('POST',
            'api/auditions/create?token=' . $this->token,
            $data);
        $response->assertStatus(201);
        $response->assertJson(['data' => []]);

    }

    public function test_create_audition_422(){
        $response = $this->json('POST',
            'api/auditions/create?token=' . $this->token,
            []);
        $response->assertStatus(422);

    }


    public function test_show_audition_200(){
      $id = 1;

        $response = $this->json('POST',
            'api/auditions/show/'.$id.'?token='. $this->token);
        $response->assertStatus(200);
    }

    public function test_show_audition_404(){
        $id = 20239;

        $response = $this->json('POST',
            'api/auditions/show/'.$id.'?token='. $this->token);
        $response->assertStatus(404);
    }
}
