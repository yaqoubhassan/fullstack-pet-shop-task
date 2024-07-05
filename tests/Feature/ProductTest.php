<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Models\User;
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
}
