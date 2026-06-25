<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;

use App\Http\Requests\{
    StoreUserRequest,
    UpdateUserRequest
};
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of users.
     */
    public function getAllUsers(): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $users = $this->userService->getPaginated();
        return encryptResponse(200, 'success', 'Users retrieved', $users);
    }

    /**
     * Store a newly created user.
     */
    public function addUser(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());
        return encryptResponse(201, 'success', 'User created successfully', $user);
    }

    /**
     * Display the specified user.
     */
    public function getUserInfo(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = User::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'User not found');
        }

        $user = $this->userService->getUserById($id);
        return encryptResponse(200, 'success', 'User retrieved', $user);
    }

    /**
     * Update the specified user.
     */
    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        $id = User::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'User not found');
        }

        $user = clone $this->userService->getUserById($id); // fetch current user instance
        $updatedUser = $this->userService->updateUser($user, $request->validated());
        
        return encryptResponse(200, 'success', 'User updated successfully', $updatedUser);
    }

    /**
     * Toggle the active status of the user.
     */
    public function updateUserStatus(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = User::resolveHashedId($request->input('hash'));
        if (!$id) {
            // Check if user is modifying themselves
            return encryptResponse(404, 'error', 'User not found');
        }

        $user = $this->userService->getUserById($id);
        
        // Prevent an admin from deactivating themselves
        if ($user->id === auth('api')->id()) {
            return encryptResponse(403, 'error', 'Cannot toggle own active status');
        }

        $user = $this->userService->toggleActive($user);
        
        return encryptResponse(200, 'success', 'User active status toggled', $user);
    }

    /**
     * Remove the specified user from storage.
     */
    public function deleteUser(Request $request): JsonResponse
    {
        if (auth('api')->user()->role !== UserRole::ADMIN) {
            return encryptResponse(403, 'error', 'Unauthorized');
        }

        $id = User::resolveHashedId($request->input('hash'));
        if (!$id) {
            return encryptResponse(404, 'error', 'User not found');
        }

        if ($id === auth('api')->id()) {
            return encryptResponse(403, 'error', 'Cannot delete yourself');
        }

        $user = $this->userService->getUserById($id);
        $this->userService->deleteUser($user);

        return encryptResponse(200, 'success', 'User deleted successfully');
    }
}

