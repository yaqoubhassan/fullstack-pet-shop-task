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

 */
abstract class Controller
{
    //
}
