<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *    title="Pet Shop API - Swagger Documentation",
 *    version="1.0.0",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *  @OA\Tag(
 *     name="Admin",
 *     description="Admin API endpoint"
 * )
 *  @OA\Tag(
 *     name="User",
 *     description="User API endpoint"
 * )
 *  @OA\Tag(
 *     name="Categories",
 *     description="Categories API endpoint"
 * )
 *
 */
abstract class Controller
{
    //
}
