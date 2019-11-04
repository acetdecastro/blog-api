<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use App\User;
use App\Article;
use \Tymon\JWTAuth\Facades\JWTAuth;

class ArticlesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    private $requiredFields = [
        'title',
        'description',
    ];

    private function validData()
    {
        return [
            'title' => 'Test Title',
            'description' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Voluptatem voluptas libero fugit provident mollitia esse, atque harum, reiciendis, illo nesciunt nam incidunt officia sunt tempore omnis. Libero dolore provident sunt!',
        ];
    }

    /*
    * This method runs before any test runs
    * Creates a user on every test
    * returns void
    */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function fields_are_required()
    {
        collect($this->requiredFields)
            ->each(function ($field) {

                $token = JWTAuth::fromUser($this->user);

                $headers = [
                    'Authorization' => 'Bearer ' . $token
                ];

                $response = $this->post(
                    '/api/articles',
                    array_merge($this->validData(), [$field => '']),
                    $headers
                );

                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Article::all());
            });
    }

    /** @test */
    public function a_title_should_not_be_greater_than_100_characters()
    {
        $token = JWTAuth::fromUser($this->user);

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $title = str_pad('', 101, 'a');

        $response = $this->post(
            '/api/articles',
            array_merge($this->validData(), ['title' => $title]),
            $headers
        );

        $response->assertSessionHasErrors('title');
        $this->assertCount(0, Article::all());
    }

    /** @test */
    public function an_unauthenticated_user_cannot_add_an_article()
    {
        $response = $this->post('/api/articles', $this->validData());

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function an_authenticated_user_can_add_an_article()
    {
        $token = JWTAuth::fromUser($this->user);

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $response = $this->post('/api/articles', $this->validData(), $headers);

        $article = Article::first();

        $this->assertEquals('Test Title', $article->title);
        $this->assertEquals('Lorem ipsum dolor, sit amet consectetur adipisicing elit. Voluptatem voluptas libero fugit provident mollitia esse, atque harum, reiciendis, illo nesciunt nam incidunt officia sunt tempore omnis. Libero dolore provident sunt!', $article->description);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            'data' => [
                'id' => $article->id
            ],
            'links' => [
                'self' => $article->path()
            ]
        ]);
    }

    /** @test */
    /*
    * 2 users and one article for each
    * First user is the one made on setUp()
    */
    public function fetch_all_articles_of_authenticated_user()
    {   
        $anotherUser = factory(User::class)->create();

        $articleOfFirstUser = factory(Article::class)->create(['user_id' => $this->user->id]);
        $articleOfAnotherUser = factory(Article::class)->create(['user_id' => $anotherUser->id]);

        $tokenOfFirstUser = JWTAuth::fromUser($this->user);
        $tokenOfAnotherUser = JWTAuth::fromUser($anotherUser);

        $headers = [
            'Authorization' => 'Bearer ' . $tokenOfFirstUser
        ];

        $response = $this->get('/api/articles', $headers);

        /* 
        * Asserts that there is a JSON returned
        * Change the token respective to the user you want to test 
        * Ex: 'Bearer ' . $tokenOfFirstUser -> 'Bearer ' . $tokenOfAnotherUser
        * Change the article on the JSON assertion respective to which user you want to test 
        */
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(1);
        $response->assertJson([
            'data' => [
                [
                    'data' => [
                        'id' => $articleOfFirstUser->id
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function an_unauthenticated_user_cannot_retrieve_an_article()
    {
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $response = $this->get('/api/articles/' .  $article->id);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function an_article_can_be_retrieved()
    {
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $token = JWTAuth::fromUser($this->user);        

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $response = $this->get('/api/articles/' . $article->id, $headers);

        $response->assertJson([
            'data' => [
                'id' => $article->id,
                'title' => $article->title,
                'description' => $article->description,
                'created_at' => $article->created_at->format('M d, Y'),
                'last_updated_at' => $article->updated_at->diffForHumans(),
            ],
            'links' => [
                'self' => $article->path()
            ]
        ]);
    }

    /** @test */
    public function an_article_can_be_retrieved_only_by_its_user()
    {
        // First user is the one made on setUp()
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $anotherUser = factory(User::class)->create();

        $tokenOfAnotherUser = JWTAuth::fromUser($anotherUser);        

        $headers = [
            'Authorization' => 'Bearer ' . $tokenOfAnotherUser
        ];

        $response = $this->get('/api/articles/' . $article->id, $headers);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function an_unauthenticated_user_cannot_patch_an_article()
    {
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $response = $this->patch('/api/articles/' .  $article->id, $this->validData());

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function an_article_can_be_patched()
    {
        $this->withoutExceptionHandling();

        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $token = JWTAuth::fromUser($this->user);        

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $response = $this->patch('/api/articles/' . $article->id, $this->validData(), $headers);

        $article = $article->fresh();

        $this->assertEquals('Test Title', $article->title);
        $this->assertEquals('Lorem ipsum dolor, sit amet consectetur adipisicing elit. Voluptatem voluptas libero fugit provident mollitia esse, atque harum, reiciendis, illo nesciunt nam incidunt officia sunt tempore omnis. Libero dolore provident sunt!', $article->description);
        
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'id' => $article->id
            ],
            'links' => [
                'self' => $article->path()
            ]
        ]);
    }

    /** @test */
    public function an_article_can_only_be_patched_by_its_user()
    {
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $anotherUser = factory(User::class)->create();

        $tokenOfAnotherUser = JWTAuth::fromUser($anotherUser);        

        $headers = [
            'Authorization' => 'Bearer ' . $tokenOfAnotherUser
        ];

        $response = $this->patch('/api/articles/' . $article->id, $this->validData(), $headers);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function an_unauthenticated_user_cannot_delete_an_article()
    {
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $response = $this->delete('/api/articles/' .  $article->id);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function an_article_can_be_deleted()
    {
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $token = JWTAuth::fromUser($this->user);        

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $response = $this->delete('/api/articles/' . $article->id, [], $headers);

        $this->assertCount(0, Article::all());

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'success' => true
        ]);
    }

    /** @test */
    public function an_article_can_only_be_deleted_by_its_user()
    {
        $article = factory(Article::class)->create(['user_id' => $this->user->id]);

        $anotherUser = factory(User::class)->create();

        $tokenOfAnotherUser = JWTAuth::fromUser($anotherUser);        

        $headers = [
            'Authorization' => 'Bearer ' . $tokenOfAnotherUser
        ];

        $response = $this->delete('/api/articles/' . $article->id, [], $headers);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}

