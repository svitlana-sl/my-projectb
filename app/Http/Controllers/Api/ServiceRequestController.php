<?php

namespace App\Http\Controllers\Api;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/service-requests",
     *     operationId="getServiceRequestsList",
     *     tags={"Service Requests"},
     *     summary="Get list of service requests",
     *     description="Returns list of service requests with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         description="Filter by owner ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sitter_id",
     *         in="query",
     *         description="Filter by sitter ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "accepted", "rejected"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service requests retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service requests retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ServiceRequest")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = ServiceRequest::with(['owner', 'sitter', 'pet']);

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->has('sitter_id')) {
            $query->where('sitter_id', $request->sitter_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $serviceRequests = $query->orderBy('created_at', 'desc')->get();

        return $this->sendResponse($serviceRequests, 'Service requests retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/service-requests",
     *     operationId="storeServiceRequest",
     *     tags={"Service Requests"},
     *     summary="Create new service request",
     *     description="Create a new service request",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ServiceRequestRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service request created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service request created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ServiceRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Invalid data or business logic error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Pet must belong to the owner")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User role restrictions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User must have owner or both role to create service requests")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="owner_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The owner id field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'owner_id' => 'required|exists:users,id',
            'sitter_id' => 'required|exists:users,id',
            'pet_id' => 'required|exists:pets,id',
            'date_from' => 'required|date|after_or_equal:today',
            'date_to' => 'required|date|after:date_from',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Verify owner has correct role
        $owner = User::find($request->owner_id);
        if (!in_array($owner->role, ['owner', 'both'])) {
            return $this->sendError('User must have owner or both role to create service requests', [], 403);
        }

        // Verify sitter has correct role
        $sitter = User::find($request->sitter_id);
        if (!in_array($sitter->role, ['sitter', 'both'])) {
            return $this->sendError('Target user must have sitter or both role', [], 403);
        }

        // Verify pet belongs to owner
        $pet = \App\Models\Pet::find($request->pet_id);
        if ($pet->owner_id !== $request->owner_id) {
            return $this->sendError('Pet must belong to the owner', [], 400);
        }

        $serviceRequest = ServiceRequest::create($request->all());
        $serviceRequest->load(['owner', 'sitter', 'pet']);

        return $this->sendResponse($serviceRequest, 'Service request created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/service-requests/{id}",
     *     operationId="getServiceRequestById",
     *     tags={"Service Requests"},
     *     summary="Get service request information",
     *     description="Returns service request data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Service request id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service request retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service request retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ServiceRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service request not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Service request not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $serviceRequest = ServiceRequest::with(['owner', 'sitter', 'pet'])->find($id);

        if (is_null($serviceRequest)) {
            return $this->sendError('Service request not found');
        }

        return $this->sendResponse($serviceRequest, 'Service request retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/service-requests/{id}",
     *     operationId="updateServiceRequest",
     *     tags={"Service Requests"},
     *     summary="Update existing service request",
     *     description="Update service request data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Service request id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ServiceRequestRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service request updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service request updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ServiceRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service request not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Service request not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error - Invalid input data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="date_from",
     *                     type="array",
     *                     @OA\Items(type="string", example="The date from field must be a date after or equal to today.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $serviceRequest = ServiceRequest::find($id);

        if (is_null($serviceRequest)) {
            return $this->sendError('Service request not found');
        }

        $validator = Validator::make($request->all(), [
            'owner_id' => 'sometimes|required|exists:users,id',
            'sitter_id' => 'sometimes|required|exists:users,id',
            'pet_id' => 'sometimes|required|exists:pets,id',
            'date_from' => 'sometimes|required|date|after_or_equal:today',
            'date_to' => 'sometimes|required|date|after:date_from',
            'message' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:pending,accepted,rejected',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $serviceRequest->update($request->all());
        $serviceRequest->load(['owner', 'sitter', 'pet']);

        return $this->sendResponse($serviceRequest, 'Service request updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/service-requests/{id}",
     *     operationId="deleteServiceRequest",
     *     tags={"Service Requests"},
     *     summary="Delete service request",
     *     description="Delete a service request",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Service request id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service request deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service request deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service request not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Service request not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $serviceRequest = ServiceRequest::find($id);

        if (is_null($serviceRequest)) {
            return $this->sendError('Service request not found');
        }

        $serviceRequest->delete();

        return $this->sendResponse([], 'Service request deleted successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/service-requests/{id}/accept",
     *     operationId="acceptServiceRequest",
     *     tags={"Service Requests"},
     *     summary="Accept service request",
     *     description="Accept a service request (sitter action)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Service request id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service request accepted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service request accepted successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ServiceRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service request not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Service request not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function accept($id): JsonResponse
    {
        $serviceRequest = ServiceRequest::find($id);

        if (is_null($serviceRequest)) {
            return $this->sendError('Service request not found');
        }

        $serviceRequest->update(['status' => 'accepted']);
        $serviceRequest->load(['owner', 'sitter', 'pet']);

        return $this->sendResponse($serviceRequest, 'Service request accepted successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/service-requests/{id}/reject",
     *     operationId="rejectServiceRequest",
     *     tags={"Service Requests"},
     *     summary="Reject service request",
     *     description="Reject a service request (sitter action)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Service request id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service request rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service request rejected successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ServiceRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service request not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Service request not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function reject($id): JsonResponse
    {
        $serviceRequest = ServiceRequest::find($id);

        if (is_null($serviceRequest)) {
            return $this->sendError('Service request not found');
        }

        $serviceRequest->update(['status' => 'rejected']);
        $serviceRequest->load(['owner', 'sitter', 'pet']);

        return $this->sendResponse($serviceRequest, 'Service request rejected successfully');
    }
}

/**
 * @OA\Schema(
 *     schema="ServiceRequest",
 *     type="object",
 *     title="Service Request",
 *     description="Service Request model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="sitter_id", type="integer", example=2),
 *     @OA\Property(property="pet_id", type="integer", example=1),
 *     @OA\Property(property="date_from", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="date_to", type="string", format="date", example="2024-01-20"),
 *     @OA\Property(property="message", type="string", example="Please take care of my dog while I'm away"),
 *     @OA\Property(property="status", type="string", enum={"pending", "accepted", "rejected"}, example="pending"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="owner",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="sitter",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="pet",
 *         ref="#/components/schemas/Pet"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ServiceRequestRequest",
 *     type="object",
 *     title="Service Request Request",
 *     description="Service Request request model",
 *     required={"owner_id", "sitter_id", "pet_id", "date_from", "date_to"},
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="sitter_id", type="integer", example=2),
 *     @OA\Property(property="pet_id", type="integer", example=1),
 *     @OA\Property(property="date_from", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="date_to", type="string", format="date", example="2024-01-20"),
 *     @OA\Property(property="message", type="string", example="Please take care of my dog while I'm away")
 * )
 */ 