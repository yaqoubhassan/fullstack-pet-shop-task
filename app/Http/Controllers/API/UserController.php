<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\JwtService;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $jwtService;
    protected $userService;

    public function __construct(JwtService $jwtService, UserService $userService)
    {
        $this->jwtService = $jwtService;
        $this->userService = $userService;
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
            'data' => new UserResource(User::find(auth()->id()), null, true),
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
     *     @OA\Response(response="201", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(),),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent(),),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent(),),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent(),)
     * )
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $response = $this->userService->createUser($data, $request->file('avatar'));

        if ($response['success']) {
            return response()->json($response, 201);
        }

        return response()->json(['error' => $response['error']], 500);
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
     * @OA\Delete(
     *     path="/api/v1/user",
     *     tags={"User"},
     *     summary="Delete a User Account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="OK"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function destroy()
    {
        $user = User::find(auth()->id());
        $user->delete();
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
        $user = User::find(auth()->id());
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
     * @OA\Post(
     *     path="/api/v1/user/forgot-password",
     *     tags={"User"},
     *     summary="Creates a token to reset a user password",
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"email"},
     *               @OA\Property(property="email", type="string",
     *               format="email", description="User email", example=""),
     *           ),
     *       ),
     *     ),
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(),),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent(),),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent(),),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent(),)
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => 0,
                'data' => [],
                'error' => 'Invalid email',
                'errors' => [],
                'extra' => []
            ], 404);
        }

        $token = Password::createToken($user);

        return response()->json([
            'success' => 1,
            'data' => [
                'reset_token' => $token
            ],
            'error' => null,
            'errors' => [],
            'extra' => []
        ], 200);
    }

       /**
     * @OA\Post(
     *     path="/api/v1/user/reset-password",
     *     tags={"User"},
     *     summary="Reset user password with token generated from forgot-pawword request",
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"token", "email", "password", "password_confirmation"},
     *               @OA\Property(property="token", type="string", description="User reset token", example=""),
     *               @OA\Property(property="email", type="string",
     *               format="email", description="User email", example=""),
     *               @OA\Property(property="password", type="string", description="User password", example=""),
     *               @OA\Property(property="password_confirmation",
     *               type="string", description="User password", example=""),
     *           ),
     *       ),
     *     ),
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(),),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent(),),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent(),),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent(),)
     * )
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json([
                'success' => 1,
                'data' => [
                    'message' => 'Password has been successfully updated'
                ],
                'error' => null,
                'errors' => [],
                'extra' => []
            ], 200)
            : response()->json(['success' => 0,
                'data' => [],
                'error' => __($status),
                'errors' => [],
                'extra' => []], 422);
    }
}
