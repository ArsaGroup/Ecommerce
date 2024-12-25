<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        try {
            // Retrieve cache expiration time from config
            $userCacheExpiration = config('register.user_cache_expiration');

            // Check if the user already exists in Redis cache
            $user = Cache::get('user_' . $request->email);

            if ($user) {
                Log::info('User found in cache', ['email' => $request->email]);
            } else {
                Log::info('User not found in cache, creating a new user', ['email' => $request->email]);

                // Create new user with the provided data
                $user = User::create([
                    "name"     => $request->name,
                    "email"    => $request->email,
                    "password" => Hash::make($request->password),
                    "usertype" => config('register.default_user_type'),  // Default user type from config
                ]);

                // Cache the user data for the configured expiration time
                Cache::put('user_' . $request->email, $user, $userCacheExpiration);
                Log::info('New user created and cached', ['email' => $request->email]);
            }

            // Retrieve token expiration time from config
            $tokenExpirationTime = config('register.token_expiration_days') * 24 * 60; // Convert days to minutes

            // Generate an auth token for the new user
            $token = $user->createToken("auth_token")->plainTextToken;
            Log::info('Auth token generated for user', ['email' => $request->email]);

            // Cache the token for the configured expiration time
            Cache::put('token_' . $user->id, $token, $tokenExpirationTime);
            Log::info('Auth token cached', ['user_id' => $user->id]);

            // Update token expiration date in the database (if needed)
            $user->tokens()->orderBy('created_at', 'desc')->first()->update(['expires_at' => now()->addMinutes($tokenExpirationTime)]);
            Log::info('Token expiration date updated in the database', ['user_id' => $user->id]);

            // Fresh the user model (get the latest data)
            $user = $user->fresh();

            // Return token, user details with the token set as a cookie
            Log::info('User registration successful', ['user_id' => $user->id]);
            return response()->json([
                'token' => $token,
                'user' => $user,
            ])->cookie('token', $token, $tokenExpirationTime);
        } catch (\Exception $e) {
            // Log the exception error
            Log::error('Error during user registration', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }
}
