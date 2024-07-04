<?php

namespace App\Http\Controllers\API;

use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Http\Resources\BrandResource;
use App\Http\Controllers\Controller;

class BrandController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/brands",
     *     tags={"Brands"},
     *     summary="List all brands",
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
        $filters = $request->validate([
            'sortBy' => ['nullable', 'string', Rule::in(['oldest', 'newest'])],
            'limit' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
            'desc' => ['nullable']
        ]);

        $limit = $filters['limit'] ?? 10;
        $page = $filters['page'] ?? 1;

        $brands = Brand::filterAndSort($filters)->paginate($limit, ['*'], 'page', $page);

        return BrandResource::collection($brands);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/brand/create",
     *     tags={"Brands"},
     *     summary="Create a new brand",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"title"},
     *               @OA\Property(property="title", type="string", description="Brand title", example="")
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

        $brand = Brand::firstOrCreate(
            ['title' => $request->input('title')],
            [
                'uuid' => (string) Str::uuid(),
                'slug' => Str::slug($request->input('title'))
            ]
        );

        return $this->successResponse(['uuid' => $brand->uuid], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/brand/{uuid}",
     *     tags={"Brands"},
     *     summary="Fetch a brand",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the brand"
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
        $brand = $this->getBrandByUuid($uuid);
        if (!$brand) {
            return $this->errorResponse("Brand not found", 404);
        }

        return $this->successResponse(new BrandResource($brand), 200);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/brand/{uuid}",
     *     tags={"Brands"},
     *     summary="Update an existing brand",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the brand"
     *     ),
     *     @OA\RequestBody(
     *       @OA\JsonContent(),
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               required={"title"},
     *               @OA\Property(property="title", type="string", description="Brand title", example="")
     *           ),
     *       ),
     *     ),
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(),),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="404", description="Page Not Found"),
     *     @OA\Response(response="422", description="Unprocessable Entity"),
     *     @OA\Response(response="500", description="Internal server error")
     * )
     */
    public function update(Request $request, string $uuid)
    {
        $request->validate(['title' => 'filled|string']);

        $brand = $this->getBrandByUuid($uuid);
        if (!$brand) {
            return $this->errorResponse("Brand not found", 404);
        }

        if ($request->input('title')) {
            $brand->update([
                'title' => $request->input('title'),
                'slug' => Str::slug($request->input('title'))
            ]);
        }

        return $this->successResponse(new BrandResource($brand->fresh()), 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/brand/{uuid}",
     *     tags={"Brands"},
     *     summary="Delete an existing brand",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         ),
     *         description="UUID of the brand"
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
        $brand = $this->getBrandByUuid($uuid);
        if (!$brand) {
            return $this->errorResponse("Brand not found", 404);
        }

        $brand->delete();
        return $this->successResponse([], 200);
    }

    private function getBrandByUuid(string $uuid)
    {
        return Brand::where('uuid', $uuid)->first();
    }

    private function successResponse($data, $status = 200)
    {
        return response()->json([
            'success' => 1,
            'data' => $data,
            'error' => null,
            'errors' => [],
            'extra' => []
        ], $status);
    }

    private function errorResponse($message, $status = 400)
    {
        return response()->json([
            'success' => 0,
            'data' => [],
            'error' => $message,
            'errors' => [],
            'extra' => []
        ], $status);
    }
}
