<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\AuditionContract;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\Credits;
use App\Models\Educations;
use App\Models\Feedbacks;
use App\Models\Performers;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserAparence;
use App\Models\UserDetails;
use App\Models\UserUnionMembers;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerformersDatabaseControllerTest extends TestCase
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
        $user->image()->create(['url' => $this->faker->url, 'name' => 'test']);
        $userDetails = factory(UserDetails::class)->create([
            'type' => 1,
            'user_id' => $user->id,
        ]);
        $response = $this->post('api/login', [
            'email' => 'token@test.com',
            'password' => '123456',
        ]);

        $this->token = $response->json('access_token');

    }

    public function test_add_performer()
    {
        $director = factory(User::class)->create();
        $user = factory(User::class)->create();
        $director2 = factory(User::class)->create();
        $performer = factory(Performers::class)->create([
            'performer_id' => $user->id,
            'director_id' => $director->id,
            'uuid' => $this->faker->uuid,
        ]);
        $response = $this->post('api/t/performers/add?token=' . $this->token, [
            'code' => $performer->uuid,
            'director' => $director2->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => 'Add User OK']);
    }

    public function test_user_exits()
    {
        $director = factory(User::class)->create();
        $user = factory(User::class)->create();
        $performer = factory(Performers::class)->create([
            'performer_id' => $user->id,
            'director_id' => $this->testId,
            'uuid' => $this->faker->uuid,
        ]);
        $response = $this->post('api/t/performers/add?token=' . $this->token, [
            'code' => $performer->uuid,
            'director' => $this->testId
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => 'This user exits in your data base']);
    }

    public function test_send_code()
    {
        $director = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $director->id,
            'type' => 1
        ]);
        $sharedDirector = factory(User::class)->create(['email' => 'alphyon21@gmail.com']);
        factory(UserDetails::class)->create([
            'user_id' => $sharedDirector->id,
            'type' => 1
        ]);
        $user = factory(User::class)->create();
        factory(UserDetails::class)->create([
            'user_id' => $user->id,
            'type' => 2
        ]);
        $performer = factory(Performers::class)->create([
            'performer_id' => $user->id,
            'director_id' => $director->id,
            'uuid' => $this->faker->uuid,
        ]);
        $response = $this->post('api/t/performers/code?token=' . $this->token, [
            'code' => $performer->uuid,
            'email' => $sharedDirector->email
        ]);

        $response->assertStatus(200);
        $response->assertJson(['data' => 'Code share']);
    }

    public function test_list_user_by_director()
    {
        $users = factory(User::class, 15)->create();

        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->get('api/t/performers/list?token=' . $this->token);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
        ]]]);

    }

    public function test_talent_filter()
    {
        $users = factory(User::class, 15)->create();

        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
        ]]]);
    }

    public function test_talent_filter_union()
    {
        $users = factory(User::class, 15)->create();
        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            factory(UserUnionMembers::class)->create(['user_id' => $item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e',
            'union' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
            'unions',

        ]]]);
    }

    public function test_talent_filter_non_union()
    {
        $users = factory(User::class, 15)->create();
        factory(UserUnionMembers::class)->create(['user_id' => $users[3]->id]);
        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);

            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e',
            'union' => 0
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
            'unions',

        ]]]);
    }

    public function test_talent_filter_union_any()
    {
        $users = factory(User::class, 15)->create();
        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            factory(UserUnionMembers::class)->create(['user_id' => $item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e',
            'union' => 2
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
            'unions',

        ]]]);
    }


    public function test_talent_filter_by_gender_male()
    {
        $users = factory(User::class, 15)->create();
        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            //  factory(UserUnionMembers::class)->create(['user_id'=>$item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e',
            'gender' => 'male'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
            'unions',

        ]]]);
    }

    public function test_talent_filter_by_gender_female()
    {
        $users = factory(User::class, 15)->create();
        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            //  factory(UserUnionMembers::class)->create(['user_id'=>$item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e',
            'gender' => 'female'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
            'unions',

        ]]]);
    }

    public function test_talent_filter_by_gender_other()
    {
        $users = factory(User::class, 15)->create();
        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            //  factory(UserUnionMembers::class)->create(['user_id'=>$item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e',
            'gender' => 'other'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
            'unions',

        ]]]);
    }

    public function test_talent_filter_by_gender_union()
    {
        $users = factory(User::class, 15)->create();
        $users->each(function ($item) {
            $item->image()->create(['type' => 'cover', 'url' => $this->faker->imageUrl(), 'name' => $this->faker->word()]);
            factory(\App\Models\UserDetails::class)->create([
                'user_id' => $item->id,
                'type' => 2
            ]);
            factory(Educations::class, 3)->create(['user_id' => $item->id]);
            factory(Credits::class, 4)->create(['user_id' => $item->id]);
            factory(UserAparence::class)->create(['user_id' => $item->id]);
            factory(UserUnionMembers::class)->create(['user_id' => $item->id]);
            factory(Performers::class)->create([
                'performer_id' => $item->id,
                'director_id' => $this->testId,
                'uuid' => $this->faker->uuid,
            ]);

        });

        $response = $this->post('api/t/performers/filter?token=' . $this->token, [
            'base' => 'e',
            'gender' => 'other',
            'union' => '1'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'image',
            'details',
            'appearance',
            'education',
            'credits',
            'calendar',
            'unions',

        ]]]);
    }

    public function test_get_tags_by_user_logged_200()
    {
        $data = factory(Auditions::class, 10)->create([
            'user_id' => $this->testId,
        ]);
        $user1 = factory(User::class)->create();

        $dataRol = factory(Roles::class, 10)->create(['auditions_id' => $data[0]->id]);
        $dataContrib = factory(AuditionContributors::class, 10)->create(['user_id' => $this->testId, 'auditions_id' => $data[0]->id]);
        $data->each(function ($item) {
            factory(Appointments::class, 10)->create([
                'auditions_id' => $item->id
            ])->each(function ($item2) {
                factory(Tags::class, 10)->create([
                    'appointment_id' => $item2->id,
                    'user_id' => User::all()->random()->id,
                    'setUser_id' => $this->testId
                ]);
            });
        });
        $audition = factory(Auditions::class)->create(
            ['user_id' => $this->testId]
        );
        $dataContrib = factory(AuditionContributors::class)->create(['user_id' => $this->testId, 'auditions_id' => $audition->id]);
        $testappoinment = factory(Appointments::class)->create([
            'auditions_id' => $audition->id,
        ]);


        $response = $this->json('GET', 'api/t/performers/tags?token=' . $this->token, [
            'user' => $user1->id
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                [
                    'id',
                    'title'
                ]
            ]
        ]);
    }

    public function test_get_tags_by_user_logged_404()
    {
        $data = factory(Auditions::class, 10)->create([
            'user_id' => $this->testId,
        ]);
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $dataRol = factory(Roles::class, 10)->create(['auditions_id' => $data[0]->id]);
        $dataContrib = factory(AuditionContributors::class, 10)->create(['user_id' => $this->testId, 'auditions_id' => $data[0]->id]);
        $data->each(function ($item) {
            factory(Appointments::class, 10)->create([
                'auditions_id' => $item->id
            ])->each(function ($item2) {
                factory(Tags::class, 10)->create([
                    'appointment_id' => $item2->id,
                    'user_id' => User::all()->random()->id,
                    'setUser_id' => $this->testId,
                ]);
            });
        });

        $response = $this->json('GET', 'api/t/performers/tags?token=' . $this->token, [
            'user' => 999
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
            ]
        ]);
    }


    public function test_get_all_commenst_by_user()
    {

        $user = factory(User::class)->create();
        $user1 = factory(User::class)->create();
        factory(Auditions::class, 10)->create([
            'user_id' => $user->id
        ]);
        factory(Appointments::class, 20)->create([
            'auditions_id' => Auditions::all()->random()->id,
        ]);
        $slots = factory(Slots::class, 30)->create([
            'appointment_id' => Appointments::all()->random()->id
        ]);

        $slots->each(function ($item) {
            factory(Feedbacks::class)->create([
                'user_id' => User::all()->random()->id,
                'appointment_id' => Appointments::all()->random()->id,
                'evaluator_id' => $this->testId,
                'slot_id' => $item->id,
                'comment' => $this->faker->text(100),
            ]);
        });
        $response = $this->json('GET', 'api/t/performers/comments?token=' . $this->token, [
            'user' => $user1->id
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                [
                    'id',
                    'comment'
                ]
            ]
        ]);

    }

    public function test_get_user_contracts_by_user_logged_200()
    {
        factory(User::class, 5)->create();
        $user = factory(User::class)->create();
        $auditions = factory(Auditions::class, 30)->create([
            'user_id' => User::all()->random()->id,
        ]);
        $auditions2 = factory(Auditions::class, 2)->create([
            'user_id' => $this->testId,
        ]);
        $auditions->each(function ($item) use ($user) {
            factory(AuditionContract::class)->create([
                'user_id' => $user->id,
                'auditions_id' => $item->id,
                'url' => $this->faker->url
            ]);
        });
        $auditions2->each(function ($item2) use ($user) {
            factory(AuditionContract::class)->create([
                'user_id' => $user->id,
                'auditions_id' => $item2->id,
                'url' => $this->faker->url
            ]);
        });


        $response = $this->json('GET', 'api/t/performers/contracts?token=' . $this->token, [
            'user' => $user->id
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                [
                    'id',
                    'url'
                ]
            ]
        ]);
    }

    public function test_get_user_contracts_by_user_logged_404()
    {
        factory(User::class, 5)->create();
        $user = factory(User::class)->create();
        $auditions = factory(Auditions::class, 30)->create([
            'user_id' => User::all()->random()->id,
        ]);
        factory(Auditions::class)->create([
            'user_id' => $this->testId,
        ]);
        $auditions->each(function ($item) use ($user) {
            factory(AuditionContract::class)->create([
                'user_id' => $user->id,
                'auditions_id' => $item->id,
                'url' => $this->faker->url
            ]);
        });


        $response = $this->json('GET', 'api/t/performers/contracts?token=' . $this->token, [
            'user' => 999
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [

            ]
        ]);
    }


}
