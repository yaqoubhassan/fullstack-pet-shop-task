<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\JwtService;
use App\Models\User;
use App\Models\File;
use App\Http\Resources\UserResource;
use App\Http\Requests\StoreUserRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     tags={"User"},
     *     summary="View a User Account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function index(Request $request)
    {
        $response = [
            'success' => 1,
            'data' => new UserResource($request->attributes->get('user')),
            'error' => null,
            'errors' => [],
            'extra' => []
        ];
        return response()->json($response, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/create",
     *     tags={"User"},
     *     summary="Create a User Account",
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"first_name", "last_name", "email", "password",
     *               "password_confirmation", "phone_number", "address"},
     *               @OA\Property(property="first_name", type="string", description="User firstname", example="Yakubu"),
     *               @OA\Property(property="last_name", type="string", description="User lastname", example="Alhassan"),
     *               @OA\Property(property="email", type="string",
     *               format="email", description="User email", example="yakubu@test.com"),
     *               @OA\Property(property="password", type="string", example="secret123", description="User password"),
     *               @OA\Property(property="password_confirmation", example="secret123", description="User password"),
     *               @OA\Property(property="avatar", type="string", description="Avatar image UUID", example=""),
     *               @OA\Property(property="address", type="string",
     *               description="User main address", example="Accra, Ghana"),
     *               @OA\Property(property="phone_number", type="string",
     *               description="User main phone number", example="+233244112288"),
     *               @OA\Property(property="is_marketing", type="string",
     *               description="User marketing preferences", example=""),
     *           ),
     *       ),
     *     ),
     *     @OA\Response(response="201", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->handleFileUpload($request->file('avatar'));
            }

            $data['uuid'] = (string) Str::uuid();
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);

            $token = $this->jwtService->generateToken($user);

            DB::commit();

            $response = [
                'success' => 1,
                'data' => new UserResource($user->fresh(), $token),
                'error' => null,
                'errors' => [],
                'extra' => []
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/user/edit",
     *     tags={"User"},
     *     summary="Update a User Account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(property="first_name", type="string", description="User firstname", example=""),
     *               @OA\Property(property="last_name", type="string", description="User lastname", example=""),
     *               @OA\Property(property="email", type="string",
     *               format="email", description="User email", example=""),
     *               @OA\Property(property="password", type="string", example="", description="User password"),
     *               @OA\Property(property="password_confirmation", example="", description="User password"),
     *               @OA\Property(property="avatar", type="string", description="Avatar image UUID", example=""),
     *               @OA\Property(property="address", type="string",
     *               description="User main address", example=""),
     *               @OA\Property(property="phone_number", type="string",
     *               description="User main phone number", example=""),
     *               @OA\Property(property="is_marketing", type="string",
     *               description="User marketing preferences", example=""),
     *           ),
     *       ),
     *     ),
     *     @OA\Response(response="201", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(),),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent(),),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent(),),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent(),)
     * )
     */
    public function update(UpdateUserRequest $request)
    {
        $user = User::find(auth()->id());
        $user->update($request->all());

        $response = [
                'success' => 1,
                'data' => new UserResource($user->fresh()),
                'error' => null,
                'errors' => [],
                'extra' => []
            ];
        return response()->json($response, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/login",
     *     tags={"User"},
     *     summary="Login a User Account",
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="string",
     *               format="email", description="User email", example=""),
     *               @OA\Property(property="password", type="string", example="", description="User password"),
     *           ),
     *       ),
     *     ),
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            $user = User::find(Auth::user()->id);
            $token = $this->jwtService->generateToken($user);

            $response = [
                'success' => 1,
                'data' => [
                    'token' => $token
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ];
            return response()->json($response, 200);
        }

        return response()->json([
            'success' => 0,
            'data' => [],
            'error' => 'Failed to authenticate user',
            'errors' => [],
            'extra' => []
        ], 422);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/logout",
     *     tags={"User"},
     *     summary="Logout a User Account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="OK", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent()),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent()),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent()),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent())
     * )
     */
    public function logout(Request $request)
    {
        $user = $request->attributes->get('user');
        $this->jwtService->invalidateToken($user);

        $response = [
            'success' => 1,
            'data' => [],
            'error' => null,
            'errors' => [],
            'extra' => []
        ];

        return response()->json($response, 200);
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
