<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class LogoutController extends Controller
{
    /**
     * User logout method
     *
     * @logout
     * @param Request $request
     *
     * @return Json
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            // Check if user is authenticated
            if (!$user) {
                Log::warning('Logout attempt with no authenticated user');
                return response()->json([
                    "error" => "User not found!"
                ], 404);
            }

            Log::info('User logged out', ['user_id' => $user->id, 'email' => $user->email]);

            // Retrieve token cache expiration time from config
            $tokenCacheExpiration = config('logout.token_cache_expiration');
            $cacheTokenKey = 'token_' . $user->id;

            // Delete the token from the Redis cache
            if (Cache::has($cacheTokenKey)) {
                Log::info('Token found in cache, deleting token', ['user_id' => $user->id]);
                Cache::forget($cacheTokenKey); // Remove token from Redis
            } else {
                Log::info('No token found in cache for user', ['user_id' => $user->id]);
            }

            // Delete the user's current access token from the database
            if ($user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
                Log::info('Current access token deleted from database', ['user_id' => $user->id]);
            } else {
                Log::info('No current access token found for user', ['user_id' => $user->id]);
            }

            // Optionally, clean up expired tokens from the database
            if (config('logout.cleanup_expired_tokens')) {
                $expiredTokensCount = $user->tokens()->where('expires_at', '<', Carbon::now())->delete();
                Log::info('Expired tokens cleaned up', [
                    'user_id' => $user->id,
                    'expired_tokens_count' => $expiredTokensCount
                ]);
            }

            return Helper::successResponse("Logged out successfully!");
        } catch (\Exception $e) {
            // Log any exceptions that occur during the logout process
            Log::error('Error during logout process', [
                'error' => $e->getMessage(),
                'user_id' => $request->user() ? $request->user()->id : 'N/A'
            ]);
            return Helper::errorResponse('An error occurred during logout');
        }
    }
}
