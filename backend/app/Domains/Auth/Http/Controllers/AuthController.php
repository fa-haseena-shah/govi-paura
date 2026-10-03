<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Domains\Auth\DTOs\LoginRequestData;
use App\Domains\Auth\DTOs\RegisterRequestData;
use App\Domains\Auth\Http\Requests\LoginRequest;
use App\Domains\Auth\Http\Requests\RegisterRequest;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = RegisterRequestData::fromArray($request->validated());
            $result = $this->authService->register($data);

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 201);
        } catch (InvalidArgumentException $e) {
            // check for any missing role-specific fields the FormRequest didn't already catch
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (RuntimeException $e) {
            // duplicate email
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $data = LoginRequestData::fromArray($request->validated());
            $result = $this->authService->login($data);

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (RuntimeException $e) {
            // account exists but not in active status
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 403);
        }
        // wrong email /password remains uncaught bc Laravel's default exception handler already
        // renders it correctly as 401 response
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    }
}