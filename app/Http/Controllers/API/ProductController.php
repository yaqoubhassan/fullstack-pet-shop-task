<?php

namespace App\Http\Controllers\API;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    private function getProductByUuid(string $uuid)
    {
        return Product::where('uuid', $uuid)->first();
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
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="List all products",
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
     *         name="title",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="brand",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
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
            'category' => ['nullable', 'uuid', 'exists:categories,uuid'],
            'price' => ['nullable', 'numeric'],
            'brand' => ['nullable', 'uuid', 'exists:brands,uuid'],
            'title' => ['nullable', 'string']
        ]);

        $limit = $filters['limit'] ?? 10;
        $page = $filters['page'] ?? 1;

        $products = Product::filterAndSort($filters)->paginate($limit, ['*'], 'page', $page);

        return ProductResource::collection($products);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/product/create",
     *     tags={"Products"},
     *     summary="Create a new product",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"category_uuid", "title", "price", "description", "metadata"},
     *                  @OA\Property(property="category_uuid",
     *                  type="string", format="uuid", description="Category UUID"),
     *                  @OA\Property(property="title", type="string", description="Product title"),
     *                  @OA\Property(property="price", type="number", description="Product price"),
     *                  @OA\Property(property="description", type="string", description="Product description"),
     *                  @OA\Property(property="metadata", type="object",
     *                          @OA\Property(property="image", type="string", format="uuid"),
     *                          @OA\Property(property="brand", type="string", format="uuid")
     *                  )
     *              ),
     *         )
     *     ),
     *     @OA\Response(response="201", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(),),
     *     @OA\Response(response="404", description="Page Not Found", @OA\JsonContent(),),
     *     @OA\Response(response="422", description="Unprocessable Entity", @OA\JsonContent(),),
     *     @OA\Response(response="500", description="Internal server error", @OA\JsonContent(),)
     * )
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'category_uuid' => $request->input('category_uuid'),
            'title' => $request->input('title'),
            'price' => $request->input('price'),
            'description' => $request->input('description'),
            'metadata' => $request->input('metadata')
        ]);

        return response()->json([
            'success' => 1,
            'data' => [
                'uuid' => $product->uuid
            ],
            'error' => null,
            'errors' => [],
            'extra' => []
        ], 201);
    }

     /**
     * @OA\Get(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Fetch a product",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the product"
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
        $product = $this->getProductByUuid($uuid);

        if (!$product) {
            return $this->createErrorResponse('Product not found', 404);
        }

        return response()->json([
            'success' => 1,
            'data' => new ProductResource($product),
            'error' => null,
            'errors' => [],
            'extra' => []
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Update an existing product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the product"
     *     ),
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(property="category_uuid",
     *                  type="string",description="Category UUID", example=""),
     *                  @OA\Property(property="title", type="string", description="Product title", example=""),
     *                  @OA\Property(property="price", type="number", description="Product price", example=""),
     *                  @OA\Property(property="description", type="string", description="Product description", example=""),
     *                  @OA\Property(property="metadata", type="object",
     *                          @OA\Property(property="image", type="string", format="uuid", example=""),
     *                          @OA\Property(property="brand", type="string", format="uuid", example="")
     *                  )
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
        $product = $this->getProductByUuid($uuid);

        if (!$product) {
            return $this->createErrorResponse('Product not found', 404);
        }

        $request->validate([
            'category_uuid' => 'filled|exists:categories,uuid',
            'title' => 'filled|string|max:255',
            'price' => 'filled|numeric|min:0',
            'description' => 'filled|string',
            'metadata' => 'filled',
            'metadata.*.brand' => 'filled|string',
            'metadata.*.image' => 'filled|string',
        ]);

        $product->update($request->all());

        $response = [
            'success' => 1,
            'data' => new ProductResource($product->fresh()),
            'error' => null,
            'errors' => [],
            'extra' => []
        ];
        return response()->json($response, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/product/{uuid}",
     *     tags={"Products"},
     *     summary="Delete a Product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the delete"
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
        $product = $this->getProductByUuid($uuid);

        if (!$product) {
            return $this->createErrorResponse('Product not found', 404);
        }

        $product->delete();
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
