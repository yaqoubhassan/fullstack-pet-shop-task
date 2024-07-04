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
    protected $user;
    protected $token;
    protected $headers;

    public function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService();
        $this->user = User::factory()->create([
            'is_admin' => true
        ]);

        $this->token = $this->jwtService->generateToken($this->user);

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
        ];
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
            'phone_number' => substr($this->faker->phoneNumber, 0, 15)
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

    public function testAdminCanListAllUsers()
    {
        User::factory()->count(20)->create();
        $response = $this->json('GET', route('admin.user.list'), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function testOnlyUserWithAdminAccountCanListAllUsers()
    {
        $admin = User::factory()->create([
            'is_admin' => false
        ]);

        User::factory()->count(20)->create();

        $token = $this->jwtService->generateToken($admin);

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];
        $response = $this->json('GET', route('admin.user.list'), [], $headers);

        $response->assertStatus(422)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'Unauthorized: Not enough privileges',
                'errors' => [],
                'trace' => []
            ]);
    }

    public function testCheckForUnauthenticatedUser()
    {
        User::factory()->count(20)->create();

        $response = $this->json('GET', route('admin.user.list'));

        $response->assertStatus(401)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'Unauthenticated',
                'errors' => [],
                'trace' => []
            ]);
    }

    public function testFetchUsersSortedByNewest()
    {
        $users = User::factory()->count(20)->create()->each(function ($user, $index) {
            $user->created_at = now()->subSeconds($index);
            $user->save();
        });
        $response = $this->json('GET', route('admin.user.list', ['sortBy' => 'newest']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $users = User::where('is_admin', false)->orderBy('created_at', 'desc')->take(10)->get();
        $responseUsers = collect($response->json('data'));

        $this->assertEquals($users->pluck('uuid')->toArray(), $responseUsers->pluck('uuid')->toArray());
    }

    public function testFetchUsersSortedByOldest()
    {
        $users = User::factory()->count(20)->create()->each(function ($user, $index) {
            $user->created_at = now()->subSeconds($index);
            $user->save();
        });
        $response = $this->json('GET', route('admin.user.list', ['sortBy' => 'oldest']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $users = User::where('is_admin', false)->orderBy('created_at', 'asc')->take(10)->get();
        $responseUsers = collect($response->json('data'));

        $this->assertEquals($users->pluck('uuid')->toArray(), $responseUsers->pluck('uuid')->toArray());
    }

    public function testFetchUsersSortedByNameDescending()
    {
        $users = User::factory()->count(20)->create()->each(function ($user, $index) {
            $user->created_at = now()->subSeconds($index);
            $user->save();
        });
        $response = $this->json('GET', route('admin.user.list', ['desc' => 'true']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $users = User::where('is_admin', false)->orderBy('first_name', 'desc')->take(10)->get();
        $responseUsers = collect($response->json('data'));

        $this->assertEquals($users->pluck('uuid'), $responseUsers->pluck('uuid'));
    }

    public function testFetchUsersSortedByNameAscending()
    {
        $users = User::factory()->count(20)->create()->each(function ($user, $index) {
            $user->created_at = now()->subSeconds($index);
            $user->save();
        });
        $response = $this->json('GET', route('admin.user.list', ['desc' => 'false']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $users = User::where('is_admin', false)->orderBy('first_name', 'asc')->take(10)->get();
        $responseUsers = collect($response->json('data'));

        $this->assertEquals($users->pluck('uuid'), $responseUsers->pluck('uuid'));
    }

    public function testFetchUsersFilteredByCreatedAt()
    {
        User::factory()->count(10)->create()->each(function ($user, $index) {
            $user->created_at = now()->subDays($index);
            $user->save();
        });

        $filterDate = now()->subDays(5)->format('Y-m-d');
        $response = $this->json('GET', route('admin.user.list', ['created_at' => $filterDate]), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $users = User::where('is_admin', false)->whereDate('created_at', $filterDate)->get();
        $responseUsers = collect($response->json('data'));

        $this->assertCount($users->count(), $responseUsers);

        $this->assertSame($users->pluck('uuid')->toArray(), $responseUsers->pluck('uuid')->toArray());

        $users->each(function ($user) use ($response) {
            $response->assertJsonFragment(['uuid' => $user->uuid]);
        });
    }

    public function testFetchUsersFilteredByIsMarketing()
    {
        User::factory()->count(5)->create(['is_marketing' => true]);

        User::factory()->count(5)->create(['is_marketing' => false]);

        $response = $this->json('GET', route('admin.user.list', ['is_marketing' => 'true']), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $users = User::where([['is_admin', false], ['is_marketing', true]])->get();
        $responseUsers = collect($response->json('data'));

        $this->assertCount($users->count(), $responseUsers);
        $this->assertSame($users->pluck('uuid')->toArray(), $responseUsers->pluck('uuid')->toArray());

        $users->each(function ($user) use ($response) {
            $response->assertJsonFragment(['uuid' => $user->uuid]);
        });
    }

    public function testFetchUsersFilteredByFirstName()
    {
        $firstName = 'John';
        User::factory()->create(['first_name' => $firstName]);
        User::factory()->count(9)->create();

        $response = $this->json('GET', route('admin.user.list', ['first_name' => $firstName]), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $responseUsers = collect($response->json('data'));
        $this->assertTrue($responseUsers->pluck('first_name')->contains($firstName));
    }

    public function testFetchUsersFilteredByEmail()
    {
        $email = 'test@email.com';
        User::factory()->create(['email' => $email]);
        User::factory()->count(9)->create();

        $response = $this->json('GET', route('admin.user.list', ['email' => $email]), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $responseUsers = collect($response->json('data'));
        $this->assertTrue($responseUsers->pluck('email')->contains($email));
    }

    public function testFetchUsersFilteredByPhoneNumber()
    {
        $phoneNumber = '0244004455';
        User::factory()->create(['phone_number' => $phoneNumber]);
        User::factory()->count(9)->create();

        $response = $this->json('GET', route('admin.user.list', ['phone_number' => $phoneNumber]), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $responseUsers = collect($response->json('data'));
        $this->assertTrue($responseUsers->pluck('phone_number')->contains($phoneNumber));
    }

    public function testFetchUsersFilteredByAddress()
    {
        $address = 'Alajo, Accra';
        User::factory()->create(['address' => $address]);
        User::factory()->count(9)->create();

        $response = $this->json('GET', route('admin.user.list', ['address' => $address]), [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uuid',
                        'first_name',
                        'last_name',
                        'email',
                        'avatar',
                        'address',
                        'phone_number',
                        'is_marketing',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);

        $responseUsers = collect($response->json('data'));
        $this->assertTrue($responseUsers->pluck('address')->contains($address));
    }

    public function testFetchUserByUuid()
    {
        $user = User::factory()->create();
        $response = $this->json('GET', route('admin.user.show', $user->uuid), [], $this->headers);

        $response->assertStatus(200)
            ->assertJson([
                'success' => 1,
                'data' => [
                    'uuid' => $user->uuid,
                    'first_name' => $user->first_name
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ]);
    }

    public function testReturnErrorMessageWhenFetchingUserWithInvalidUuid()
    {
        $response = $this->json('GET', route('admin.user.show', 'invalid-uuid'), [], $this->headers);

        $response->assertStatus(404)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'User not found',
                'errors' => [],
                'trace' => []
            ]);
    }

    public function testUpdateUserByUuid()
    {
        $user = User::factory()->create();

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

        $response = $this->json('PUT', route('admin.user.update', $user->uuid), $newData, $this->headers);

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

    public function testReturnErrorMessageWhenUpdatingUserWithInvalidUuid()
    {
        $response = $this->json('PUT', route('admin.user.update', 'invalid-uuid'), [], $this->headers);

        $response->assertStatus(404)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'User not found',
                'errors' => [],
                'trace' => []
            ]);
    }

    public function testAdminAccountCannotBeUpdated()
    {
        $user = User::factory()->create([
            'is_admin' => true
        ]);

        $newData = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        $response = $this->json('PUT', route('admin.user.update', $user->uuid), $newData, $this->headers);

        $response->assertStatus(401)
            ->assertJson([
                'success' => 0,
                'data' => [],
                'error' => 'Unauthorized',
                'errors' => [],
                'trace' => []
            ]);
    }
}
