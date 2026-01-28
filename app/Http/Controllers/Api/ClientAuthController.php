<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClientAuthController extends Controller
{
    /**
     * Handle Client Login (Client Credentials Grant).
     */
    public function login(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'client_secret' => 'required',
        ]);

        $client = ApiClient::where('client_id', $request->client_id)->first();

        if (! $client || ! $client->is_active || ! Hash::check($request->client_secret, $client->client_secret)) {
            throw ValidationException::withMessages([
                'client_id' => ['Invalid credentials.'],
            ]);
        }

        // Create Access Token (short lived)
        $accessToken = $client->createToken('access_token', ['external:read']);

        // Create Refresh Token (long lived)
        // We use a specific ability to identify it as a refresh token
        $refreshTokenExpiration = now()->addWeeks(2);
        $refreshToken = $client->createToken('refresh_token', ['issue-access-token'], $refreshTokenExpiration);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'access_token' => $accessToken->plainTextToken,
                'refresh_token' => $refreshToken->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60,
            ],
        ]);
    }

    /**
     * Handle Token Refresh.
     */
    public function refresh(Request $request)
    {
        // The client effectively authenticates with the Bearer Refresh Token
        $client = $request->user(); // This will return ApiClient instance because of the token association

        // Verify it is indeed an ApiClient
        if (! $client instanceof ApiClient) {
            return response()->json(['message' => 'Invalid token type.'], 401);
        }

        // Check if the token capability is correct
        if (! $client->currentAccessToken()->can('issue-access-token')) {
            return response()->json(['message' => 'Invalid token ability.'], 403);
        }

        // Rotate Refresh Token
        // Revoke the old refresh token (the one used for this request)
        $client->currentAccessToken()->delete();

        // Create NEW Access Token
        $newAccessToken = $client->createToken('access_token', ['external:read']);

        // Create NEW Refresh Token
        $refreshTokenExpiration = now()->addWeeks(2);
        $newRefreshToken = $client->createToken('refresh_token', ['issue-access-token'], $refreshTokenExpiration);

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $newAccessToken->plainTextToken,
                'refresh_token' => $newRefreshToken->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60,
            ],
        ]);
    }
}
