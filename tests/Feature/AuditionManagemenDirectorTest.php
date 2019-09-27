<?php

namespace Tests\Unit;

use App\Models\Appointments;
use App\Models\AuditionContract;
use App\Models\AuditionContributors;
use App\Models\Auditions;
use App\Models\AuditionVideos;
use App\Models\Credits;
use App\Models\Educations;
use App\Models\Roles;
use App\Models\Slots;
use App\Models\User;
use App\Models\UserAparence;
use App\Models\UserAuditions;
use App\Models\UserDetails;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditionManagemenDirectorTest extends TestCase
{
    protected $rolId;
    protected $userId;
    protected $auditionId;
    protected $slot;
    protected $token;

    public function test_auditions_upcomming_director()
    {


        $data = factory(Auditions::class, 10)->create([
            'user_id' => $this->userId,
        ]);
        $dataRol = factory(Roles::class, 10)->create(['auditions_id' => $data[0]->id]);
        $dataContrib = factory(AuditionContributors::class, 10)->create(['user_id' => $this->userId, 'auditions_id' => $data[0]->id]);
        $response = $this->json('GET',
            'api/t/auditions/upcoming?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'id',
            'id_user',
            'title',
            'date',
            'union',
            'contract',
            'production',
            'number_roles',
        ]]]);
    }

    public function test_auditions_passed_director()
    {


        $data = factory(Auditions::class, 10)->create([
            'user_id' => $this->userId,
        ]);
        $dataRol = factory(Roles::class, 10)->create(['auditions_id' => $data[0]->id]);
        $dataContrib = factory(AuditionContributors::class, 10)->create(['user_id' => $this->userId, 'auditions_id' => $data[0]->id]);
        $response = $this->json('GET',
            'api/t/auditions/passed?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'id',
            'id_user',
            'title',
            'date',
            'union',
            'contract',
            'production',
            'number_roles',
        ]]]);
    }

    public function test_auditions_passed_director_404()
    {
        Auditions::whereNotNull('id')->delete();
        $user = factory(User::class)->create();

        $data = factory(Auditions::class)->create([
            'user_id' => $user->id,
            'status' => 1
        ]);
        $dataRol = factory(Roles::class, 10)->create(['auditions_id' => $data->id]);
        $dataContrib = factory(AuditionContributors::class, 10)->create([
                'user_id' => $user->id,
                'auditions_id' => $data->id,
                'status' => true]
        );
        $response = $this->json('GET',
            'api/t/auditions/passed?token=' . $this->token);
        $response->assertStatus(404);
        $response->assertJson(['data' => 'Not Found Data']);
    }

    public function test_update_status_open_audition()
    {
        $response = $this->json('PUT', 'api/t/auditions/open/' . $this->auditionId . '?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJson(['data' => ['status' => 1]]);

    }

    public function test_update_status_close_audition()
    {

        $response = $this->json('PUT', 'api/t/auditions/close/' . $this->auditionId . '?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJson(['data' => ['status' => 2]]);

    }

    public function test_list_profile_user_audition_200()
    {
        $userprofile = factory(User::class)->create();
        factory(UserDetails::class)->create(['user_id' => $userprofile->id]);
        factory(Educations::class, 3)->create(['user_id' => $userprofile->id]);
        factory(Credits::class, 4)->create(['user_id' => $userprofile->id]);
        factory(UserAparence::class)->create(['user_id' => $userprofile->id]);
        $response = $this->json('GET', 'api/t/auditions/profile/user/' . $userprofile->id . '?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [
            'id',
            'details',
            'education',
            'credits',
            'aparence'
        ]]);
    }

    public function test_list_profile_user_audition_404()
    {

        $response = $this->json('GET', 'api/t/auditions/profile/user/99999?token=' . $this->token);
        $response->assertStatus(404);
        $response->assertJson(['data' => 'Not Found Data']);
    }

    public function test_audition_save_video_200()
    {
        $user = factory(User::class)->create();

        $data = factory(Auditions::class)->create([
            'user_id' => $user->id,
        ]);

        $appointment = factory(Appointments::class)->create([
            'auditions_id' => $data->id
        ]);

        $response = $this->json('POST',
            'api/t/auditions/video/save?token=' . $this->token, [
                'url' => $this->faker->imageUrl(),
                'appointment_id' => $appointment->id,
                'performer' => $user->id,
                'slot_id' => $this->slot->id
            ]);
        $response->assertStatus(200);
        //$response->assertJson(['data' => 'Not Found Data']);

    }

    public function test_audition_delete_video_200()
    {
        $user = factory(User::class)->create();

        $data = factory(Auditions::class)->create([
            'user_id' => $user->id,
        ]);
        $appointment = factory(Appointments::class)->create([
            'auditions_id' => $data->id
        ]);
        $video = factory(AuditionVideos::class)->create([
            'user_id' => $user->id,
            'appointment_id' => $appointment->id,
            'url' => $this->faker->imageUrl(),
            'contributors_id' => $this->userId,
            'slot_id' => $this->slot->id
        ]);

        $response = $this->json('DELETE',
            'api/t/auditions/video/delete/' . $video->id . '?token=' . $this->token);
        $response->assertStatus(200);
        //$response->assertJson(['data' => 'Not Found Data']);

    }

    public function test_audition_list_video_200()
    {
        $user = factory(User::class, 10)->create();
        $appointment = factory(Appointments::class)->create([
            'auditions_id' => $this->auditionId,
        ]);
        $user->each(function ($element) use ($appointment) {
            factory(UserDetails::class)->create([
                'user_id' => $element->id
            ]);

            factory(AuditionVideos::class)->create([
                'user_id' => $element->id,
                'appointment_id' => $appointment->id,
                'url' => $this->faker->imageUrl(),
                'contributors_id' => $this->userId,
                'slot_id' => $this->slot->id,
            ]);
        });
        $response = $this->json('GET',
            'api/t/auditions/video/list/' . $appointment->id . '?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [[
            'id',
            'name',
            'url'
        ]]]);


    }

    public function test_audition_contract_200()
    {
        $user = factory(User::class)->create();

        $data = factory(Auditions::class)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->json('POST',
            'api/t/auditions/contract/save?token=' . $this->token, [
                'url' => $this->faker->imageUrl(),
                'audition' => $data->id,
                'performer' => $user->id,

            ]);
        $response->assertStatus(200);
        //$response->assertJson(['data' => 'Not Found Data']);

    }

    public function test_audition_delete_contract_200()
    {
        $user = factory(User::class)->create();

        $data = factory(Auditions::class)->create([
            'user_id' => $user->id,
        ]);

        $contract = factory(AuditionContract::class)->create([
            'user_id' => $user->id,
            'auditions_id' => $data->id,
            'url' => $this->faker->imageUrl(),


        ]);

        $response = $this->json('DELETE',
            'api/t/auditions/contract/delete/' . $contract->id . '?token=' . $this->token);
        $response->assertStatus(200);
        //$response->assertJson(['data' => 'Not Found Data']);

    }

    public function test_audition_contract_by_user_audition_200()
    {
        $user = factory(User::class)->create();

        $contract = factory(AuditionContract::class)->create([
            'user_id' => $user->id,
            'auditions_id' => $this->auditionId,
            'url' => $this->faker->imageUrl(),


        ]);

        $response = $this->json('GET',
            'api/t/auditions/contract/' . $user->id . '/' . $this->auditionId . '?token=' . $this->token);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [

            "id",
            "user_id",
            "auditions_id",
            "url",


        ]
        ]);


    }

    protected function setUp(): void
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

        $audition = factory(Auditions::class)->create([
            'user_id' => $user->id
        ]);
        $appointment = factory(Appointments::class)->create([
            'auditions_id' => $audition->id,
        ]);
        $this->slot = factory(Slots::class)->create([
            'appointment_id' => $appointment->id
        ]);
        $audition->media()->create(['url' => $this->faker->url, 'type' => 4, 'name' => 'test']);
        $rol = factory(Roles::class)->create([
            'auditions_id' => $audition->id
        ]);
        $this->rolId = $rol->id;
        $this->userId = $user->id;
        $this->auditionId = $audition->id;
    }

}
