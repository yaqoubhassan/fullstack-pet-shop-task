<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Requests\StoreUserRequest;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
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
            'is_marketing' => ['nullable', 'boolean'],
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
