<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/sitters",
     *     operationId="getSittersList",
     *     tags={"Users"},
     *     summary="Get list of sitters (public)",
     *     description="Returns list of sitters with optional filtering - no authentication required",
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filter by city",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="service_type",
     *         in="query",
     *         description="Filter by service type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sitters retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
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
    public function getSitters(Request $request): JsonResponse
    {
        $query = User::whereIn('role', ['sitter', 'both']);

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Add service type filtering if needed
        if ($request->has('service_type')) {
            $query->whereHas('sitterServices.serviceType', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->service_type . '%');
            });
        }

        $sitters = $query->get();

        return $this->sendResponse($sitters, 'Sitters retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     summary="Get list of users",
     *     description="Returns list of users with optional filtering by role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter by user role",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"owner", "sitter", "both", "admin"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filter by city",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             )
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
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $users = $query->get();

        return $this->sendResponse($users, 'Users retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     operationId="storeUser",
     *     tags={"Users"},
     *     summary="Create new user",
     *     description="Create a new user account",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
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
     *         response=409,
     *         description="Conflict - User with this email already exists"
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,sitter,both,admin',
            'avatar_url' => 'nullable|url',
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);

        return $this->sendResponse($user, 'User created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     summary="Get user information",
     *     description="Returns user data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="User id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
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
     *         description="Not Found - User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found');
        }

        return $this->sendResponse($user, 'User retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     summary="Update existing user",
     *     description="Update user data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="User id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
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
     *         description="Not Found - User not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - User with this email already exists"
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
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found');
        }

        // Build validation rules array
        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:owner,sitter,both,admin',
            'avatar_url' => 'nullable|url',
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];

        // Add file validation if avatar_file is present
        if ($request->hasFile('avatar_file')) {
            $rules['avatar_file'] = $user->getFileValidationRules();
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $input = $request->all();
        
        // Handle password hashing
        if (isset($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }

        // Handle file upload if present
        if ($request->hasFile('avatar_file')) {
            try {
                // Delete old avatar files
                $user->deleteOldAvatar();

                // Upload new avatar using simplified method
                $file = $request->file('avatar_file');
                [$directory, $thumbWidth, $thumbHeight] = $user->getUploadConfig('avatar_path');
                
                $filePaths = $user->uploadFile(
                    $file,
                    $directory,
                    'avatar_path',
                    'avatar_thumb_path',
                    $thumbWidth,
                    $thumbHeight
                );

                // Add file paths to input for update
                $input = array_merge($input, $filePaths);
                
            } catch (\Exception $e) {
                return $this->sendError('File upload failed: ' . $e->getMessage(), [], 500);
            }
        }

        // Remove avatar_file from input as it's not a database field
        unset($input['avatar_file']);

        $user->update($input);

        return $this->sendResponse($user, 'User updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     summary="Delete user",
     *     description="Delete a user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="User id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
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
     *         description="Not Found - User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found');
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully');
    }


}

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"owner", "sitter", "both", "admin"}, example="owner"),
 *     @OA\Property(property="avatar_url", type="string", format="url", example="https://example.com/avatar.jpg"),
 *     @OA\Property(property="address_line", type="string", example="123 Main St"),
 *     @OA\Property(property="city", type="string", example="Amsterdam"),
 *     @OA\Property(property="postal_code", type="string", example="1000AB"),
 *     @OA\Property(property="country", type="string", example="Netherlands"),
 *     @OA\Property(property="latitude", type="number", format="float", example=52.3676),
 *     @OA\Property(property="longitude", type="number", format="float", example=4.9041),
 *     @OA\Property(property="is_admin", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="UserRequest",
 *     type="object",
 *     title="User Request",
 *     description="User request model",
 *     required={"name", "email", "password", "role"},
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123"),
 *     @OA\Property(property="role", type="string", enum={"owner", "sitter", "both", "admin"}, example="owner"),
 *     @OA\Property(property="avatar_url", type="string", format="url", example="https://example.com/avatar.jpg"),
 *     @OA\Property(property="address_line", type="string", example="123 Main St"),
 *     @OA\Property(property="city", type="string", example="Amsterdam"),
 *     @OA\Property(property="postal_code", type="string", example="1000AB"),
 *     @OA\Property(property="country", type="string", example="Netherlands"),
 *     @OA\Property(property="latitude", type="number", format="float", example=52.3676),
 *     @OA\Property(property="longitude", type="number", format="float", example=4.9041)
 * )
 */ 