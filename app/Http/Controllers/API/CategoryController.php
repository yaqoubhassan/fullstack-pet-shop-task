<?php

namespace App\Http\Controllers\API;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     tags={"Categories"},
     *     summary="List all categories",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="Sort by newest or oldest",
     *         required=false,
     *         @OA\Schema(type="string", enum={"newest", "oldest"})
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Sort by title in descending or ascending order",
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
        $request->validate([
            'sortBy' => ['nullable', 'string', Rule::in(['oldest', 'newest'])],
            'limit' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer']
        ]);
        $filters = $request->only(['sortBy', 'desc', 'limit', 'page']);

        $limit = $filters['limit'] ?? 10;
        $page = $filters['page'] ?? 1;

        // Apply filters and sorting
        $categories = Category::filterAndSort($filters)->paginate($limit, ['*'], 'page', $page);

        return CategoryResource::collection($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/category/create",
     *     tags={"Categories"},
     *     summary="Create a new category",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"title"},
     *               @OA\Property(property="title", type="string", description="Category title", example="")
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
    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string']);

        $category = Category::firstOrCreate(
            ['title' => $request->input('title')],
            [
                'uuid' => (string) Str::uuid(),
                'slug' => Str::slug($request->input('title'))
            ]
        );

        $response = [
            'success' => 1,
            'data' => [
                'uuid' => $category->uuid
            ],
            'error' => null,
            'errors' => [],
            'extra' => []
        ];

        return response()->json($response, 201);
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
