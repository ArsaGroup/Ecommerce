<?php


return [

    /*
    |---------------------------------------------------------------------------
    | Token Cache Expiration Time
    |---------------------------------------------------------------------------
    |
    | The number of minutes the token data should be cached in Redis. This
    | value is used to control how long the token data stays in the cache
    | before it's automatically expired or removed.
    |
    */

    'token_cache_expiration' => 60,  // Token cache expiration time (in minutes)

    /*
    |---------------------------------------------------------------------------
    | Optional Expiration Cleanup
    |---------------------------------------------------------------------------
    |
    | Enable or disable automatic cleanup of expired tokens from the database
    | when a user logs out. If true, expired tokens will be deleted from the
    | database during the logout process.
    |
    */

    'cleanup_expired_tokens' => true,  // Automatically delete expired tokens from DB

];
