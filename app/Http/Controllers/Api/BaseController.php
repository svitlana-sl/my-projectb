<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Pet Care Platform API",
 *     version="1.0.0",
 *     description="API documentation for Pet Care Platform - connecting pet owners with reliable sitters",
 *     @OA\Contact(
 *         email="admin@petcare.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class BaseController extends Controller
{
    /**
     * Success response method
     */
    public function sendResponse($result, $message = 'Success', $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * Error response method
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response method
     */
    public function sendValidationError($errorMessages, $message = 'Validation Error'): JsonResponse
    {
        return $this->sendError($message, $errorMessages, 422);
    }
} 