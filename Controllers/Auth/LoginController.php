<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /**
     * User login
     *
     * @method login
     * @param LoginRequest request
     *
     * @return Json
     */
    public function login(LoginRequest $request)
    {
        // Check if the user data is in Redis cache
        $user = Cache::get('user_' . $request->email);

        // If user not found in cache, fetch from DB and cache
        if (!$user) {
            $user = User::where('email', $request->email)->first();

            // If user doesn't exist in DB, return error
            if (!$user) {
                return Helper::errorResponse('User not found');
            }

            // Cache the user data for 60 minutes
            Cache::put('user_' . $request->email, $user, 60);
        }

        // Check the password validity
        if (Hash::check($request->password, $user->password)) {
            // Check if the token is cached for the user
            $token = Cache::get('token_' . $user->id);

            // If no token is cached, create a new one and store it in Redis
            if (!$token) {
                $token = $user->createToken('login_token')->plainTextToken;

                // Cache the token for 30 days
                $expirationTime = 60 * 24 * 30; // 30 days expiration time
                Cache::put('token_' . $user->id, $token, $expirationTime);

                // Assign the expiration time to the token in the database
                $user->tokens()->orderBy('created_at', 'desc')->first()->update(['expires_at' => now()->addMinutes($expirationTime)]);
            }

            // Return token, user details with the token set as a cookie
            return response()->json([
                'token' => $token,
                'user' => $user,
            ])->cookie('token', $token, $expirationTime);
        }

        return Helper::errorResponse('Invalid credentials');
    }
}
