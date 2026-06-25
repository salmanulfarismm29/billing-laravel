<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return encryptResponse(200, 'success', 'Login Successfully.', $data);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return encryptResponse(200, 'success', 'Successfully logged out');
    }

    public function refresh(): JsonResponse
    {
        $tokenData = $this->authService->refresh();

        return encryptResponse(200, 'success', 'Token refreshed', $tokenData);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->getAuthenticatedUser();

        return encryptResponse(200, 'success', 'User details retrieved', $user);
    }
}
