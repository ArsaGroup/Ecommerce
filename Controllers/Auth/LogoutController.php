<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

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
        $user = $request->user();

        if (!$user) {
            return response()->json([
                "error" => "User not found!"
            ], 404);
        }

        // Delete the token from the Redis cache
        $cacheTokenKey = 'token_' . $user->id;
        if (Cache::has($cacheTokenKey)) {
            Cache::forget($cacheTokenKey); // Remove token from Redis
        }

        // Delete the user's current access token from the database
        $user->currentAccessToken()->delete();

        // Optionally, you can also delete expired tokens from the database
        // $user->tokens()->where('expires_at', '<', Carbon::now())->delete();

        return Helper::successResponse("Logged out successfully!");
    }
}
