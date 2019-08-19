<?php
namespace Test\Unit;

use App\Http\Repositories\PostTopicsRepository;

use App\Models\PostTopics;
use App\Models\Posts;
use App\Models\Topics;
use App\Models\User;

use Tests\TestCase;


class PostTopicsTest extends TestCase
{
    protected $postId;
    protected $topicId;

    public function setUp(): void
    {
        parent::setUp();
        $user = factory(User::class)->create();

        $post = factory(Posts::class)->create(['user_id' => $user->id]);
        $this->postId = $post->id;

        $topic = factory(Topics::class)->create();
        $this->topicId = $topic->id;

    }
    public function test_create_post_topic()
    {
        $data = [
            'post_id' =>    $this->postId,
            'topic_id' =>   $this->topicId
        ];

        $postTopicRepo = new PostTopicsRepository(new PostTopics());
        $postTopic = $postTopicRepo->create($data);

        $this->assertInstanceOf(PostTopics::class, $postTopic);
        $this->assertEquals($data['post_id'], $postTopic->post_id);

    }

    public function test_delete_post_topic()
    {
        $data = [
            'post_id' =>    $this->postId,
            'topic_id' =>   $this->postId
        ];

        $postTopic = factory(PostTopics::class)->create($data);

        $postTopicRepo = new PostTopicsRepository($postTopic);
        $delete = $postTopicRepo->delete();
        $this->assertTrue($delete);
    }

}