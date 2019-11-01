<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\AuditionContributors;
use App\Models\Dates;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\Notifications\NotificationHistory;
use App\Models\UserDetails;
use Tests\TestCase;


class AuditionControllerTest extends TestCase
{
    protected $token;
    protected $testId;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create([
                'email' => 'token@test.com',
                'password' => bcrypt('123456')]
        );
        $this->testId = $user->id;
        $user->image()->create(['url' => $this->faker->url,'name'=>$this->faker->word()]);
        $userDetails = factory(UserDetails::class)->create([
            'type'=>1,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token');

    }
    public function test_create_audition_201()
    {
        $cont1 = factory(User::class)->create([
            'email'=>'alphyon21@gmail.com'
        ]);
        factory(UserDetails::class)->create([
            'user_id' => $cont1->id,
        ]);
        $cont2 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont2->id,
        ]);
        $cont3 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont3->id,
        ]);
        $data = [
            'title' => $this->faker->words(3, 3),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'location' => [
                "latitude"=> $this->faker->latitude,
                "latitudeDelta"=> $this->faker->latitude,
                "longitude"=>$this->faker->longitude,
                "longitudeDelta"=>$this->faker->longitude,
            ],
            'description' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'personal_information'=>$this->faker->text(100),
            'additional_info'=>$this->faker->text(100),
            'phone'=>$this->faker->phoneNumber,
            'email'=>$this->faker->companyEmail,
            'other_info'=>$this->faker->text(100),
            'cover' => $this->faker->imageUrl(),
            'cover_name'=>$this->faker->word(),
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
                    'cover' => $this->faker->imageUrl(),
                    'name_cover'=>$this->faker->word(),
                ],
                [
                    'name' => $this->faker->firstName(),
                    'description' => $this->faker->paragraph(),
                    'cover' => $this->faker->imageUrl(),
                    'name_cover'=>$this->faker->word(),
                ],
            ],
            'appointment' => [
                'spaces' => $this->faker->numberBetween(10, 20),
                'type' => $this->faker->numberBetween(1, 2),
                'length' => $this->faker->time('i'),
                'start' => $this->faker->time('H'),
                'end' => $this->faker->time('H'),
                'round'=> 1,
                'status'=>true,
                'slots' => [
                    [
                        'time' => $this->faker->time('i'),
                        'status' => $this->faker->boolean(),
                        'is_walk' => $this->faker->boolean()
                    ],
                    [
                        'time' => $this->faker->time('i'),
                        'status' => $this->faker->boolean(),
                        'is_walk' => $this->faker->boolean()
                    ],
                    [
                        'time' => $this->faker->time('i'),
                        'status' => $this->faker->boolean(),
                        'is_walk' => $this->faker->boolean()
                    ],
                    [
                        'time' => $this->faker->time('i'),
                        'status' => $this->faker->boolean(),
                        'is_walk' => $this->faker->boolean()
                    ]
                ]
            ],
            'contributors' => [
                ['email' => $cont1->email],
                ['email' => $cont2->email],
                ['email' => $cont3->email],
                ['email' => 'g2g@test.com'],

            ],
            'media' => [
                ['type' => 1, 'url' => $this->faker->url(),'name'=>$this->faker->word(),'share'=>'yes'],
                ['type' => 2, 'url' => $this->faker->url(),'name'=>$this->faker->word(),'share'=>'no'],
                ['type' => 3, 'url' => $this->faker->url(),'name'=>$this->faker->word(),'share'=>'yes'],
            ]
        ];

        $response = $this->json('POST',
            'api/t/auditions/create?token=' . $this->token,
            $data);
        $response->assertStatus(201);
        $response->assertJson(['data' => []]);

    }

    public function test_create_audition_online_201()
    {
        $cont1 = factory(User::class)->create([
            'email' => 'jose.chavarria@elaniin.com'
        ]);
        factory(UserDetails::class)->create([
            'user_id' => $cont1->id,
        ]);
        $cont2 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont2->id,
        ]);
        $cont3 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont3->id,
        ]);
        $data = [
            'title' => $this->faker->words(3, 3),
            'date' => $this->faker->date(),
            'time' => $this->faker->time(),
            'location' => [
                "latitude" => $this->faker->latitude,
                "latitudeDelta" => $this->faker->latitude,
                "longitude" => $this->faker->longitude,
                "longitudeDelta" => $this->faker->longitude,
            ],
            'description' => $this->faker->paragraph(),
            'url' => $this->faker->url(),
            'personal_information' => $this->faker->text(100),
            'additional_info' => $this->faker->text(100),
            'phone' => $this->faker->phoneNumber,
            'online'=>true,
            'email' => $this->faker->companyEmail,
            'other_info' => $this->faker->text(100),
            'cover' => $this->faker->imageUrl(),
            'cover_name' => $this->faker->word(),
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
                    'cover' => $this->faker->imageUrl(),
                    'name_cover' => $this->faker->word(),
                ],
                [
                    'name' => $this->faker->firstName(),
                    'description' => $this->faker->paragraph(),
                    'cover' => $this->faker->imageUrl(),
                    'name_cover' => $this->faker->word(),
                ],
            ],
            'appointment' => [
                'spaces' => $this->faker->numberBetween(10, 20),
                'type' => $this->faker->numberBetween(1, 2),
                'length' => $this->faker->time('i'),
                'start' => $this->faker->time('H'),
                'end' => $this->faker->time('H'),
                'round' => 1,
                'status' => true,

            ],
            'contributors' => [
                ['email' => $cont1->email],
                ['email' => $cont2->email],
                ['email' => $cont3->email],
                ['email' => 'g2g@test.com'],

            ],
            'media' => [
                ['type' => 1, 'url' => $this->faker->url(), 'name' => $this->faker->word(), 'share' => 'yes'],
                ['type' => 2, 'url' => $this->faker->url(), 'name' => $this->faker->word(), 'share' => 'no'],
                ['type' => 3, 'url' => $this->faker->url(), 'name' => $this->faker->word(), 'share' => 'yes'],
            ]
        ];

        $response = $this->json('POST',
            'api/t/auditions/create?token=' . $this->token,
            $data);
        $response->assertStatus(201);
        $response->assertJson(['data' => []]);

    }

        public function test_create_audition_422()
    {
        $response = $this->json('POST',
            'api/t/auditions/create?token=' . $this->token,
            []);
        $response->assertStatus(422);

    }


    public function test_add_contruibuitor_audition_200()
    {
        $audition = factory(Auditions::class)->create([
            'user_id' => $this->testId,
        ]);

        $cont1 = factory(User::class)->create([
            'email'=>'alphyon21@gmail.com'
        ]);
        factory(UserDetails::class)->create([
            'user_id' => $cont1->id,
        ]);

        $cont2 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont2->id,
        ]);
        $cont3 = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $cont3->id,
        ]);

        $response = $this->json('POST',
            'api/t/auditions/'.  $audition->id.'/contributors?token=' . $this->token,
            [
                'contributors' => [
                    ['email' => $cont1->email, 'status'=> 0],
                    ['email' => $cont2->email, 'status'=> 0],
                    ['email' => $cont3->email, 'status'=> 0],
                    ['email' => 'g2g@test.com', 'status'=> 0],

                ]
            ]);
        $response->assertStatus(200);

    }



    public function test_show_audition_200()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $roles = factory(Roles::class)->create(['auditions_id' => $audition->id]);
        $appoinments = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinments->id]);

        $response = $this->json('GET',
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
        $audition2 = factory(Auditions::class)->create(['user_id' => $user->id,'status'=>false]);
        $roles2 = factory(Roles::class)->create(['auditions_id' => $audition2->id]);
        $appoinments2 = factory(Appointments::class)->create(['auditions_id' => $audition2->id]);
        $slot2 = factory(Slots::class)->create(['appointment_id' => $appoinments2->id]);

        $response = $this->json('GET',
            'api/auditions/show?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function test_show_all_audition_404()
    {
        $response = $this->json('GET',
            'api/auditions/show?token=' . $this->token);
        $response->assertStatus(404);
    }

    public function test_show_audition_404()
    {
        $id = 20239;

        $response = $this->json('GET',
            'api/auditions/show/' . $id . '?token=' . $this->token);
        $response->assertStatus(404);
    }

    public function test_update_audition_200()
    {
        $user = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);
        $audition->media()->create(['type'=>4,'url'=>$audition->url,'name'=>'test']);
        $date1 = $audition->dates()->create([
            'type'=>1,
            'to'=>$this->faker()->date(),
            'from'=>$this->faker()->date(),
        ]);
        $date2 = $audition->dates()->create([
            'type'=>2,
            'to'=>$this->faker()->date(),
            'from'=>$this->faker()->date(),
        ]);
        $roles = factory(Roles::class)->create(['auditions_id' => $audition->id]);
        $roles->image()->create(['type'=>4,'url'=>$this->faker->imageUrl(),'name'=>'test']);
        $appoinments = factory(Appointments::class)->create(['auditions_id' => $audition->id]);
        $slot = factory(Slots::class)->create(['appointment_id' => $appoinments->id]);

        $data = [
            'title' => 'aladin',
            'date' => '2019-01-20',
            'time' => '4',
            'location' => [
                "latitude"=> $this->faker->latitude,
                "latitudeDelta"=> $this->faker->latitude,
                "longitude"=>$this->faker->longitude,
                "longitudeDelta"=>$this->faker->longitude,
            ],
            'description' => 'Sed tempora itaque iusto. Praesentium explicabo pariatur vero quis deserunt assumenda qui. Libero at omnis illo incidunt nihil quam.',
            'url' => 'http://jacobs.org/autem-consequatur-et-et-maxime-veniam.html',
            'personal_information'=>'Sed tempora itaque iusto. Praesentium explicabo pariatur vero quis deserunt assumenda qui. Libero at omnis illo incidunt nihil quam.',
            'phone'=>$this->faker->phoneNumber,
            'additional_info'=>'Sed tempora itaque iusto. Praesentium explicabo pariatur vero',
            'email'=>$this->faker->companyEmail,
            'other_info'=>'Sed tempora itaque iusto. Praesentium explicabo pariatur vero',
            'cover_name'=>'covername',
            'dates' => [
                [
                    'id' => $date1->id,
                    'type' => 'contract',
                    'to' => '2006-09-23',
                    'from' => '1970-10-11'
                ],
                [
                    'id' => $date2->id,
                    'type' => 'rehearsal',
                    'to' => '1994-09-03',
                    'from' => '2007-02-04'
                ],
            ],
            'union' => 'quia',
            'contract' => 'possimus',
            'production' => 'est, animi',
            'status' => 0,
            'user_id' => 3,
            'roles' => [
                [
                    'id' => $roles->id,
                    'name' => 'Micaela',
                    'description' => 'Ipsum in minima unde veniam eos ut unde. Hic error fugit in consequatur necessitatibus vel reprehenderit. Voluptatem laboriosam non quos praesentium ducimus id et.',

                        'cover' => 'https://dfsdfsf.com/640/480/?86366',
                        'type' => '4',
                        'name'=>'test1'
                ],

            ],
            'media' => [
                [
                    'id' => 12,
                    'url' => 'http://el audosdfshw om/repellat-dfsdfsdfsdfsdf-excepturi-quam-et-dolores-officiis-eius',
                    'type' => 'audio',
                    'name'=>'testaudio'

                ],

            ],
            'appointment' => [[

                'general' => [
                    'id' => $appoinments->id,
                    'slots' => 10,
                    'type' => 'time',
                    'length' => '41',
                    'start' => '07',
                    'end' => '10'
                ],
                'slots' => [
                    [
                        'id' => $slot->id,
                        'number' => null,
                        'time' => '34',
                        'status' => 1

                    ],

                ]
            ]
        ]];
        $response = $this->json('PUT',
            'api/t/auditions/update/'.$audition->id.'?token=' . $this->token,
            $data);
        $response->assertStatus(200);

    }

    public function test_find_by_filter_all_params(){
        $user = factory(User::class)->create();
        factory(Auditions::class)->create([
            'title'=>'ordinary people',
            'union'=>'any',
            'contract'=>'unpaid',
            'production'=>'film,tv&video',
            'user_id'=>$user->id
        ]);
        $audition = factory(Auditions::class,40)->create(['user_id' => $user->id]);
        $response = $this->json('POST',
            'api/auditions/findby?token=' . $this->token,[
                'base'=>'or',
                'union'=>'any',
                'contract'=>'unpaid',
                'production'=>'film'
            ]);
        $count = $this->count($response);
        $response->assertStatus(200);
        $this->assertTrue($count > 0);
    }

    public function test_find_by_filter_all_params_only_base(){
        $user = factory(User::class)->create();

        $audition = factory(Auditions::class,40)->create(['user_id' => $user->id]);
        $response = $this->json('POST',
            'api/auditions/findby?token=' . $this->token,[
                'base'=>'or'
            ]);
        $count = $this->count($response);
        $response->assertStatus(200);
        $this->assertTrue($count > 0);
    }

    public function test_find_by_filter_multiple_tags(){
        $user = factory(User::class)->create();

        $audition = factory(Auditions::class,40)->create(['user_id' => $user->id]);
        $response = $this->json('POST',
            'api/auditions/findby?token=' . $this->token,[
                'union'=>'ANY',
                'contract'=>'UNPAID',
                'production'=>'MODELING'
            ]);
        $count = $this->count($response);
        $response->assertStatus(200);
        $this->assertTrue($count > 0);
    }

    public function test_find_by_filter_production_any(){
        $user = factory(User::class)->create();

        $audition = factory(Auditions::class,40)->create(['user_id' => $user->id]);
        $response = $this->json('POST',
            'api/auditions/findby?token=' . $this->token,[

                'production'=>'any'
            ]);
        $count = $this->count($response);
        $response->assertStatus(200);
        $this->assertTrue($count > 0);
    }

    public function test_accept_invite_contribuitor(){
        $user = factory(User::class)->create();

        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);


        $notification = factory(NotificationHistory::class)->create(['user_id' => $user->id]);


        $audition_contributor = factory(AuditionContributors::class)->create(['auditions_id'=> $audition->id,'user_id'=> $this->testId]);

        $response = $this->json('GET',
            'api/t/auditions/invite-accept/'. $audition_contributor->id .'?status=1'. '&notification_id='. $notification->id .'&token=' . $this->token);
        $response->assertStatus(200);

    }

    public function test_reject_invite_contribuitor(){
        $user = factory(User::class)->create();

        $audition = factory(Auditions::class)->create(['user_id' => $user->id]);

        $audition_contributor = factory(AuditionContributors::class)->create(['auditions_id'=> $audition->id,'user_id'=> $this->testId]);

        $notification = factory(NotificationHistory::class)->create(['user_id' => $user->id]);

        $response = $this->json('GET',
        'api/t/auditions/invite-accept/'. $audition_contributor->id .'?status=0'. '&notification_id='. $notification->id .'&token=' . $this->token);
         $response->assertStatus(200);

    }




}
