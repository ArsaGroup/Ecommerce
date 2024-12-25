<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class RegisterController extends Controller
{
    /**
     * User registration
     *
     * @method register
     * @param RegisterRequest $request
     *
     * @return Json
     */
    public function register(RegisterRequest $request)
    {
        // Check if the user already exists in Redis cache
        $user = Cache::get('user_' . $request->email);

        // If user not found in cache, create a new user in DB and cache it
        if (!$user) {
            // Create new user with the provided data
            $user = User::create([
                "name"     => $request->name,
                "email"    => $request->email,
                "password" => Hash::make($request->password),
                "usertype" => 'user',  // Default value for usertype
            ]);

            // Cache the user data for 60 minutes (1 hour)
            Cache::put('user_' . $request->email, $user, 60);
        }

        // Generate an auth token for the new user
        $token = $user->createToken("auth_token")->plainTextToken;
        $expirationTime = 60 * 24 * 30; // 30 days expiration time

        // Cache the token for 30 days
        Cache::put('token_' . $user->id, $token, $expirationTime);

        // Update token expiration date in the database (if needed)
        $user->tokens()->orderBy('created_at', 'desc')->first()->update(['expires_at' => now()->addMinutes($expirationTime)]);

        // Fresh the user model (get the latest data)
        $user = $user->fresh();

        // Return token, user details with the token set as a cookie
        return response()->json([
            'token' => $token,
            'user' => $user,
        ])->cookie('token', $token, $expirationTime);
    }
}
