<?php 
namespace Test\Unit;

use App\Http\Exceptions\CreateException;
use App\Http\Exceptions\NotFoundException;

use App\Http\Repositories\CommentsRepository;

use App\Models\Posts;
use App\Models\Comments;
use App\Models\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentsTest extends TestCase
{
    protected $post;

    public function setUp(): void
    {
        parent::setUp();

        $user = factory(User::class)->create();
        $post = factory(Posts::class)->create(['user_id' => $user->id]);

        $this->post = $post;
    }

    public function test_create_comment()
    {
        $data = [
            'body' => $this->faker->paragraph(),
            'post_id' => $this->post->id
        ];

        $commentRepo = new CommentsRepository(new Comments());
        $comment = $commentRepo->create($data);
        $this->assertInstanceOf(Comments::class, $comment);
        $this->assertEquals($data['body'], $comment->body);
    }

    public function test_delete_comment()
    {
        $data = [
            'body' => $this->faker->paragraph(),
            'post_id' => $this->post->id
        ];
        
        $comment = factory(Comments::class)->create($data);
        $commentRepo = new CommentsRepository($comment);
        $delete = $commentRepo->delete();
        $this->assertTrue($delete);
    }
}