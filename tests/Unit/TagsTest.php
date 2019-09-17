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
    protected $audition_id;
    protected $performance_id;

    public function setUp(): void
    {
        parent::setUp();

        $director = factory(User::class)->create();
        $performance = factory(User::class)->create();
        $audition = factory(Auditions::class)->create(['user_id'=>$director->id]);
        $this->audition_id = $audition->id;
        $this->performance_id = $performance->id;
    }

    public function test_create_tags()
    {

        $data = [
            'title' => 'High',
            'audition_id' => $this->audition_id,
            'user_id' => $this->performance_id
        ];

        $tagRepo = new TagsRepository(new Tags());

        $tag = $tagRepo->create($data);
        $this->assertInstanceOf(Tags::class, $tag);
        $this->assertEquals($data['title'], $tag->title);
        
    }

    /* @test */
    public function test_user_can_delete_a_tag()
    {
        $data = [
            'title' => 'High',
            'audition_id' => $this->audition_id,
            'user_id' => $this->performance_id
        ];

        $tagRepo = new TagsRepository(new Tags());
        $tag = $tagRepo->create($data);

        $delete = $tag->delete();
        $this->assertTrue($delete);
    }
}