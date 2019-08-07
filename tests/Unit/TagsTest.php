<?php 
namespace Test\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;

use App\Http\Repositories\TagsRepository;

use App\Models\Auditions;
use App\Models\Appointments;
use App\Models\Tags;
use App\Models\Feedbacks;
use App\Models\User;
use App\Models\UserSlots;
use App\Models\Slots;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TagsTest extends TestCase
{
    protected $feedback_id;

    public function setUp(): void
    {
        parent::setUp();

        $director = factory(User::class)->create();
        $performance = factory(User::class)->create();
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

        $feedback = factory(Feedbacks::class)->create([
            'auditions_id' => $audition->id,
            'user_id' => $performance->id, //id usuario que recibe evaluacion
            'evaluator_id' =>$director->id, //id de usuario que da feecback,
            'evaluation' => $this->faker->numberBetween(1, 5),
            'callback' => $this->faker->boolean(),
            'work' => $work[$this->faker->numberBetween(0, 2)],
            'favorite' => $this->faker->boolean(),
            'slot_id'=> $slot->id
        ]);

        $this->feedback_id = $feedback->id;
        
    }

    public function test_create_tags()
    {

        $data = [
            'title' => 'High',
            'feedback_id' => $this->feedback_id
        ];

        $tagRepo = new TagsRepository(new Tags());

        $tag = $tagRepo->create($data);
        $this->assertInstanceOf(Tags::class, $tag);
        $this->assertEquals($data['title'], $tag->title);
        
    }

    public function test_delete_tag()
    {

        $data = [
            'title' => 'High',
            'feedback_id' => $this->feedback_id
        ];

        $tagRepo = new TagsRepository(new Tags());
        $tag = $tagRepo->create($data);

        $delete = $tag->delete();
        $this->assertTrue($delete);
    }
}