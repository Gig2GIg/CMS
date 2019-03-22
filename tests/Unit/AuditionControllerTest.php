<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\Dates;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserDetails;
use Tests\TestCase;


class AuditionControllerTest extends TestCase
{
    public function test_create_audition_201()
    {
        $cont1 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont1->id,
        ]);
        $cont2 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont2->id,
        ]);
        $data = [
            'title' => $this->faker->words(3, 3),
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
                    'type' => 1
                ],
                [
                    'to' => $this->faker->date(),
                    'from' => $this->faker->date(),
                    'type' => 2
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
                ['user_id' => $cont1->id],
                ['user_id' => $cont2->id],

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

    public function test_create_audition_422()
    {
        $response = $this->json('POST',
            'api/auditions/create?token=' . $this->token,
            []);
        $response->assertStatus(422);

    }


    public function test_show_audition_200()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $roles = factory(Roles::class)->create(['auditions_id' => $audition->id]);
        $appoinments = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinments->id]);

        $response = $this->json('POST',
            'api/auditions/show/' . $audition->id . '?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function test_show_all_audition_200()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $roles = factory(Roles::class)->create(['auditions_id' => $audition->id]);
        $appoinments = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinments->id]);
        $audition2 = factory(Auditions::class)->create(['user_id' => $user->id]);
        $roles2 = factory(Roles::class)->create(['auditions_id' => $audition2->id]);
        $appoinments2 = factory(Appointments::class)->create(['auditions_id' => $audition2->id]);
        $slot2 = factory(Slots::class)->create(['appointment_id' => $appoinments2->id]);

        $response = $this->json('POST',
            'api/auditions/show?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function test_show_all_audition_404()
    {
        $response = $this->json('POST',
            'api/auditions/show?token=' . $this->token);
        $response->assertStatus(404);
    }

    public function test_show_audition_404()
    {
        $id = 20239;

        $response = $this->json('POST',
            'api/auditions/show/' . $id . '?token=' . $this->token);
        $response->assertStatus(404);
    }

    public function test_update_audition_200()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $date1 = factory(Dates::class)->create();
        $date2 = factory(Dates::class)->create(['auditions_id' => $audition->id,'type'=>2]);
        $roles = factory(Roles::class)->create(['auditions_id' => $audition->id]);
        $appoinments = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinments->id]);

        $data = [
            "title" => "Mrs.",
            "date" => "1978-01-20",
            "time" => "08:35:08",
            "location" => "889 Whitney Canyon\nConsidineton, WY 13935-0177",
            "description" => "Sed tempora itaque iusto. Praesentium explicabo pariatur vero quis deserunt assumenda qui. Libero at omnis illo incidunt nihil quam.",
            "url" => "http://jacobs.org/autem-consequatur-et-et-maxime-veniam.html",
            "dates" => [
                [
                    "id" => $date1->id,
                    "type" => "contract",
                    "to" => "2006-09-23",
                    "from" => "1970-10-11"
                ],
                [
                    "id" => $date2->id,
                    "type" => "rehearsal",
                    "to" => "1994-09-03",
                    "from" => "2007-02-04"
                ],
            ],
            "union" => "quia",
            "contract" => "possimus",
            "production" => "est, animi",
            "status" => 0,
            "user_id" => 3,
            "roles" => [
                [
                    "id" => $roles->id,
                    "name" => "Micaela",
                    "description" => "Ipsum in minima unde veniam eos ut unde. Hic error fugit in consequatur necessitatibus vel reprehenderit. Voluptatem laboriosam non quos praesentium ducimus id et.",
                    "image" => [
                        "id" => 16,
                        "url" => "https://lorempixel.com/640/480/?86366",
                        "type" => "4"
                    ],
                ],

            ],
            "media" => [
                [
                    "id" => 12,
                    "url" => "http://baumbach.com/repellat-voluptatem-excepturi-quam-et-dolores-officiis-eius",
                    "type" => "audio"

                ],

            ],
            "appointment" => [

                "general" => [
                    "id" => $appoinments->id,
                    "slots" => 10,
                    "type" => "time",
                    "length" => "41",
                    "start" => "07",
                    "end" => "10"
                ],
                "slots" => [
                    [
                        "id" => $slot->id,
                        "number" => null,
                        "time" => "34",
                        "status" => 0

                    ],

                ]
            ]
        ];
        $response = $this->json('PUT',
            'api/auditions/update/'.$user->id.'?token=' . $this->token,
            $data);
        $response->assertStatus(200);

    }
}
