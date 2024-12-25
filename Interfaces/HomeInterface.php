<?php

namespace App\Http\Interfaces;

use Illuminate\Http\Request;

interface HomeInterface
{
    // Get all products with Redis caching and other related data (comments, replies)
    public function index();

    // Redirect user based on user type (Admin or Regular User)
    public function redirect();

    // Dashboard with user data
    public function dashboard(Request $request);

    // Get product details by ID with Redis caching
    public function product_details($id);

    // Add product to cart
    public function add_cart(Request $request, $id);

    // Show all cart items with Redis caching
    public function show_cart();

    // Remove product from cart
    public function remove_cart($id);

    // Place an order using cash on delivery method with Redis caching
    public function cash_order();

    // Stripe payment integration for placing an order
    public function stripePost(Request $request, $totalprice);

    // Show all orders placed by the user with Redis caching
    public function show_order();

    // Cancel an order by ID
    public function cancel_order($id);

    // Add comment to a product
    public function add_comment(Request $request);

    // Add reply to a comment
    public function add_reply(Request $request);

    // Search products based on a query with Redis caching
    public function product_search(Request $request);

    // Get products, comments, and replies with Redis caching
    public function product();
}
