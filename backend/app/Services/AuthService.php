<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    /**
     * Register a new user and generate access token.
     */
    public function register(array $data): array
    {
        // Generate a default username if not provided
        $username = $data['username'] ?? Str::slug($data['name']) . rand(100, 999);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // User model uses 'password' => 'hashed' cast
            'username' => $username,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];
    }

    /**
     * Authenticate user credentials and generate access token.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ];
    }

    /**
     * Revoke current access token for user.
     */
    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}