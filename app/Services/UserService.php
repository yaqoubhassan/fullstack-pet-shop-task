<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use App\Services\JwtService;
use App\Models\File;
use App\Http\Resources\UserResource;

class UserService
{
    protected $jwtService;
    protected $userRepository;

    public function __construct(JwtService $jwtService, UserRepository $userRepository)
    {
        $this->jwtService = $jwtService;
        $this->userRepository = $userRepository;
    }

    public function createUser($data, $avatar = null, $isAdmin = false)
    {
        DB::beginTransaction();

        try {
            if ($avatar) {
                $data['avatar'] = $this->handleFileUpload($avatar);
            }

            $data['uuid'] = (string) Str::uuid();
            $data['password'] = Hash::make($data['password']);
            $data['is_admin'] = $isAdmin;

            $user = $this->userRepository->create($data);

            $token = $this->jwtService->generateToken($user);

            DB::commit();

            return [
                'success' => true,
                'data' => new UserResource($user->fresh(), $token),
                'error' => null,
                'errors' => [],
                'extra' => []
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
                'errors' => [],
                'extra' => []
            ];
        }
    }

    /**
     * Wrapper method to test handleFileUpload.
     */
    public function testHandleFileUpload($file)
    {
        return $this->handleFileUpload($file);
    }

    private function handleFileUpload($file)
    {
        $filePath = $file->store('avatars', 'public');
        $fileUuid = (string) Str::uuid();

        $fileRecord = File::create([
            'uuid' => $fileUuid,
            'name' => $file->getClientOriginalName(),
            'path' => $filePath,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
        ]);

        return $fileRecord->uuid;
    }
}
