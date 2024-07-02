<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Models\User;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $jwtService;
    protected $user;
    protected $token;
    protected $headers;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->jwtService = new JwtService();

        $this->token = $this->jwtService->generateToken($this->user);

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    public function testSuccessfulCategoryCreation()
    {
        $title = $this->faker->word;

        $response = $this->json('POST', route('category.create'), ['title' => $title], $this->headers);

        $response->assertStatus(201)
            ->assertJson([
                'success' => 1,
                'data' => [
                    'uuid' => true
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);

        $this->assertDatabaseHas('categories', ['title' => $title]);
    }

    public function testMissingTitleValidation()
    {
        $response = $this->postJson(route('category.create'), [], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function testCategoryAlreadyExists()
    {
        $title = $this->faker->word;

        Category::create([
           'title' => $title,
           'uuid' => (string) Str::uuid(),
           'slug' => Str::slug($title)
        ]);

        $response = $this->postJson(route('category.create'), [
           'title' => $title
        ], $this->headers);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => 1,
                    'data' => [
                        'uuid' => true
                    ],
                    'error' => null,
                    'errors' => [],
                    'extra' => []
                ]);

        $this->assertCount(1, Category::where('title', $title)->get());
    }
}
