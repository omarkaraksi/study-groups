<?php

namespace App\Http\Controllers\Api\V1;

use App\Application\Users\AuthenticateApiUser;
use App\Application\Users\RegisterApiUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterApiUser $registerApiUser): JsonResponse
    {
        return $this->tokenResponse($registerApiUser->handle($request->validated()), 201);
    }

    public function login(LoginRequest $request, AuthenticateApiUser $authenticateApiUser): JsonResponse
    {
        $credentials = $request->validated();

        return $this->tokenResponse(
            $authenticateApiUser->handle($credentials['email'], $credentials['password']),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $accessToken = $request->user()->currentAccessToken();

        if ($accessToken instanceof PersonalAccessToken) {
            $accessToken->delete();
        }

        return response()->json(status: 204);
    }

    private function tokenResponse(NewAccessToken $accessToken, int $status = 200): JsonResponse
    {
        return response()->json([
            'token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'user' => (new UserResource($accessToken->accessToken->tokenable))->resolve(),
        ], $status);
    }
}
