<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Models\JwtToken;
use App\Models\File;

class UserTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    protected $jwtService;

    public function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService();
    }

    public function testAdminUserCreation()
    {
        Storage::fake('public');

        $data = [
            'first_name' => 'Admin',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'address' => '123 Main St',
            'phone_number' => '1234567890',
            'is_marketing' => 0,
            'is_admin' => 1,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $response = $this->postJson(route('admin.user.create'), $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'uuid',
                    'first_name',
                    'last_name',
                    'email',
                    'avatar',
                    'address',
                    'phone_number',
                    'is_marketing',
                    'created_at',
                    'updated_at',
                    'token'
                ],
                'error',
                'errors',
                'extra'
            ]);

        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertNotNull($user);

        $fileRecord = File::where('uuid', $user->avatar)->first();
        $this->assertNotNull($fileRecord);
        Storage::disk('public')->assertExists($fileRecord->path);

        $token = JwtToken::where('user_id', $user->id)->first();
        $this->assertNotNull($token);
    }

    public function testItHandlesDatabaseExceptionWhenCreatingAdminAccount()
    {
        $this->withoutExceptionHandling();

        $data = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
        ];

        $userRepositoryMock = Mockery::mock(UserRepository::class);
        $userRepositoryMock->shouldReceive('create')->andThrow(new \Exception('Database error'));
        $this->app->instance(UserRepository::class, $userRepositoryMock);

        $response = $this->postJson(route('admin.user.create'), $data);

        $response->assertStatus(500)
            ->assertJsonStructure([
                'error',
            ]);
    }
}
