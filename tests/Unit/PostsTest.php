<?php
namespace Test\Unit;

use App\Http\Repositories\PostsRepository;

use App\Models\Posts;

use Tests\TestCase;


class PostsTest extends TestCase
{
    public function test_create_post()
    {
        $data = [
            'title' =>  $this->faker->text(),
            'body' =>  $this->faker->paragraph()
        ];

        $posttRepo = new PostsRepository(new Posts());
        $post = $posttRepo->create($data);

        $this->assertInstanceOf(Posts::class, $post);
        $this->assertEquals($data['title'], $post->title);

    }

    public function test_delete_post()
    {
        $post = factory(Posts::class)->create();

        $posttRepo = new PostsRepository($post);
        $delete = $posttRepo->delete();
        $this->assertTrue($delete);
    }

    public function test_edit_post()
    {
        $post = factory(Posts::class)->create();
        $posttRepo = new PostsRepository($post);
        
        $data = [
            'title' =>  $this->faker->text(),
            'body' =>  $this->faker->paragraph()
        ];

        $edit = $posttRepo->update($data);
        $this->assertTrue($edit);

    }

}