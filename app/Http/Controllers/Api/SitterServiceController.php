<?php

namespace App\Http\Controllers\Api;

use App\Models\SitterService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Sitter Services",
 *     description="API Endpoints for managing sitter services"
 * )
 */
class SitterServiceController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/sitter-services",
     *     operationId="getSitterServicesList",
     *     tags={"Sitter Services"},
     *     summary="Get all sitter services (public)",
     *     description="Returns list of sitter services with optional filtering - no authentication required",
     *     @OA\Parameter(
     *         name="service_type_id",
     *         in="query",
     *         description="Filter by service type ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of sitter services",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="hourly_rate", type="number", format="float"),
     *                 @OA\Property(property="service_type_id", type="integer"),
     *                 @OA\Property(property="sitter_id", type="integer"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="service_type",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - The requested resource was not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity - Validation failed for the input data"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = SitterService::with('serviceType');
        
        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }
        
        return response()->json($query->get());
    }

    /**
     * @OA\Post(
     *     path="/api/sitter-services",
     *     summary="Create a new sitter service",
     *     tags={"Sitter Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "hourly_rate", "service_type_id"},
     *             @OA\Property(property="name", type="string", example="Dog Walking Service"),
     *             @OA\Property(property="description", type="string", example="Professional dog walking services"),
     *             @OA\Property(property="hourly_rate", type="number", format="float", example=25.00),
     *             @OA\Property(property="service_type_id", type="integer", example=1),
     *             @OA\Property(property="sitter_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sitter service created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - You do not have access to this resource"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - The requested resource was not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity - Validation failed for the input data"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'hourly_rate' => 'required|numeric|min:0',
            'service_type_id' => 'required|exists:service_types,id',
            'sitter_id' => 'required|exists:users,id'
        ]);

        $service = SitterService::create($validated);
        return response()->json($service, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/sitter-services/{id}",
     *     summary="Get a specific sitter service (public)",
     *     tags={"Sitter Services"},
     *     description="Get specific sitter service details - no authentication required",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sitter service ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sitter service details"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Sitter service not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function show(SitterService $sitterService)
    {
        return response()->json($sitterService->load('serviceType'));
    }

    /**
     * @OA\Put(
     *     path="/api/sitter-services/{id}",
     *     summary="Update a sitter service",
     *     tags={"Sitter Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sitter service ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "hourly_rate", "service_type_id"},
     *             @OA\Property(property="name", type="string", example="Dog Walking Service"),
     *             @OA\Property(property="description", type="string", example="Professional dog walking services"),
     *             @OA\Property(property="hourly_rate", type="number", format="float", example=25.00),
     *             @OA\Property(property="service_type_id", type="integer", example=1),
     *             @OA\Property(property="sitter_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sitter service updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - You do not have access to this resource"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Sitter service not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity - Validation failed for the input data"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function update(Request $request, SitterService $sitterService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'hourly_rate' => 'required|numeric|min:0',
            'service_type_id' => 'required|exists:service_types,id',
            'sitter_id' => 'required|exists:users,id'
        ]);

        $sitterService->update($validated);
        return response()->json($sitterService);
    }

    /**
     * @OA\Delete(
     *     path="/api/sitter-services/{id}",
     *     summary="Delete a sitter service",
     *     tags={"Sitter Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Sitter service ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Sitter service deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - You do not have access to this resource"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Sitter service not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function destroy(SitterService $sitterService)
    {
        $sitterService->delete();
        return response()->json(null, 204);
    }
}
