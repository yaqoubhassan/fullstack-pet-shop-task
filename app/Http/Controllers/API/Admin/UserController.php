<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Services\JwtService;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $userService;
    protected $jwtService;

    public function __construct(UserService $userService, JwtService $jwtService)
    {
        $this->userService = $userService;
        $this->jwtService = $jwtService;
    }

    private function getUserByUuid(string $uuid)
    {
        return User::where('uuid', $uuid)->first();
    }

    private function createErrorResponse(string $message, int $status)
    {
        return response()->json([
            'success' => 0,
            'data' => [],
            'error' => $message,
            'errors' => [],
            'trace' => []
        ], $status);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/user-listing",
     *     tags={"Admin"},
     *     summary="List all users",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"newest", "oldest"})
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="first_name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="phone_number",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", type="date")
     *     ),
     *     @OA\Parameter(
     *         name="is_marketing",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'sortBy' => ['nullable', 'string', Rule::in(['oldest', 'newest'])],
            'limit' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
            'desc' => ['nullable'],
            'first_name' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'created_at' => ['nullable', 'date_format:Y-m-d'],
            'is_marketing' => ['nullable'],
        ]);

        $limit = $filters['limit'] ?? 10;
        $page = $filters['page'] ?? 1;

        $users = User::filterAndSort($filters)->paginate($limit, ['*'], 'page', $page);

        return new UserCollection($users);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/create",
     *     tags={"Admin"},
     *     summary="Create an Admin Account",
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"first_name", "last_name", "email", "password",
     *               "password_confirmation", "phone_number", "address"},
     *               @OA\Property(property="first_name", type="string", description="User firstname", example="Admin"),
     *               @OA\Property(property="last_name", type="string", description="User lastname", example="Admin"),
     *               @OA\Property(property="email", type="string",
     *               format="email", description="User email", example="admin@email.com"),
     *               @OA\Property(property="password", type="string", example="secret123", description="User password"),
     *               @OA\Property(property="password_confirmation", example="secret123", description="User password"),
     *               @OA\Property(property="avatar", type="string", description="Avatar image UUID", example=""),
     *               @OA\Property(property="address", type="string",
     *               description="User main address", example="Accra, Ghana"),
     *               @OA\Property(property="phone_number", type="string",
     *               description="User main phone number", example="+233244112444"),
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
     * @OA\Get(
     *     path="/api/v1/admin/user/{uuid}",
     *     tags={"Admin"},
     *     summary="Fetch a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the user"
     *     ),
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function show(string $uuid)
    {
        $user = $this->getUserByUuid($uuid);

        if (!$user) {
            return $this->createErrorResponse('User not found', 404);
        }

        return response()->json([
            'success' => 1,
            'data' => new UserResource($user, null, true),
            'error' => null,
            'errors' => [],
            'extra' => []
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/user-edit/{uuid}",
     *     tags={"Admin"},
     *     summary="Edit a user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the user"
     *     ),
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
    public function update(Request $request, string $uuid)
    {
        $user = $this->getUserByUuid($uuid);

        if (!$user) {
            return $this->createErrorResponse('User not found', 404);
        }

        $request->validate([
            'first_name' => 'filled|string|max:255',
            'last_name' => 'filled|string|max:255',
            'email' => 'filled|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'filled|string|min:8|confirmed',
            'address' => 'filled|string',
            'phone_number' => 'filled|string|max:15',
            'is_marketing' => 'boolean',
            'is_admin' => 'boolean',
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png,bmp|max:2048',
        ]);

        if ($user->is_admin == true) {
            return response()->json([
                'success' => 0,
                'data' => [],
                'error' => 'Unauthorized',
                'errors' => [],
                'trace' => []
            ], 401);
        }
        $user->update($request->all());

        $response = [
            'success' => 1,
            'data' => new UserResource($user->fresh(), null, true),
            'error' => null,
            'errors' => [],
            'extra' => []
        ];
        return response()->json($response, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/user-delete/{uuid}",
     *     tags={"Admin"},
     *     summary="Delete a User Account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the user"
     *     ),
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function destroy(string $uuid)
    {
        $user = $this->getUserByUuid($uuid);

        if (!$user) {
            return $this->createErrorResponse('User not found', 404);
        }

        if ($user->is_admin == true) {
            return response()->json([
                'success' => 0,
                'data' => [],
                'error' => 'Unauthorized',
                'errors' => [],
                'trace' => []
            ], 401);
        }
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
     *     path="/api/v1/admin/login",
     *     tags={"Admin"},
     *     summary="Login an Admin Account",
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

            if ($user->is_admin == false) {
                return response()->json([
                    'success' => 0,
                    'data' => [],
                    'error' => 'Failed to authenticate user',
                    'errors' => [],
                    'extra' => []
                ], 422);
            }
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
     *     path="/api/v1/admin/logout",
     *     tags={"Admin"},
     *     summary="Logout an Admin Account",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="OK", @OA\JsonContent()),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent()),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent()),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent()),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent())
     * )
     */
    public function logout()
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
}
