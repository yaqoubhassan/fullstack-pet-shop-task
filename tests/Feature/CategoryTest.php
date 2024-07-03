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

    public function testFetchFirstPageWithDefaultSettings()
    {
        Category::factory()->count(20)->create();
        $response = $this->json('GET', route('category.list'), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    public function testFetchCategoriesSortedByNewest()
    {
        Category::factory()->count(20)->create();
        $response = $this->json('GET', route('category.list', ['sortBy' => 'newest']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $categories = Category::orderBy('created_at', 'desc')->take(10)->get();
        $responseCategories = collect($response->json('data'));

        $this->assertEquals($categories->pluck('uuid'), $responseCategories->pluck('uuid'));
    }

    public function testFetchCategoriesSortedByOldest()
    {
        Category::factory()->count(20)->create();
        $response = $this->json('GET', route('category.list', ['sortBy' => 'oldest']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $categories = Category::orderBy('created_at', 'asc')->take(10)->get();
        $responseCategories = collect($response->json('data'));

        $this->assertEquals($categories->pluck('uuid'), $responseCategories->pluck('uuid'));
    }

    public function testFetchCategoriesSortedByTitleDescending()
    {
        Category::factory()->count(20)->create();
        $response = $this->json('GET', route('category.list', ['desc' => 'true']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $categories = Category::orderBy('title', 'desc')->take(10)->get();
        $responseCategories = collect($response->json('data'));

        $this->assertEquals($categories->pluck('uuid'), $responseCategories->pluck('uuid'));
    }

    public function testFetchCategoriesSortedByTitleAscending()
    {
        Category::factory()->count(20)->create();
        $response = $this->json('GET', route('category.list', ['desc' => 'false']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $categories = Category::orderBy('title', 'asc')->take(10)->get();
        $responseCategories = collect($response->json('data'));

        $this->assertEquals($categories->pluck('uuid'), $responseCategories->pluck('uuid'));
    }

    public function testSuccessfulCategoryRetrieval()
    {
        $category = Category::factory()->create();
        $response = $this->json('GET', route('category.show', $category->uuid), [], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [
                    'uuid' => $category->uuid,
                    'title' => $category->title
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);
    }

    public function testCategoryNotFound()
    {
        Category::factory()->create();
        $response = $this->json('GET', route('category.show', 11144555), [], $this->headers);

        $response->assertStatus(404)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'Category not found',
                'errors' => [],
                'extra' => []
            ]);
    }

    public function testUpdateCategorySuccess()
    {
        $category = Category::factory()->create();

        $newTitle = 'Updated Category Title';

        $response = $this->json('PUT', route('category.update', $category->uuid), [
            'title' => $newTitle
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [
                        'uuid' => $category->uuid,
                        'title' => $newTitle,
                        'slug' => Str::slug($newTitle)
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);

        $this->assertDatabaseHas('categories', [
            'uuid' => $category->uuid,
            'title' => $newTitle,
            'slug' => Str::slug($newTitle)
        ]);
    }

    public function testUpdateCategoryNotFound()
    {
        $response = $this->putJson(route('category.update', Str::uuid()), [
            'title' => 'New Title'
        ], $this->headers);

        $response->assertStatus(404)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'Category not found',
                'errors' => [],
                'extra' => []
            ]);
    }

    public function testSuccessfullyDeleteCategory()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson(route('category.delete', $category->uuid), [], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);

        $this->assertDatabaseMissing('categories', [
            'uuid' => $category->uuid
        ]);
    }

    public function testDeleteCategoryNotFound()
    {
        $response = $this->deleteJson(route('category.delete', Str::uuid()), [], $this->headers);

        $response->assertStatus(404)
         ->assertJson([
             'success' => 0,
             'data' => [],
             'error' => 'Category not found',
             'errors' => [],
             'extra' => []
         ]);
    }
}
