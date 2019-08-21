<?php 

namespace Test\Unit;

use Tests\TestCase;

use App\Models\User;
use App\Models\UserDetails;
use App\Models\Posts;
use App\Models\Topics;

class PostsControllerTest extends TestCase
{
    protected $token;
    protected $userId;
    protected $token2;
    protected $userId2;
    protected $topicId;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
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


        $user2 = factory(User::class)->create([
            'email' => 'token2@test.com',
            'password' => bcrypt('123456')]
        );
        
        $this->userId2 = $user2->id;
        $user2->image()->create(['url' => $this->faker->url,'name'=>'test']);
        $userDetails = factory(UserDetails::class)->create([
            'type' => 2,
            'user_id' => $user2->id,
        ]);

        $response = $this->post('api/login', [
            'email' => 'token2@test.com',
            'password' => '123456',
        ]);

        $this->token2 = $response->json('access_token');


        $topic = factory(Topics::class)->create();
        $this->topicId = $topic->id;


    }

    public function test_created_posts_201()
    {
        $topic_1 = factory(Topics::class)->create();
        $topic_2 = factory(Topics::class)->create();
        $topic_3 = factory(Topics::class)->create();
        
        $response = $this->json('POST',
            'api/t/blog/posts?token=' . $this->token, 
            [
                'title' =>  $this->faker->title(),
                'url_media' =>  $this->faker->url(),
                'body' =>  $this->faker->paragraph(),
                'type' => 'blog',
                'search_to' =>  'both',
                'topic_id' => $this->topicId,
                'topic_ids' => [
                    ['id' => $topic_1->id],
                    ['id' => $topic_2->id],
                    ['id' => $topic_3->id]
                ]
            ]);

        $response->assertStatus(201);

    }

    public function test_search_by_title_posts_200()
    {
        $post1 = factory(Posts::class, 2)->create(['user_id' => $this->userId, 'type'=> 'blog']);
        $post2 = factory(Posts::class, 2)->create(['user_id' => $this->userId, 'type'=> 'forum']);
 
        $query = $post1->first()->title;
   
        $response = $this->json('GET',
            'api/t/blog/posts/find_by_title?value='.$query.'&token=' . $this->token);

        $response->assertStatus(200);

    }

    public function test_search_by_title_forum_200()
    {
        $post1 = factory(Posts::class, 2)->create(['user_id' => $this->userId, 'type'=> 'blog']);
        $post2 = factory(Posts::class, 2)->create(['user_id' => $this->userId, 'type'=> 'forum']);
 
        $query = $post1->first()->title;
   
        $response = $this->json('GET',
            'api/a/forum/posts/find_by_title?value='.$query.'&token=' . $this->token2);

        $response->assertStatus(200);

    }

    public function test_list_forum_200()
    {
        factory(Posts::class, 10)->create(['user_id' => $this->userId, 'type'=> 'blog']);
        factory(Posts::class, 2)->create(['user_id' => $this->userId, 'type'=> 'forum']);

        $response = $this->json('GET', 'api/a/forum/posts'. '?token=' . $this->token2);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
  
    }



    public function test_created_posts_to_performance201()
    {
        $topic_1 = factory(Topics::class)->create();
        $topic_2 = factory(Topics::class)->create();
        $topic_3 = factory(Topics::class)->create();
        
        $response = $this->json('POST',
            'api/t/blog/posts?token=' . $this->token, 
            [
                'title' =>  $this->faker->title(),
                'url_media' =>  $this->faker->url(),
                'body' =>  $this->faker->paragraph(),
                'type' => 'forum',
                'search_to' =>  'performance',
                'topic_id' => $this->topicId,
                'topic_ids' => [
                    ['id' => $topic_1->id],
                    ['id' => $topic_2->id],
                    ['id' => $topic_3->id]
                ]
            ]);

        $response->assertStatus(201);

    }

    public function test_update_posts_200()
    {
        $post = factory(Posts::class)->create(['user_id' => $this->userId]);
        
        $response = $this->json('PUT',
            'api/t/blog/posts/'. $post->id .'?token=' . $this->token, 
            [
                'title' =>  $this->faker->title(),
                'url_media' =>  $this->faker->url(),
                'body' =>  $this->faker->paragraph(),
                'type' => 'blog',
                'search_to' =>  'both'
            ]);

        $response->assertStatus(200);

    }


    public function test_delete_posts_200()
    { 
        
        $post = factory(Posts::class)->create(['user_id' => $this->userId]);
     
        $response = $this->json('DELETE', 'api/t/blog/posts/'. $post->id. '/delete' .'?token=' . $this->token);
        $response->assertStatus(200);
    }

    public function test_list_posts_200()
    {
        $post = factory(Posts::class, 20)->create(['user_id' => $this->userId]);
        $response = $this->json('GET', 'api/t/blog/posts'. '?token=' . $this->token);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
  
    }

   
}
