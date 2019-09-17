<?php
namespace Test\Unit;

use App\Http\Repositories\PostsRepository;

use App\Models\Posts;
use App\Models\User;

use Tests\TestCase;


class PostsTest extends TestCase
{
    protected $userId;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();
        $this->userId = $user->id;

    }
    public function test_create_post()
    {
        $data = [
            'title' =>  $this->faker->title(),
            'body' =>  $this->faker->paragraph(),
            'user_id' => $this->userId

        ];

        $posttRepo = new PostsRepository(new Posts());
        $post = $posttRepo->create($data);

        $this->assertInstanceOf(Posts::class, $post);
        $this->assertEquals($data['title'], $post->title);

    }

    public function test_delete_post()
    {
        $post = factory(Posts::class)->create(['user_id' => $this->userId]);

        $posttRepo = new PostsRepository($post);
        $delete = $posttRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_edit_post()
    {
        $post = factory(Posts::class)->create(['user_id' => $this->userId]);
        $posttRepo = new PostsRepository($post);

        $data = [
            'title' =>  'test title to valite',
            'body' =>  $this->faker->paragraph()
        ];

        $edit = $posttRepo->update($data);
        $this->assertTrue($edit);

    }

}
