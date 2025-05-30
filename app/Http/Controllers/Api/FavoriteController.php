<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FavoriteController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/favorites",
     *     operationId="getFavoritesList",
     *     tags={"Favorites"},
     *     summary="Get list of favorites",
     *     description="Returns list of favorites with optional filtering",
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
     *     @OA\Response(
     *         response=200,
     *         description="Favorites retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Favorites retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Favorite")
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
        $query = Favorite::with(['owner', 'sitter']);

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->has('sitter_id')) {
            $query->where('sitter_id', $request->sitter_id);
        }

        $favorites = $query->orderBy('created_at', 'desc')->get();

        return $this->sendResponse($favorites, 'Favorites retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/favorites",
     *     operationId="storeFavorite",
     *     tags={"Favorites"},
     *     summary="Add sitter to favorites",
     *     description="Add a sitter to owner's favorites list",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FavoriteRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Favorite added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Favorite added successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Favorite")
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
     *             @OA\Property(property="message", type="string", example="User must have owner or both role to add favorites")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict - Sitter already in favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Sitter is already in favorites")
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
     *                     property="sitter_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The sitter id and owner id must be different.")
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
            'sitter_id' => 'required|exists:users,id|different:owner_id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Verify owner has correct role
        $owner = User::find($request->owner_id);
        if (!in_array($owner->role, ['owner', 'both'])) {
            return $this->sendError('User must have owner or both role to add favorites', [], 403);
        }

        // Verify sitter has correct role
        $sitter = User::find($request->sitter_id);
        if (!in_array($sitter->role, ['sitter', 'both'])) {
            return $this->sendError('Target user must have sitter or both role', [], 403);
        }

        // Check if favorite already exists
        $existingFavorite = Favorite::where('owner_id', $request->owner_id)
                                   ->where('sitter_id', $request->sitter_id)
                                   ->first();

        if ($existingFavorite) {
            return $this->sendError('Sitter is already in favorites', [], 409);
        }

        $favorite = Favorite::create($request->all());
        $favorite->load(['owner', 'sitter']);

        return $this->sendResponse($favorite, 'Favorite added successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/favorites/{id}",
     *     operationId="getFavoriteById",
     *     tags={"Favorites"},
     *     summary="Get favorite information",
     *     description="Returns favorite data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Favorite id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Favorite retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Favorite")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Favorite not found"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $favorite = Favorite::with(['owner', 'sitter'])->find($id);

        if (is_null($favorite)) {
            return $this->sendError('Favorite not found');
        }

        return $this->sendResponse($favorite, 'Favorite retrieved successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/favorites/{id}",
     *     operationId="deleteFavorite",
     *     tags={"Favorites"},
     *     summary="Remove favorite",
     *     description="Remove a sitter from favorites",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Favorite id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Favorite removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Favorite removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Favorite not found"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $favorite = Favorite::find($id);

        if (is_null($favorite)) {
            return $this->sendError('Favorite not found');
        }

        $favorite->delete();

        return $this->sendResponse([], 'Favorite removed successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/favorites/remove",
     *     operationId="removeFavoriteByIds",
     *     tags={"Favorites"},
     *     summary="Remove favorite by owner and sitter IDs",
     *     description="Remove a sitter from favorites using owner_id and sitter_id",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="owner_id", type="integer", example=1),
     *             @OA\Property(property="sitter_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Favorite removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Favorite removed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Favorite not found"
     *     )
     * )
     */
    public function removeByIds(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'owner_id' => 'required|exists:users,id',
            'sitter_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $favorite = Favorite::where('owner_id', $request->owner_id)
                           ->where('sitter_id', $request->sitter_id)
                           ->first();

        if (is_null($favorite)) {
            return $this->sendError('Favorite not found');
        }

        $favorite->delete();

        return $this->sendResponse([], 'Favorite removed successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/owners/{owner_id}/favorites",
     *     operationId="getOwnerFavorites",
     *     tags={"Favorites"},
     *     summary="Get owner's favorite sitters",
     *     description="Returns list of favorite sitters for a specific owner",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner_id",
     *         description="Owner ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Owner favorites retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Favorite")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Owner not found"
     *     )
     * )
     */
    public function getOwnerFavorites($ownerId): JsonResponse
    {
        $owner = User::find($ownerId);

        if (is_null($owner)) {
            return $this->sendError('Owner not found');
        }

        if (!in_array($owner->role, ['owner', 'both'])) {
            return $this->sendError('User is not an owner', [], 403);
        }

        $favorites = Favorite::with(['owner', 'sitter'])
                            ->where('owner_id', $ownerId)
                            ->orderBy('created_at', 'desc')
                            ->get();

        return $this->sendResponse($favorites, 'Owner favorites retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/favorites/check",
     *     operationId="checkFavoriteStatus",
     *     tags={"Favorites"},
     *     summary="Check if sitter is in owner's favorites",
     *     description="Check if a specific sitter is in owner's favorites list",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         description="Owner ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sitter_id",
     *         in="query",
     *         description="Sitter ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Favorite status checked successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="is_favorite", type="boolean", example=true),
     *                 @OA\Property(property="favorite_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function checkFavoriteStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'owner_id' => 'required|exists:users,id',
            'sitter_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $favorite = Favorite::where('owner_id', $request->owner_id)
                           ->where('sitter_id', $request->sitter_id)
                           ->first();

        $data = [
            'is_favorite' => !is_null($favorite),
            'favorite_id' => $favorite ? $favorite->id : null
        ];

        return $this->sendResponse($data, 'Favorite status checked successfully');
    }
}

/**
 * @OA\Schema(
 *     schema="Favorite",
 *     type="object",
 *     title="Favorite",
 *     description="Favorite model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="sitter_id", type="integer", example=2),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="owner",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="sitter",
 *         ref="#/components/schemas/User"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="FavoriteRequest",
 *     type="object",
 *     title="Favorite Request",
 *     description="Favorite request model",
 *     required={"owner_id", "sitter_id"},
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="sitter_id", type="integer", example=2)
 * )
 */ 