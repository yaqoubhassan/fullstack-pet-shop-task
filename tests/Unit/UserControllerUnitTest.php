<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\UserService;
use App\Services\JwtService;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Http\Controllers\API\UserController;

class UserControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    public function testHandleFileUpload()
    {
        Storage::fake('public');

        // Create a fake file
        $file = UploadedFile::fake()->image('avatar.jpg');


        // Create an instance of the UserController
        $service = new UserService(new JwtService(), new UserRepository());

        // Call the handleFileUpload method
        $fileUuid = $service->testHandleFileUpload($file);

        // Assert that the file was stored
        Storage::disk('public')->assertExists('avatars/' . $file->hashName());

        // Assert that a record was created in the files table
        $this->assertDatabaseHas('files', [
            'uuid' => $fileUuid,
            'name' => 'avatar.jpg',
            'path' => 'avatars/' . $file->hashName(),
        ]);
    }

    public function testGenerateToken()
    {
        $jwtService = new JwtService();
        $user = User::factory()->create();

        $token = $jwtService->generateToken($user);

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }
}
