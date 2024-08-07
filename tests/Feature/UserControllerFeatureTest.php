<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Models\JwtToken;
use App\Models\File;

class UserControllerFeatureTest extends TestCase
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

    public function testAdminCannotLoginFromThisRoute()
    {
        $password = 'secret123';
        $user = User::factory()->create([
            'is_admin' => true
        ]);

        $payload = [
            'email' => $user->email,
            'password' => $password,
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
            'expires_at' => now()->addHour(),  // The token should no longer be valid
        ]);

        // $this->assertDatabaseHas('jwt_tokens', [
        //     'user_id' => $user->id,
        //     'expires_at' => now(),  // Token should be expired
        // ]);
    }

    public function testItReturnsTheAuthenticatedUserData()
    {
        $user = User::factory()->create();

        $token = $this->jwtService->generateToken($user);

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

    public function testAuthenticatedUserCanUpdateTheirAccountDetails()
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);

        $newData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'address' => $this->faker->address,
            'phone_number' => substr($this->faker->phoneNumber(), 0, 15),
            'is_marketing' => $this->faker->boolean,
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson(route('user.update'), $newData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => $newData['first_name'],
            'last_name' => $newData['last_name'],
            'email' => $newData['email'],
            'address' => $newData['address'],
            'phone_number' => $newData['phone_number'],
            'is_marketing' => $newData['is_marketing'],
        ]);
    }

    public function testDeleteUser()
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson(route('user.delete'));

        $response->assertStatus(200);

        $response->assertJson([
            'success' => true,
            'data' => [],
            'error' => null,
            'errors' => [],
            'extra' => []
        ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function testItHandlesDatabaseException()
    {
        $this->withoutExceptionHandling();

        $data = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => $this->faker->address,
            'phone_number' => substr($this->faker->phoneNumber, 0, 15),
        ];

        $userRepositoryMock = Mockery::mock(UserRepository::class);
        $userRepositoryMock->shouldReceive('create')->andThrow(new \Exception('Database error'));
        $this->app->instance(UserRepository::class, $userRepositoryMock);

        $response = $this->postJson(route('user.create'), $data);

        $response->assertStatus(500)
            ->assertJsonStructure([
                'error',
            ]);
    }

    public function testItReturnsAResetTokenForValidEmail()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('user.forgot-password'), [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => ['reset_token'],
            'error',
            'errors',
            'extra',
        ]);
        $this->assertEquals(1, $response->json('success'));
        $this->assertNull($response->json('error'));
    }

    public function testItReturnsAnErrorForNonExistentEmail()
    {
        $response = $this->postJson(route('user.forgot-password'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => 0,
            'data' => [],
            'error' => 'Invalid email',
            'errors' => [],
            'extra' => []
        ]);
    }

    public function testItResetsPasswordWithValidData()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = Password::createToken($user);

        $response = $this->postJson(route('password.reset'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => 1,
            'data' => ['message' => 'Password has been successfully updated'],
            'error' => null,
            'errors' => [],
            'extra' => []
        ]);

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function testItReturnsAnErrorForInvalidToken()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('password.reset'), [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => 0,
            'data' => [],
            'error' => 'This password reset token is invalid.',
            'errors' => [],
            'extra' => []
        ]);
    }
}
