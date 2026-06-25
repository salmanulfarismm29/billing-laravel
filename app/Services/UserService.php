<?php

namespace App\Services;

use App\Models\User;
use Illuminate\{
    Pagination\LengthAwarePaginator,
    Support\Facades\Hash,
    Support\Facades\DB
};

class UserService
{
    /**
     * Get paginated users with shops.
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('shops')->latest()->paginate($perPage);
    }

    /**
     * Create a new user and assign shops.
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $userData = collect($data)->except(['shop_ids'])->toArray();
            $userData['password'] = Hash::make($data['password']);
            
            // Set the primary shop to the first shop assigned
            if (!empty($data['shop_ids'])) {
                $userData['shop_id'] = $data['shop_ids'][0];
            }

            $user = User::create($userData);

            if (isset($data['shop_ids'])) {
                $user->shops()->attach($data['shop_ids']);
            }

            return $user->load('shops');
        });
    }

    /**
     * Get a user by ID.
     */
    public function getUserById(int $id): User
    {
        return User::with('shops')->findOrFail($id);
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $userData = collect($data)->except(['shop_ids', 'password'])->toArray();
            
            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if (isset($data['shop_ids']) && !empty($data['shop_ids'])) {
                $userData['shop_id'] = $data['shop_ids'][0];
            }

            $user->update($userData);

            if (isset($data['shop_ids'])) {
                $user->shops()->sync($data['shop_ids']);
            }

            return $user->fresh('shops');
        });
    }

    /**
     * Toggle the active status of a user.
     */
    public function toggleActive(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user;
    }

    /**
     * Soft delete a user.
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }
}
