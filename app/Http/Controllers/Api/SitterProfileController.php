<?php

namespace App\Http\Controllers\Api;

use App\Models\SitterProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SitterProfileController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/sitter-profiles",
     *     operationId="getSitterProfilesList",
     *     tags={"Sitter Profiles"},
     *     summary="Get list of sitter profiles (public)",
     *     description="Returns list of sitter profiles with optional filtering - no authentication required",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="min_rate",
     *         in="query",
     *         description="Minimum hourly rate",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="max_rate",
     *         in="query",
     *         description="Maximum hourly rate",
     *         required=false,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitter profiles retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SitterProfile")
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
    public function index(Request $request): JsonResponse
    {
        $query = SitterProfile::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('min_rate')) {
            $query->where('default_hourly_rate', '>=', $request->min_rate);
        }

        if ($request->has('max_rate')) {
            $query->where('default_hourly_rate', '<=', $request->max_rate);
        }

        $sitterProfiles = $query->get();

        return $this->sendResponse($sitterProfiles, 'Sitter profiles retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/sitter-profiles",
     *     operationId="storeSitterProfile",
     *     tags={"Sitter Profiles"},
     *     summary="Create new sitter profile",
     *     description="Create a new sitter profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SitterProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sitter profile created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitter profile created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SitterProfile")
     *         )
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
     *         response=409,
     *         description="Conflict - Sitter profile already exists for this user"
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id|unique:sitter_profiles,user_id',
            'bio' => 'nullable|string|max:2000',
            'default_hourly_rate' => 'required|numeric|min:0|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Verify user has correct role
        $user = User::find($request->user_id);
        if (!in_array($user->role, ['sitter', 'both'])) {
            return $this->sendError('User must have sitter or both role to create sitter profile', [], 403);
        }

        $sitterProfile = SitterProfile::create($request->all());
        $sitterProfile->load('user');

        return $this->sendResponse($sitterProfile, 'Sitter profile created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/sitter-profiles/{id}",
     *     operationId="getSitterProfileById",
     *     tags={"Sitter Profiles"},
     *     summary="Get sitter profile information (public)",
     *     description="Returns sitter profile data - no authentication required",
     *     @OA\Parameter(
     *         name="id",
     *         description="Sitter profile id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitter profile retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SitterProfile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Sitter profile not found"
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
    public function show($id): JsonResponse
    {
        $sitterProfile = SitterProfile::with('user')->find($id);

        if (is_null($sitterProfile)) {
            return $this->sendError('Sitter profile not found');
        }

        return $this->sendResponse($sitterProfile, 'Sitter profile retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/sitter-profiles/{id}",
     *     operationId="updateSitterProfile",
     *     tags={"Sitter Profiles"},
     *     summary="Update existing sitter profile",
     *     description="Update sitter profile data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Sitter profile id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SitterProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sitter profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitter profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SitterProfile")
     *         )
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
     *         description="Not Found - Sitter profile not found"
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
    public function update(Request $request, $id): JsonResponse
    {
        $sitterProfile = SitterProfile::find($id);

        if (is_null($sitterProfile)) {
            return $this->sendError('Sitter profile not found');
        }

        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string|max:2000',
            'default_hourly_rate' => 'sometimes|required|numeric|min:0|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90', // Required in database, but optional for updates
            'longitude' => 'nullable|numeric|between:-180,180', // Required in database, but optional for updates
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $sitterProfile->update($request->all());
        $sitterProfile->load('user');

        return $this->sendResponse($sitterProfile, 'Sitter profile updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/sitter-profiles/{id}",
     *     operationId="deleteSitterProfile",
     *     tags={"Sitter Profiles"},
     *     summary="Delete sitter profile",
     *     description="Delete a sitter profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Sitter profile id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sitter profile deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitter profile deleted successfully")
     *         )
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
     *         description="Not Found - Sitter profile not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $sitterProfile = SitterProfile::find($id);

        if (is_null($sitterProfile)) {
            return $this->sendError('Sitter profile not found');
        }

        $sitterProfile->delete();

        return $this->sendResponse([], 'Sitter profile deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user_id}/sitter-profile",
     *     operationId="getSitterProfileByUserId",
     *     tags={"Sitter Profiles"},
     *     summary="Get sitter profile by user ID (public)",
     *     description="Returns sitter profile for a specific user - no authentication required",
     *     @OA\Parameter(
     *         name="user_id",
     *         description="User ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitter profile retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/SitterProfile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User is not a sitter"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - User or sitter profile not found"
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
    public function getByUserId($userId): JsonResponse
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return $this->sendError('User not found');
        }

        if (!in_array($user->role, ['sitter', 'both'])) {
            return $this->sendError('User is not a sitter', [], 403);
        }

        $sitterProfile = SitterProfile::with('user')->where('user_id', $userId)->first();

        if (is_null($sitterProfile)) {
            return $this->sendError('Sitter profile not found');
        }

        return $this->sendResponse($sitterProfile, 'Sitter profile retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/sitter-profiles/search",
     *     operationId="searchSitterProfiles",
     *     tags={"Sitter Profiles"},
     *     summary="Search sitter profiles by location (public)",
     *     description="Search for sitter profiles within a certain radius of given coordinates - no authentication required",
     *     @OA\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="Search center latitude",
     *         required=true,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="longitude",
     *         in="query",
     *         description="Search center longitude",
     *         required=true,
     *         @OA\Schema(type="number", format="float")
     *     ),
     *     @OA\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Search radius in kilometers (default: 10)",
     *         required=false,
     *         @OA\Schema(type="number", format="float", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitter profiles found successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SitterProfile")
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
    public function searchByLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // Default 10km radius

        // Using Haversine formula to calculate distance
        $sitterProfiles = SitterProfile::with('user')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("
                *,
                (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) AS distance
            ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->get();

        return $this->sendResponse($sitterProfiles, 'Sitter profiles found successfully');
    }
}

/**
 * @OA\Schema(
 *     schema="SitterProfile",
 *     type="object",
 *     title="Sitter Profile",
 *     description="Sitter Profile model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="bio", type="string", example="Experienced pet sitter with 5 years of experience. Love all animals!"),
 *     @OA\Property(property="default_hourly_rate", type="number", format="float", example=25.50),
 *     @OA\Property(property="latitude", type="number", format="float", example=52.3676),
 *     @OA\Property(property="longitude", type="number", format="float", example=4.9041),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="SitterProfileRequest",
 *     type="object",
 *     title="Sitter Profile Request",
 *     description="Sitter Profile request model",
 *     required={"user_id", "default_hourly_rate", "latitude", "longitude"},
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="bio", type="string", example="Experienced pet sitter with 5 years of experience. Love all animals!"),
 *     @OA\Property(property="default_hourly_rate", type="number", format="float", example=25.50),
 *     @OA\Property(property="latitude", type="number", format="float", example=52.3676),
 *     @OA\Property(property="longitude", type="number", format="float", example=4.9041)
 * )
 */ 