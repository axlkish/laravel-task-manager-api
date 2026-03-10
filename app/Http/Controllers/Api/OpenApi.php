<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Task Manager API",
 *         version="1.0.0",
 *         description="REST API for task manager built with Laravel"
 *     ),
 *     @OA\Server(
 *         url="http://localhost",
 *         description="Local server"
 *     )
 * )
 */
class OpenApi
{
}
