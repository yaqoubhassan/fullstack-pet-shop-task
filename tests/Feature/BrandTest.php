<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Models\User;
use App\Models\Brand;

class BrandTest extends TestCase
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

        $this->user = User::factory()->create([
            'is_admin' => true
        ]);
        $this->jwtService = new JwtService();

        $this->token = $this->jwtService->generateToken($this->user);

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    public function testSuccessfulBrandCreation()
    {
        $title = $this->faker->word;

        $response = $this->json('POST', route('brand.create'), ['title' => $title], $this->headers);

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

        $this->assertDatabaseHas('brands', ['title' => $title]);
    }

    public function testMissingTitleValidation()
    {
        $response = $this->postJson(route('brand.create'), [], $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function testBrandAlreadyExists()
    {
        $title = $this->faker->word;

        Brand::create([
            'title' => $title,
            'uuid' => (string) Str::uuid(),
            'slug' => Str::slug($title)
        ]);

        $response = $this->postJson(route('brand.create'), [
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

        $this->assertCount(1, Brand::where('title', $title)->get());
    }

    public function testFetchFirstBrandsPageWithDefaultSettings()
    {
        Brand::factory()->count(20)->create();
        $response = $this->json('GET', route('brand.list'), [], $this->headers);

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

    public function testFetchBrandsSortedByNewest()
    {
        Brand::factory()->count(20)->create();
        $response = $this->json('GET', route('brand.list', ['sortBy' => 'newest']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $brands = Brand::orderBy('created_at', 'desc')->take(10)->get();
        $responseBrands = collect($response->json('data'));

        $this->assertEquals($brands->pluck('uuid'), $responseBrands->pluck('uuid'));
    }

    public function testFetchBrandsSortedByOldest()
    {
        Brand::factory()->count(20)->create();
        $response = $this->json('GET', route('brand.list', ['sortBy' => 'oldest']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $brands = Brand::orderBy('created_at', 'asc')->take(10)->get();
        $responseBrands = collect($response->json('data'));

        $this->assertEquals($brands->pluck('uuid'), $responseBrands->pluck('uuid'));
    }

    public function testFetchBrandsSortedByTitleDescending()
    {
        Brand::factory()->count(20)->create();
        $response = $this->json('GET', route('brand.list', ['desc' => 'true']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $brands = Brand::orderBy('title', 'desc')->take(10)->get();
        $responseBrands = collect($response->json('data'));

        $this->assertEquals($brands->pluck('uuid'), $responseBrands->pluck('uuid'));
    }

    public function testFetchBrandsSortedByTitleAscending()
    {
        Brand::factory()->count(20)->create();
        $response = $this->json('GET', route('brand.list', ['desc' => 'false']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['uuid', 'title', 'slug', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);

        $brands = Brand::orderBy('title', 'asc')->take(10)->get();
        $responseBrands = collect($response->json('data'));

        $this->assertEquals($brands->pluck('uuid'), $responseBrands->pluck('uuid'));
    }

    public function testSuccessfulBrandRetrieval()
    {
        $brand = Brand::factory()->create();
        $response = $this->json('GET', route('brand.show', $brand->uuid), [], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [
                    'uuid' => $brand->uuid,
                    'title' => $brand->title
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);
    }

    public function testBrandNotFound()
    {
        Brand::factory()->create();
        $response = $this->json('GET', route('brand.show', 11144555), [], $this->headers);

        $response->assertStatus(404)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'Brand not found',
                'errors' => [],
                'extra' => []
            ]);
    }

    public function testUpdateBrandSuccess()
    {
        $brand = Brand::factory()->create();

        $newTitle = 'Updated Brand Title';

        $response = $this->json('PUT', route('brand.update', $brand->uuid), [
            'title' => $newTitle
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [
                        'uuid' => $brand->uuid,
                        'title' => $newTitle,
                        'slug' => Str::slug($newTitle)
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);

        $this->assertDatabaseHas('brands', [
            'uuid' => $brand->uuid,
            'title' => $newTitle,
            'slug' => Str::slug($newTitle)
        ]);
    }

    public function testUpdateBrandNotFound()
    {
        $response = $this->putJson(route('brand.update', Str::uuid()), [
            'title' => 'New Title'
        ], $this->headers);

        $response->assertStatus(404)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'Brand not found',
                'errors' => [],
                'extra' => []
            ]);
    }

    public function testSuccessfullyDeleteBrand()
    {
        $brand = Brand::factory()->create();

        $response = $this->deleteJson(route('brand.delete', $brand->uuid), [], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);

        $this->assertDatabaseMissing('brands', [
            'uuid' => $brand->uuid
        ]);
    }

    public function testDeleteBrandNotFound()
    {
        $response = $this->deleteJson(route('brand.delete', Str::uuid()), [], $this->headers);

        $response->assertStatus(404)
         ->assertJson([
             'success' => 0,
             'data' => [],
             'error' => 'Brand not found',
             'errors' => [],
             'extra' => []
         ]);
    }
}
