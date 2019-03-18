<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionControllerTest extends TestCase
{

    use RefreshDatabase;
    use WithFaker;

    public function test_create_audition_201()
    {
        $user = factory(User::class)->create();
        $data = [
            'title'=>$this->faker->title(),
            'date'=>$this->faker->date(),
            'time'=>$this->faker->time(),
            'location'=>$this->faker->address(),
            'description'=>$this->faker->paragraph(),
            'url'=>$this->faker->url(),
            'cover'=>$this->faker->imageUrl(),
            'union'=>$this->faker->word(),
            'contract'=>$this->faker->word(),
            'production'=>$this->faker->word.", ".$this->faker->word(),
            'status'=>$this->faker->boolean(),
            'user_id'=>$user->id,
            'roles'=>[
                [
                    'name'=>$this->faker->firstName(),
                    'description'=>$this->faker->paragraph(),
                    'cover'=>$this->faker->imageUrl()
                ],
                [
                    'name'=>$this->faker->firstName(),
                    'description'=>$this->faker->paragraph(),
                    'cover'=>$this->faker->imageUrl()
                ],
            ],
            'appointment'=>[
                'slots'=>$this->faker->numberBetween(10,20),
                'type'=>$this->faker->numberBetween(1,2),
                'length'=>$this->faker->time('i'),
                'start'=>$this->faker->time('H'),
                'end'=>$this->faker->time('H'),
                'slots'=>[
                    [
                        'time'=>$this->faker->time('i'),
                        'status'=>$this->faker->boolean()
                    ],
                    [
                        'time'=>$this->faker->time('i'),
                        'status'=>$this->faker->boolean()
                    ]
                ]
            ],
            'contributors'=>[
                [],[],[]
            ],
            'media'=>[
                ['type'=>1,'url'=>$this->faker->url()],
                ['type'=>2,'url'=>$this->faker->url()],
                ['type'=>3,'url'=>$this->faker->url()],
            ]
        ];

        $response = $this->json('POST',
            'api/auditions/create?token=' . $this->token,
            $data);
        $response->assertStatus(201);
        $response->assertJson(['data'=>'some']);

    }

}
