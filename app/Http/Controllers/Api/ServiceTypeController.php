<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Service Types",
 *     description="API Endpoints for managing service types"
 * )
 */
class ServiceTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/public/service-types",
     *     summary="Get all service types (public)",
     *     tags={"Public"},
     *     description="Get all service types - no authentication required",
     *     @OA\Response(
     *         response=200,
     *         description="List of service types",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(ServiceType::all());
    }

    /**
     * @OA\Post(
     *     path="/api/service-types",
     *     summary="Create a new service type",
     *     tags={"Service Types"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Dog Walking"),
     *             @OA\Property(property="description", type="string", example="Professional dog walking services")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service type created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $serviceType = ServiceType::create($validated);
        return response()->json($serviceType, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/public/service-types/{id}",
     *     summary="Get a specific service type (public)",
     *     tags={"Public"},
     *     description="Get specific service type details - no authentication required",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service type details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service type not found"
     *     )
     * )
     */
    public function show(ServiceType $serviceType)
    {
        return response()->json($serviceType);
    }

    /**
     * @OA\Put(
     *     path="/api/service-types/{id}",
     *     summary="Update a service type",
     *     tags={"Service Types"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Dog Walking"),
     *             @OA\Property(property="description", type="string", example="Professional dog walking services")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service type updated successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service type not found"
     *     )
     * )
     */
    public function update(Request $request, ServiceType $serviceType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name,' . $serviceType->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $serviceType->update($validated);
        return response()->json($serviceType);
    }

    /**
     * @OA\Delete(
     *     path="/api/service-types/{id}",
     *     summary="Delete a service type",
     *     tags={"Service Types"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Service type ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Service type deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service type not found"
     *     )
     * )
     */
    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();
        return response()->json(null, 204);
    }
}
