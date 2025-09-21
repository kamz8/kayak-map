<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\User\CreateUserRequest;
use App\Http\Requests\Dashboard\User\UpdateUserRequest;
use App\Http\Resources\Dashboard\UserResource;
use App\Models\User;
use App\Services\Dashboard\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Dashboard - Users",
 *     description="Zarządzanie użytkownikami w panelu administracyjnym"
 * )
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {
        // Middleware are handled at route level
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/users",
     *     tags={"Dashboard - Users"},
     *     summary="Lista użytkowników",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista użytkowników pobrana pomyślnie"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = $this->userService->getUsersList($request->all());
        return UserResource::collection($users);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/dashboard/users/{user}",
     *     tags={"Dashboard - Users"},
     *     summary="Szczegóły użytkownika",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Szczegóły użytkownika pobrane pomyślnie"
     *     )
     * )
     */
    public function show(User $user): UserResource
    {
        $userWithDetails = $this->userService->getUserWithDetails($user);
        return new UserResource($userWithDetails);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/dashboard/users",
     *     tags={"Dashboard - Users"},
     *     summary="Utwórz nowego użytkownika",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Użytkownik utworzony pomyślnie"
     *     )
     * )
     */
    public function store(CreateUserRequest $request): UserResource
    {
        $user = $this->userService->createUser($request->validated(), $request->user());
        return new UserResource($user);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/dashboard/users/{user}",
     *     tags={"Dashboard - Users"},
     *     summary="Aktualizuj użytkownika",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Użytkownik zaktualizowany pomyślnie"
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $updatedUser = $this->userService->updateUser($user, $request->validated(), $request->user());
        return new UserResource($updatedUser);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/dashboard/users/{user}",
     *     tags={"Dashboard - Users"},
     *     summary="Usuń użytkownika",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Użytkownik usunięty pomyślnie"
     *     )
     * )
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user, request()->user());
        return response()->json(['message' => 'Użytkownik usunięty pomyślnie']);
    }
}