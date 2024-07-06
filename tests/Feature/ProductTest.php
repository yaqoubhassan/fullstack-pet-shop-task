<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Models\User;
use App\Models\Product;
use App\Models\File;
use App\Models\Category;
use App\Models\Brand;

class ProductTest extends TestCase
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

    public function testItCanCreateAProduct()
    {
        $category = Category::factory()->create();
        $file = File::factory()->create();
        $brand = Brand::factory()->create();

        $requestData = [
            'category_uuid' => $category->uuid,
            'title' => 'Test Product',
            'price' => 100.50,
            'description' => 'Test description for product',
            'metadata' => [
                ['image' => $file->uuid, 'brand' => $brand->uuid]
            ]
        ];

        $response = $this->json('POST', route('product.create'), $requestData, $this->headers);

        $response->assertStatus(201)
            ->assertJson([
                'success' => 1,
                'data' => [
                    'uuid' => true,
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Test Product',
            'price' => 100.50
        ]);
    }

    public function testListAllProducts()
    {
        Product::factory()->count(10)->create();

        $response = $this->json('GET', route('product.list'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'category_uuid',
                        'title',
                        'price',
                        'description',
                        'metadata',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'category',
                        'brand'
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(10, 'data');
    }

    public function testFetchProductsSortedByNewest()
    {
        $products = Product::factory()->count(20)->create()->each(function ($product, $index) {
            $product->created_at = now()->subSeconds($index);
            $product->save();
        });
        $response = $this->json('GET', route('product.list', ['sortBy' => 'newest']));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'category_uuid',
                        'title',
                        'price',
                        'description',
                        'metadata',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'category',
                        'brand'
                    ]
                ],
                'links',
                'meta'
            ]);

        $products = Product::orderBy('created_at', 'desc')->take(10)->get();
        $responseProducts = collect($response->json('data'));

        $this->assertEquals($products->pluck('uuid')->toArray(), $responseProducts->pluck('uuid')->toArray());
    }

    public function testFetchProductsSortedByOldest()
    {
        $products = Product::factory()->count(20)->create()->each(function ($product, $index) {
            $product->created_at = now()->subSeconds($index);
            $product->save();
        });
        $response = $this->json('GET', route('product.list', ['sortBy' => 'oldest']));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'category_uuid',
                        'title',
                        'price',
                        'description',
                        'metadata',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'category',
                        'brand'
                    ]
                ],
                'links',
                'meta'
            ]);

        $products = Product::orderBy('created_at', 'asc')->take(10)->get();
        $responseProducts = collect($response->json('data'));

        $this->assertEquals($products->pluck('uuid')->toArray(), $responseProducts->pluck('uuid')->toArray());
    }

    public function testFetchProductsSortedByTitlteDescending()
    {
        $products = Product::factory()->count(20)->create()->each(function ($product, $index) {
            $product->created_at = now()->subSeconds($index);
            $product->save();
        });
        $response = $this->json('GET', route('product.list', ['desc' => 'true']));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'category_uuid',
                        'title',
                        'price',
                        'description',
                        'metadata',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'category',
                        'brand'
                    ]
                ],
                'links',
                'meta'
            ]);

        $products = Product::orderBy('title', 'desc')->take(10)->get();
        $responseProducts = collect($response->json('data'));

        $this->assertEquals($products->pluck('uuid'), $responseProducts->pluck('uuid'));
    }

    public function testFetchProductsSortedByTitleAscending()
    {
        $products = Product::factory()->count(20)->create()->each(function ($product, $index) {
            $product->created_at = now()->subSeconds($index);
            $product->save();
        });
        $response = $this->json('GET', route('product.list', ['desc' => 'false']));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'category_uuid',
                        'title',
                        'price',
                        'description',
                        'metadata',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'category',
                        'brand'
                    ]
                ],
                'links',
                'meta'
            ]);

        $products = Product::orderBy('title', 'asc')->take(10)->get();
        $responseProducts = collect($response->json('data'));

        $this->assertEquals($products->pluck('uuid'), $responseProducts->pluck('uuid'));
    }

    public function testSearchProductsByTitle()
    {
        $title = 'First Product';
        Product::factory()->create(['title' => $title]);
        Product::factory()->count(9)->create();

        $response = $this->json('GET', route('product.list', ['title' => $title]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'category_uuid',
                        'title',
                        'price',
                        'description',
                        'metadata',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'category',
                        'brand'
                    ]
                ],
                'links',
                'meta'
            ]);

        $responseProducts = collect($response->json('data'));
        $this->assertTrue($responseProducts->pluck('title')->contains($title));
    }

    public function testFetchProductsSortedByCategory()
    {
        $category = Category::factory()->create();
        Product::factory()->count(7)->create();
        Product::factory()->count(3)->create([
            'category_uuid' => $category->uuid
        ]);

        $response = $this->json('GET', route('product.list'), ['category' => $category->uuid]);
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testFetchProductsFilterByPrice()
    {
        Product::factory()->count(7)->create([
            'price' => $this->faker->randomFloat(2, 10, 100),
        ]);
        Product::factory()->count(3)->create([
            'price' => 1000
        ]);

        $response = $this->json('GET', route('product.list'), ['price' => 1000]);
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testFetchProductsFilterByBrand()
    {
        $brand = Brand::factory()->create();
        $file = File::factory()->create();
        Product::factory()->count(7)->create();
        Product::factory()->count(3)->create([
            'metadata' => [
                [
                    'brand' => $brand->uuid,
                    'image' => $file->uuid
                ]
            ]
        ]);

        $response = $this->json('GET', route('product.list'), ['brand' => $brand->uuid]);
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}
