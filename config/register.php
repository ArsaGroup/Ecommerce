<?php


return [

    /*
    |---------------------------------------------------------------------------
    | User Registration Cache Expiration
    |---------------------------------------------------------------------------
    |
    | Define how long to cache the user data in minutes. This controls how long
    | user data will be stored in the cache after registration.
    |
    */

    'user_cache_expiration' => 60,  // Cache expiration for user data (1 hour)

    /*
    |---------------------------------------------------------------------------
    | Token Expiration
    |---------------------------------------------------------------------------
    |
    | This setting defines the expiration time for the authentication token in
    | days. The default is 30 days, but you can adjust this based on your needs.
    |
    */

    'token_expiration_days' => 30, // Expiration time for the user token (in days)

    /*
    |---------------------------------------------------------------------------
    | Default User Type
    |---------------------------------------------------------------------------
    |
    | When creating a new user, this setting controls the default user type.
    | This is useful if you want to assign a default role or permission to new users.
    |
    */

    'default_user_type' => 'user', // Default user type on registration

];
