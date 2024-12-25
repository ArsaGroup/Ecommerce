<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Cache Expiration Times
    |--------------------------------------------------------------------------
    |
    | Define how long we want to store certain data in the cache.
    | These values control the expiration time for cached data like categories,
    | products, orders, etc. These values are in seconds.
    |
    */

    'cache_expiration' => 600, // Default cache expiration time (10 minutes)

    /*
    |--------------------------------------------------------------------------
    | Redis Cache Keys
    |--------------------------------------------------------------------------
    |
    | These are the Redis keys used for caching the data. It helps to centralize
    | and customize the cache keys used across various controller methods.
    |
    */

    'redis_keys' => [
        'categories' => 'categories',
        'products' => 'products',
        'orders' => 'orders',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Here we specify the allowed image types for product images and the maximum
    | file size for uploaded images.
    |
    */

    'image' => [
        'allowed_extensions' => ['jpeg', 'png', 'jpg', 'gif', 'svg'],
        'max_size' => 2048, // Maximum file size in KB
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure email settings for the admin's order-related notifications.
    | This is used in the `send_user_email` method to send notifications to users.
    |
    */

    'notification' => [
        'greeting' => 'Hello,',
        'firstline' => 'We have an update regarding your order.',
        'body' => 'Your order has been processed successfully.',
        'button' => 'View Order',
        'url' => 'http://example.com/order',
        'lastline' => 'Thank you for shopping with us!',
    ],

];
