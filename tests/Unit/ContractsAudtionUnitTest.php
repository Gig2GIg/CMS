<?php

namespace Tests\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;
use App\Http\Repositories\AuditionContractRepository;
use App\Models\Appointments;
use App\Models\Auditions;
use App\Models\AuditionContract;
use App\Models\Resources;
use App\Models\Slots;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContractsAudtionUnitTest extends TestCase
{
    protected $userId;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $user = factory(User::class)->create();
        $this->userId = $user->id;
    }

    public function test_create_audition_contract()
    {
        $contributors = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id'=>$contributors->id,
        ]);
        $appointment = factory(Appointments::class)->create([
            'auditions_id'=>$audition->id,
        ]);
        $slot = factory(Slots::class)->create([
           'appointment_id'=>$appointment->id
        ]);
        $repo = new AuditionContractRepository(New AuditionContract());

        $data = [
            'user_id'=>$this->userId,
            'contributors_id'=>$contributors->id,
            'url'=>$this->faker->imageUrl(),
            'auditions_id'=>$audition->id,
            'slot_id'=>$slot->id
        ];
        $contractaudition = $repo->create($data);
        $this->assertInstanceOf(AuditionContract::class, $contractaudition);


    }
    public function test_create_audition_contract_exception()
    {
        $this->expectException(CreateException::class);
        $repo = new AuditionContractRepository(New AuditionContract());
        $video = $repo->create([]);
        $this->assertInstanceOf(AuditionContract::class, $video);
    }
    public function test_contrat_show_by_id(){
        $contributors = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id'=>$contributors->id,
        ]);
        $appointment = factory(Appointments::class)->create([
            'auditions_id'=>$audition->id,
        ]);
        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appointment->id
        ]);
        $resource = new Resources();

        $vid = factory(AuditionContract::class)->create([
            'user_id'=>$this->userId,

            'url'=>$this->faker->imageUrl(),
            'auditions_id'=>$audition->id,

        ]);

        $videoRepo = new AuditionContractRepository(new AuditionContract());
        $data = $videoRepo->find($vid->id);
        $this->assertInstanceOf(AuditionContract::class, $data);
    }

    public function test_contract_show_list(){
        $contributors = factory(User::class)->create();
        $audition = factory(Auditions::class)->create([
            'user_id'=>$contributors->id,
        ]);
        $appointment = factory(Appointments::class)->create([
            'auditions_id'=>$audition->id,
        ]);
        $slot = factory(Slots::class)->create([
            'appointment_id'=>$appointment->id
        ]);
        $resource = new Resources();

        $vid = factory(AuditionContract::class,3)->create([
            'user_id'=>$this->userId,

            'url'=>$this->faker->imageUrl(),
            'auditions_id'=>$audition->id,

        ]);

        $videoRepo = new AuditionContractRepository(new AuditionContract());
        $data = $videoRepo->findbyparam('auditions_id',$audition->id);
        $this->assertTrue($data->get()->count() > 2);
    }

    public function test_video_audition_show_exception()
    {
        $this->expectException(NotFoundException::class);
        $skill_userRepo = new AuditionContractRepository(new AuditionContract());
        $skill_userRepo->find(28374);
    }

}