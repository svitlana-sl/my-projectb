<?php

namespace App\Http\Controllers\Api;

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
 * 
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
 * 
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
 * 
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
 * 
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
 * 
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
 *     required={"user_id", "default_hourly_rate"},
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="bio", type="string", example="Experienced pet sitter with 5 years of experience. Love all animals!"),
 *     @OA\Property(property="default_hourly_rate", type="number", format="float", example=25.50),
 *     @OA\Property(property="latitude", type="number", format="float", example=52.3676),
 *     @OA\Property(property="longitude", type="number", format="float", example=4.9041)
 * )
 * 
 * @OA\Schema(
 *     schema="ServiceType",
 *     type="object",
 *     title="Service Type",
 *     description="Service Type model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Dog Walking"),
 *     @OA\Property(property="description", type="string", example="Professional dog walking service"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="ServiceTypeRequest",
 *     type="object",
 *     title="Service Type Request",
 *     description="Service Type request model",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="Dog Walking"),
 *     @OA\Property(property="description", type="string", example="Professional dog walking service")
 * )
 * 
 * @OA\Schema(
 *     schema="SitterService",
 *     type="object",
 *     title="Sitter Service",
 *     description="Sitter Service model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="sitter_id", type="integer", example=2),
 *     @OA\Property(property="service_type_id", type="integer", example=1),
 *     @OA\Property(property="hourly_rate", type="number", format="float", example=25.50),
 *     @OA\Property(property="description", type="string", example="I provide excellent dog walking services"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="sitter",
 *         ref="#/components/schemas/User"
 *     ),
 *     @OA\Property(
 *         property="serviceType",
 *         ref="#/components/schemas/ServiceType"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="SitterServiceRequest",
 *     type="object",
 *     title="Sitter Service Request",
 *     description="Sitter Service request model",
 *     required={"sitter_id", "service_type_id", "hourly_rate"},
 *     @OA\Property(property="sitter_id", type="integer", example=2),
 *     @OA\Property(property="service_type_id", type="integer", example=1),
 *     @OA\Property(property="hourly_rate", type="number", format="float", example=25.50),
 *     @OA\Property(property="description", type="string", example="I provide excellent dog walking services")
 * )
 */
class Schemas
{
    // This class is only used for Swagger schema definitions
} 