<?php

namespace App\Http\Controllers\Api;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PetController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/pets",
     *     operationId="getPetsList",
     *     tags={"Pets"},
     *     summary="Get list of pets",
     *     description="Returns list of pets with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="owner_id",
     *         in="query",
     *         description="Filter by owner ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="breed",
     *         in="query",
     *         description="Filter by breed",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pets retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pets retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Pet")
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
        $query = Pet::with('owner');

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->has('breed')) {
            $query->where('breed', 'like', '%' . $request->breed . '%');
        }

        $pets = $query->get();

        return $this->sendResponse($pets, 'Pets retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/pets",
     *     operationId="storePet",
     *     tags={"Pets"},
     *     summary="Create new pet",
     *     description="Create a new pet profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PetRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pet created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pet created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Pet")
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
     *             @OA\Property(property="message", type="string", example="User must have owner or both role to create pets")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
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
            'name' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'age' => 'required|integer|min:0|max:30',
            'weight' => 'required|numeric|min:0.1|max:200',
            'photo_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        // Verify owner has correct role
        $owner = User::find($request->owner_id);
        if (!in_array($owner->role, ['owner', 'both'])) {
            return $this->sendError('User must have owner or both role to create pets', [], 403);
        }

        $pet = Pet::create($request->all());
        $pet->load('owner');

        return $this->sendResponse($pet, 'Pet created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/pets/{id}",
     *     operationId="getPetById",
     *     tags={"Pets"},
     *     summary="Get pet information",
     *     description="Returns pet data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Pet id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pet retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pet retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Pet")
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
     *         description="Pet not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Pet not found")
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
        $pet = Pet::with('owner')->find($id);

        if (is_null($pet)) {
            return $this->sendError('Pet not found');
        }

        return $this->sendResponse($pet, 'Pet retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/pets/{id}",
     *     operationId="updatePet",
     *     tags={"Pets"},
     *     summary="Update existing pet",
     *     description="Update pet data",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Pet id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PetRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pet updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pet updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Pet")
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
     *         description="Pet not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Pet not found")
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
     *                     property="weight",
     *                     type="array",
     *                     @OA\Items(type="string", example="The weight must be between 0.1 and 200.")
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
        $pet = Pet::find($id);

        if (is_null($pet)) {
            return $this->sendError('Pet not found');
        }

        $validator = Validator::make($request->all(), [
            'owner_id' => 'sometimes|required|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'breed' => 'sometimes|required|string|max:255',
            'age' => 'sometimes|required|integer|min:0|max:30',
            'weight' => 'sometimes|required|numeric|min:0.1|max:200',
            'photo_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        $pet->update($request->all());
        $pet->load('owner');

        return $this->sendResponse($pet, 'Pet updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/pets/{id}",
     *     operationId="deletePet",
     *     tags={"Pets"},
     *     summary="Delete pet",
     *     description="Delete a pet",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Pet id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pet deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pet deleted successfully")
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
     *         description="Pet not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Pet not found")
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
        $pet = Pet::find($id);

        if (is_null($pet)) {
            return $this->sendError('Pet not found');
        }

        $pet->delete();

        return $this->sendResponse([], 'Pet deleted successfully');
    }
}

/**
 * @OA\Schema(
 *     schema="Pet",
 *     type="object",
 *     title="Pet",
 *     description="Pet model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Buddy"),
 *     @OA\Property(property="breed", type="string", example="Golden Retriever"),
 *     @OA\Property(property="age", type="integer", example=3),
 *     @OA\Property(property="weight", type="number", format="float", example=25.5),
 *     @OA\Property(property="photo_url", type="string", format="url", example="https://example.com/pet.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="owner",
 *         ref="#/components/schemas/User"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="PetRequest",
 *     type="object",
 *     title="Pet Request",
 *     description="Pet request model",
 *     required={"owner_id", "name", "breed", "age", "weight"},
 *     @OA\Property(property="owner_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Buddy"),
 *     @OA\Property(property="breed", type="string", example="Golden Retriever"),
 *     @OA\Property(property="age", type="integer", example=3),
 *     @OA\Property(property="weight", type="number", format="float", example=25.5),
 *     @OA\Property(property="photo_url", type="string", format="url", example="https://example.com/pet.jpg")
 * )
 */ 