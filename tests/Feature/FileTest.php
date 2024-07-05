<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\JwtService;
use App\Models\User;
use App\Models\File;

class FileTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

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

    public function testUploadFile()
    {
        Storage::fake('public');

        $response = $this->postJson(route('file.upload'), [
            'file' => UploadedFile::fake()->image('test.jpg')
        ], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'uuid',
                    'name',
                    'path',
                    'size',
                    'type',
                    'updated_at',
                    'created_at'
                ],
                'error',
                'errors',
                'extra'
            ]);

        $fileRecord = File::where('name', 'test')->first();

        Storage::disk('public')->assertExists($fileRecord->path);
    }

    public function testUploadFileValidationError()
    {
        $response = $this->postJson(route('file.upload'), [
            'file' => ''
        ], $this->headers);

        $response->assertStatus(422)
            ->assertJsonStructure([
                "message",
                "errors" => [
                    "file"
                ]
            ]);
    }

    // public function testDownloadFile()
    // {
    //     Storage::fake('public');

    //     $file = UploadedFile::fake()->image('test.jpg');
    //     $filePath = $file->store('pet-shop', 'public');
    //     $fileUuid = (string) Str::uuid();

    //     $fileRecord = File::create([
    //         'uuid' => $fileUuid,
    //         'name' => 'test',
    //         'path' => $filePath,
    //         'size' => $file->getSize(),
    //         'type' => $file->getMimeType(),
    //     ]);

    //     Storage::disk('public')->assertExists($filePath);

    //     $response = $this->json('GET', route('file.download', $fileUuid));

    //     $response->assertStatus(200);
    //     $response->assertHeader('Content-Disposition', 'attachment; filename=test.jpg');
    // }
}