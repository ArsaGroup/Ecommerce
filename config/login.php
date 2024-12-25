<?php


return [

    /*
    |---------------------------------------------------------------------------
    | User Cache Expiration Time
    |---------------------------------------------------------------------------
    |
    | The number of minutes that the user data should be cached in Redis. This
    | value is used to control how long we store the user data in the cache.
    |
    */

    'user_cache_expiration' => 60,  // Cache expiration time for user data (in minutes)

    /*
    |---------------------------------------------------------------------------
    | Token Expiration Time
    |---------------------------------------------------------------------------
    |
    | The expiration time for the authentication token in minutes. This is used
    | to control how long the user's token remains valid.
    |
    */

    'token_expiration' => 60 * 24 * 30, // Token expiration time in minutes (30 days)

];
