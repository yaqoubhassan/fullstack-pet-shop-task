<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
     *                     @OA\Property(property="brand", type="string", format="uuid"),
     *                     @OA\Property(property="image", type="string", format="uuid")
     *
     *             )
     *           ),
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
