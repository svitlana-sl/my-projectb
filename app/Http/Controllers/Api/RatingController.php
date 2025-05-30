<?php

namespace App\Http\Controllers\Api;

use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RatingController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/ratings",
     *     operationId="getRatingsList",
     *     tags={"Ratings"},
     *     summary="Get list of ratings (public)",
     *     description="Returns list of ratings with optional filtering - no authentication required",
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
     *         name="score",
     *         in="query",
     *         description="Filter by score",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ratings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Rating")
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
        $query = Rating::with(['owner', 'sitter']);

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->has('sitter_id')) {
            $query->where('sitter_id', $request->sitter_id);
        }

        if ($request->has('score')) {
            $query->where('score', $request->score);
        }

        $ratings = $query->orderBy('created_at', 'desc')->get();

        return $this->sendResponse($ratings, 'Ratings retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/ratings",
     *     operationId="storeRating",
     *     tags={"Ratings"},
     *     summary="Create new rating",
     *     description="Create a new rating for a sitter",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RatingRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rating created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rating")
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
     *         description="Conflict - Rating already exists for this owner-sitter combination"
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
            'owner_id' => 'required|exists:users,id',
            'sitter_id' => 'required|exists:users,id|different:owner_id',
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Verify owner has correct role
        $owner = User::find($request->owner_id);
        if (!in_array($owner->role, ['owner', 'both'])) {
            return $this->sendError('User must have owner or both role to create ratings', [], 403);
        }

        // Verify sitter has correct role
        $sitter = User::find($request->sitter_id);
        if (!in_array($sitter->role, ['sitter', 'both'])) {
            return $this->sendError('Target user must have sitter or both role', [], 403);
        }

        // Check if rating already exists
        $existingRating = Rating::where('owner_id', $request->owner_id)
                                ->where('sitter_id', $request->sitter_id)
                                ->first();

        if ($existingRating) {
            return $this->sendError('Rating already exists for this owner-sitter combination', [], 409);
        }

        $rating = Rating::create($request->all());
        $rating->load(['owner', 'sitter']);

        return $this->sendResponse($rating, 'Rating created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/ratings/{id}",
     *     operationId="getRatingById",
     *     tags={"Ratings"},
     *     summary="Get rating information (public)",
     *     description="Returns rating data - no authentication required",
     *     @OA\Parameter(
     *         name="id",
     *         description="Rating id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rating")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Invalid parameters provided"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found - Rating not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        $rating = Rating::with(['owner', 'sitter'])->find($id);

        if (is_null($rating)) {
            return $this->sendError('Rating not found');
        }

        return $this->sendResponse($rating, 'Rating retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/ratings/{id}",
     *     operationId="updateRating",
     *     tags={"Ratings"},
     *     summary="Update existing rating",
     *     description="Update rating data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Rating id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RatingRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Rating")
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
     *         description="Not Found - Rating not found"
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
        $rating = Rating::find($id);

        if (is_null($rating)) {
            return $this->sendError('Rating not found');
        }

        $validator = Validator::make($request->all(), [
            'score' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $rating->update($request->all());
        $rating->load(['owner', 'sitter']);

        return $this->sendResponse($rating, 'Rating updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/ratings/{id}",
     *     operationId="deleteRating",
     *     tags={"Ratings"},
     *     summary="Delete rating",
     *     description="Delete a rating",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Rating id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating deleted successfully")
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
     *         description="Not Found - Rating not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        $rating = Rating::find($id);

        if (is_null($rating)) {
            return $this->sendError('Rating not found');
        }

        $rating->delete();

        return $this->sendResponse([], 'Rating deleted successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/sitters/{sitter_id}/average-rating",
     *     operationId="getSitterAverageRating",
     *     tags={"Ratings"},
     *     summary="Get sitter's average rating (public)",
     *     description="Returns average rating for a specific sitter - no authentication required",
     *     @OA\Parameter(
     *         name="sitter_id",
     *         description="Sitter ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Average rating retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="sitter_id", type="integer", example=2),
     *                 @OA\Property(property="average_rating", type="number", format="float", example=4.5),
     *                 @OA\Property(property="total_ratings", type="integer", example=10)
     *             )
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
     *         description="Not Found - Sitter not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - An unexpected error occurred on the server"
     *     )
     * )
     */
    public function getSitterAverageRating($sitterId): JsonResponse
    {
        $sitter = User::find($sitterId);

        if (is_null($sitter)) {
            return $this->sendError('Sitter not found');
        }

        if (!in_array($sitter->role, ['sitter', 'both'])) {
            return $this->sendError('User is not a sitter', [], 403);
        }

        $ratings = Rating::where('sitter_id', $sitterId);
        $averageRating = $ratings->avg('score');
        $totalRatings = $ratings->count();

        $data = [
            'sitter_id' => (int) $sitterId,
            'average_rating' => $averageRating ? round($averageRating, 2) : null,
            'total_ratings' => $totalRatings
        ];

        return $this->sendResponse($data, 'Average rating retrieved successfully');
    }
}

/**
 * @OA\Schema(
 *     schema="Rating",
 *     type="object",
 *     title="Rating",
 *     description="Rating model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="sitter_id", type="integer", example=2),
 *     @OA\Property(property="score", type="integer", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="comment", type="string", example="Excellent service! My dog was very happy."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
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
 *     schema="RatingRequest",
 *     type="object",
 *     title="Rating Request",
 *     description="Rating request model",
 *     required={"owner_id", "sitter_id", "score"},
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="sitter_id", type="integer", example=2),
 *     @OA\Property(property="score", type="integer", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="comment", type="string", example="Excellent service! My dog was very happy.")
 * )
 */ 