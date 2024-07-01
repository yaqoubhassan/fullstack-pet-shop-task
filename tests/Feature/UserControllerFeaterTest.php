<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Models\User;
use App\Models\JwtToken;
use App\Models\File;

class UserControllerFeaterTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $jwtService;

    public function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService();
    }

    public function testUserCreationWithAvatar()
    {
        Storage::fake('public');

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'address' => '123 Main St',
            'phone_number' => '1234567890',
            'is_marketing' => 0,
            'is_admin' => 0,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ];

        $response = $this->postJson(route('user.create'), $data);

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

    public function testUserCreationWithoutAvatar()
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'address' => '456 Elm St',
            'phone_number' => '0987654321',
            'is_marketing' => 0,
            'is_admin' => 0,
        ];

        $response = $this->postJson(route('user.create'), $data);

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

        $user = User::where('email', 'jane.doe@example.com')->first();
        $this->assertNotNull($user);

        $token = JwtToken::where('user_id', $user->id)->first();
        $this->assertNotNull($token);
    }

    public function testUserCanLoginWithValidCredentials()
    {
        $password = 'secret123';
        $user = User::factory()->create();

        $payload = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->postJson(route('user.login'), $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['token'],
            'error',
            'errors',
            'extra',
        ]);
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        $user = User::factory()->create();

        $payload = [
            'email' => $user->email,
            'password' => 'wrong-password',
        ];

        $response = $this->postJson(route('user.login'), $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => 0,
            'data' => [],
            'error' => 'Failed to authenticate user',
            'errors' => [],
            'extra' => []
        ]);
    }

    public function testUserCanLogoutSuccessfully()
    {
        $user = User::factory()->create();

        $token = $this->jwtService->generateToken($user);

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = $this->json('GET', route('user.logout'), [], $headers);

        $response->assertStatus(200);

        // Assert token is invalidated in the database
        $this->assertDatabaseMissing('jwt_tokens', [
            'user_id' => $user->id,
            'expires_at' => Carbon::now()->addHour(),  // The token should no longer be valid
        ]);

        $this->assertDatabaseHas('jwt_tokens', [
            'user_id' => $user->id,
            'expires_at' => Carbon::now(),  // Token should be expired
        ]);
    }

    public function testItReturnsTheAuthenticatedUserData()
    {
        // Create a user
        $user = User::factory()->create();

        // Generate a JWT token for the user
        $token = $this->jwtService->generateToken($user);

        // Make the GET request with the JWT token in the Authorization header
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('user.index'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'error' => null,
                'errors' => [],
                'extra' => [],
            ]);
    }

    public function testUnAuthenticatedUserCannotViewTheirData()
    {
        User::factory()->create();

        $response = $this->getJson(route('user.index'));

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);
    }
}
