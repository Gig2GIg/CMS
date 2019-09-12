<?php 

namespace Test\Unit;

use Tests\TestCase;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\Auditions;
use App\Models\Appointments;
use App\Models\Tags;
use App\Models\Feedbacks;
use App\Models\UserSlots;
use App\Models\Slots;

class TagsControllerTest extends TestCase
{
    protected $token;
    protected $userId;
    protected $audition_id;
    protected $performance_id;


    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $director = factory(User::class)->create();

        $performance = factory(User::class)->create();

        $this->performance_id = $performance->id;

        $audition = factory(Auditions::class)->create(['user_id'=>$director->id]);

        $appoinment = factory(Appointments::class)->create([
            'auditions_id'=> $audition->id
        ]);

        $slot = factory(Slots::class)->create([
            'appointment_id'=> $appoinment->id
        ]);

        $slot_user = factory(UserSlots::class)->create([
            'user_id'=> $performance->id,
            'auditions_id'=> $audition->id,
            'slots_id'=> $slot->id
        ]);

        $work = [
            'vocals',
            'acting',
            'dancing',
        ];

        $this->audition_id = $audition->id;

        $user = factory(User::class)->create([
                'email' => 'token@test.com',
                'password' => bcrypt('123456')]
        );
        $this->userId = $user->id;
        $user->image()->create(['url' => $this->faker->url,'name'=>'test']);
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

    public function test_created_tags_201()
    {
        
        $response = $this->json('POST',
            'api/t/auditions/feedbacks/tags?token=' . $this->token, 
            [
                'title' => 'high',
                'audition_id' => $this->audition_id,
                'user_id' => $this->performance_id
            ]);

        $response->assertStatus(201);
    }

    public function test_update_tags_201()
    {
        
        $response = $this->json('POST',
            'api/t/auditions/feedbacks/tags?token=' . $this->token, 
            [
                'title' => 'high',
                'audition_id' => $this->audition_id,
                'user_id' => $this->performance_id
            ]);

        $response->assertStatus(201);
    }


    public function test_update_tags_from_array_200()
    {
        
        $tags = factory(Tags::class, 10)->create(['audition_id' => $this->audition_id, 'user_id' => $this->performance_id]);

       $data =  [
                    'tags' => [
                                ['title' => 'UPDA', 'id' => $tags[0]->id],
                                ['title' => 'UPDA','id' => $tags[1]->id],
                                ['title' => 'NEW','id' => null, 'audition_id' => $this->audition_id,'user_id' => $this->performance_id]      
                ] 
            ];

        $response = $this->json('PUT',
            'api/t/auditions/'. $this->audition_id .'/feedbacks/user/tags?token=' . $this->token, $data);

        $response->assertStatus(200);
    }

    public function test_update_tags_from_array_422()
    {
        
        $tags = factory(Tags::class, 10)->create(['audition_id' => $this->audition_id, 'user_id' => $this->performance_id]);

       $data =  [
                    'tags' => [
                                ['title' => 'UPDA', 'id' => $tags[0]->id],
                                ['title' => 'UPDA','id' => $tags[1]->id],
                                ['title' => 'NEW','id' => null, 'audition_id' => $this->audition_id,'user_id' => $this->performance_id]      
                ] 
            ];

        $response = $this->json('PUT',
            'api/t/auditions/'. '6262626'.'/feedbacks/user/tags?token=' . $this->token, $data);

        $response->assertStatus(422);
    }




    public function test_delete_tags_200()
    {
        
        $tag = factory(Tags::class)->create(['audition_id' => $this->audition_id, 'user_id' => $this->performance_id]);
        $response = $this->json('DELETE', 'api/t/auditions/feedbacks/tags/'. $tag->id. '/delete' .'?token=' . $this->token);

        $response->assertStatus(200);
    }

    public function test_list_tags_by_user_200()
    {
        
        $tag = factory(Tags::class, 50)->create(['audition_id' => $this->audition_id, 'user_id' => $this->performance_id]);

        $response = $this->json('GET', 'api/t/auditions/'. $this->audition_id. '/user/tags'. '?user_id='. $this->performance_id.'&token=' . $this->token);

        $response->assertStatus(200);
   
        $response->assertJsonStructure(['data' => [[
            'id',
            'title',
            'audition_id',
            'user_id'
        ]]]);
    }
}
