<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        try {
            // Retrieve user cache expiration time from config
            $userCacheExpiration = config('login.user_cache_expiration');
            Log::info('Login attempt started', ['email' => $request->email]);

            // Check if the user data is in Redis cache
            $user = Cache::get('user_' . $request->email);

            if ($user) {
                Log::info('User found in cache', ['email' => $request->email]);
            } else {
                Log::info('User not found in cache, fetching from database', ['email' => $request->email]);

                // Fetch user from DB
                $user = User::where('email', $request->email)->first();

                // If user doesn't exist in DB, return error
                if (!$user) {
                    Log::warning('User not found in the database', ['email' => $request->email]);
                    return Helper::errorResponse('User not found');
                }

                // Cache the user data for the configured expiration time
                Cache::put('user_' . $request->email, $user, $userCacheExpiration);
                Log::info('User data cached for future use', ['email' => $request->email]);
            }

            // Check the password validity
            if (Hash::check($request->password, $user->password)) {
                Log::info('Password check successful', ['email' => $request->email]);

                // Check if the token is cached for the user
                $token = Cache::get('token_' . $user->id);

                if (!$token) {
                    Log::info('Token not found in cache, generating new token', ['user_id' => $user->id]);

                    // If no token is cached, create a new one and store it in Redis
                    $token = $user->createToken('login_token')->plainTextToken;

                    // Retrieve token expiration time from config
                    $tokenExpirationTime = config('login.token_expiration');

                    // Cache the token for the configured expiration time
                    Cache::put('token_' . $user->id, $token, $tokenExpirationTime);
                    Log::info('Auth token cached for user', ['user_id' => $user->id]);

                    // Assign the expiration time to the token in the database
                    $user->tokens()->orderBy('created_at', 'desc')->first()->update(['expires_at' => now()->addMinutes($tokenExpirationTime)]);
                    Log::info('Token expiration updated in database', ['user_id' => $user->id]);
                }

                // Return token, user details with the token set as a cookie
                Log::info('Login successful', ['user_id' => $user->id]);
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                ])->cookie('token', $token, $tokenExpirationTime);
            }

            Log::warning('Invalid credentials', ['email' => $request->email]);
            return Helper::errorResponse('Invalid credentials');
        } catch (\Exception $e) {
            // Log the exception error
            Log::error('Error during login process', ['error' => $e->getMessage(), 'email' => $request->email]);
            return Helper::errorResponse('An error occurred during login');
        }
    }
}
