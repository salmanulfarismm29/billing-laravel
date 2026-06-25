<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthService
{
    /**
     * Attempt to log the user in.
     *
     * @param array $credentials
     * @return array|null Returns token array on success, null on failure.
     */
    public function login(array $credentials): array
    {
        $loginType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        $authCredentials = [
            $loginType => $credentials['login'],
            'password' => $credentials['password'],
        ];

        if (!$token = Auth::guard('api')->attempt($authCredentials)) {
            throw new HttpResponseException(
                encryptResponse(406, 'error', 'Invalid email, username and/or password.')
            );
        }

        /** @var User $user */
        $user = Auth::guard('api')->user();

        // Check if the user is active
        if (!$user->is_active) {
            Auth::guard('api')->logout();
            throw new HttpResponseException(
                encryptResponse(406, 'error', 'Inactive user please connect admin.')
            );
        }

        $user->load('shop');

        return [
            'id' => $user->hashed_id, // We'll return it strictly as hashed_id but map to 'id' or just return 'hashed_id' 
            'name' => $user->name,
            'email' => $user->email,
            'role_id' => $user->role->value,
            'role_label' => $user->role->label(),
            'shop_id' => $user->shop?->hashed_id,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ];
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): void
    {
        Auth::guard('api')->logout();
    }

    /**
     * Refresh a token.
     */
    public function refresh(): array
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Get the authenticated User.
     * 
     * @return User|null
     */
    public function getAuthenticatedUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::guard('api')->user();

        // If you want to load relations like shop, do it here
        if ($user) {
            $user->load('shop', 'shops');
        }

        return $user;
    }

    /**
     * Format the token response.
     */
    protected function respondWithToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ];
    }
}
