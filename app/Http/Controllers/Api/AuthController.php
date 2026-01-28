<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Create Access Token (short lived, determined by config/sanctum.php 'expiration')
        // We give it 'access-api' ability
        $accessToken = $user->createToken('access_token', ['access-api']);

        // Create Refresh Token (long lived)
        // We give it 'issue-access-token' ability
        // We manually set a longer expiration for this token if needed, but Sanctum's createToken uses default expiration.
        // To have different expiration, we might need to update the token after creation or rely on a different config?
        // Actually custom expiration per token is supported in recent Sanctum versions via expiresAt argument in NewAccessToken
        // but createToken method signature is: createToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null)

        $refreshTokenExpiration = now()->addWeeks(2);
        $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], $refreshTokenExpiration);

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60, // seconds
        ]);
    }

    public function refresh(Request $request)
    {
        // The user should validly authenticate with the REFRESH TOKEN to hit this endpoint
        $user = $request->user();

        // Check if the current token used has the 'issue-access-token' ability
        if (! $user->currentAccessToken()->can('issue-access-token')) {
            return response()->json(['message' => 'Invalid token ability.'], 403);
        }

        // Issue new Access Token
        $accessToken = $user->createToken('access_token', ['access-api']);

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
