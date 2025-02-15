<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Comment;
use App\Models\Reply;
use Illuminate\Support\Facades\Auth;
use Stripe;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Interfaces\HomeInterface;

class HomeController extends Controller implements HomeInterface
{
    // Get all products (API) with Redis caching
    public function index()
    {
        Log::info('Fetching all products from cache or database.');

        $product = Cache::remember('products_page_1', config('settings.cache_expiration'), function () {
            return Product::paginate(10);
        });

        $comment = Cache::remember('comments', config('settings.cache_expiration'), function () {
            return Comment::orderby('id', 'desc')->get();
        });

        $reply = Cache::remember('replies', config('settings.cache_expiration'), function () {
            return Reply::all();
        });

        Log::info('Products, comments, and replies fetched successfully.');

        return response()->json([
            'products' => $product,
            'comments' => $comment,
            'replies' => $reply,
        ]);
    }

    // Redirect method with Redis caching for admin statistics
    public function redirect()
    {
        $usertype = Auth::user()->usertype;

        if ($usertype == '1') {  // Admin user
            Log::info('Admin user is accessing statistics.');

            $total_product = Cache::remember('total_products', config('settings.cache_expiration'), function () {
                return Product::all()->count();
            });

            $total_order = Cache::remember('total_orders', config('settings.cache_expiration'), function () {
                return Order::all()->count();
            });

            $total_user = Cache::remember('total_users', config('settings.cache_expiration'), function () {
                return User::all()->count();
            });

            $order = Order::all();
            $total_revenue = 0;
            foreach ($order as $order) {
                $total_revenue += $order->price;
            }

            $total_delivered = Order::where('delivery_status', 'delivered')->count();
            $total_processing = Order::where('delivery_status', 'processing')->count();

            Log::info('Admin statistics fetched successfully.');

            return response()->json([
                'total_product' => $total_product,
                'total_order' => $total_order,
                'total_user' => $total_user,
                'total_revenue' => $total_revenue,
                'total_delivered' => $total_delivered,
                'total_processing' => $total_processing,
            ]);
        } else {  // Regular user
            Log::info('Regular user is accessing products and comments.');

            $product = Cache::remember('products_page_1', config('settings.cache_expiration'), function () {
                return Product::paginate(10);
            });

            $comment = Cache::remember('comments', config('settings.cache_expiration'), function () {
                return Comment::orderby('id', 'desc')->get();
            });

            $reply = Cache::remember('replies', config('settings.cache_expiration'), function () {
                return Reply::all();
            });

            Log::info('Products, comments, and replies fetched successfully for regular user.');

            return response()->json([
                'products' => $product,
                'comments' => $comment,
                'replies' => $reply,
            ]);
        }
    }

    // Dashboard method with user data
    public function dashboard(Request $request)
    {
        $user = $request->user();
        Log::info('User dashboard accessed.', ['user' => $user->id]);

        return response()->json([
            'message' => 'Welcome to the Dashboard!',
            'user' => $user,
        ]);
    }

    // Product details (API) with Redis caching
    public function product_details($id)
    {
        Log::info('Fetching product details for product ID: ' . $id);

        $product = Cache::remember("product_details_{$id}", config('settings.cache_expiration'), function () use ($id) {
            return Product::findOrFail($id);
        });

        Log::info('Product details fetched successfully.', ['product_id' => $id]);

        return response()->json($product);
    }

    // Add product to cart (API)
    public function add_cart(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $cart = new Cart;
        $cart->product_id = $product->id;
        $cart->quantity = $request->quantity;
        $cart->price = $product->discount_price ? $product->discount_price * $cart->quantity : $product->price * $cart->quantity;
        $cart->save();

        Cache::forget('cart_items');  // Clear cart cache after adding an item

        Log::info('Product added to cart.', ['product_id' => $id, 'quantity' => $request->quantity]);

        return response()->json(['message' => 'Product added to cart'], 200);
    }

    // Show cart (API) with Redis caching
    public function show_cart()
    {
        Log::info('Fetching cart items from cache or database.');

        $cartItems = Cache::remember('cart_items', config('settings.cache_expiration'), function () {
            return Cart::all();
        });

        Log::info('Cart items fetched successfully.');

        return response()->json($cartItems);
    }

    // Remove product from cart (API)
    public function remove_cart($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();

        Cache::forget('cart_items');  // Clear cart cache after removal

        Log::info('Product removed from cart.', ['cart_item_id' => $id]);

        return response()->json(['message' => 'Product removed from cart'], 200);
    }

    // Place order (API) with Redis caching
    public function cash_order()
    {
        Log::info('Placing cash on delivery order.');

        $cartItems = Cache::remember('cart_items', config('settings.cache_expiration'), function () {
            return Cart::all();
        });

        foreach ($cartItems as $item) {
            $order = new Order;
            $order->product_title = $item->product->title;
            $order->price = $item->price;
            $order->quantity = $item->quantity;
            $order->payment_status = 'cash on delivery';
            $order->delivery_status = 'processing';
            $order->save();
            $item->delete();
        }

        Cache::forget('cart_items');  // Clear cart cache after placing an order

        Log::info('Cash order placed successfully.');

        return response()->json(['message' => 'Order placed successfully'], 200);
    }

    // Stripe payment (API) with Redis caching
    public function stripePost(Request $request, $totalprice)
    {
        Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        Stripe\Charge::create([
            "amount" => $totalprice * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "Thanks for payment."
        ]);

        $cartItems = Cache::remember('cart_items', config('settings.cache_expiration'), function () {
            return Cart::all();
        });

        foreach ($cartItems as $item) {
            $order = new Order;
            $order->product_title = $item->product->title;
            $order->price = $item->price;
            $order->quantity = $item->quantity;
            $order->payment_status = 'Paid';
            $order->delivery_status = 'processing';
            $order->save();
            $item->delete();
        }

        Cache::forget('cart_items');  // Clear cart cache after payment

        Log::info('Payment processed successfully via Stripe.');

        return response()->json(['message' => 'Payment successful'], 200);
    }

    // Show user's orders (API) with Redis caching
    public function show_order()
    {
        Log::info('Fetching user orders from cache or database.');

        $orders = Cache::remember('user_orders', config('settings.cache_expiration'), function () {
            return Order::all();
        });

        Log::info('User orders fetched successfully.');

        return response()->json($orders);
    }

    // Cancel an order (API)
    public function cancel_order($id)
    {
        Log::info('Canceling order ID: ' . $id);

        $order = Order::findOrFail($id);
        $order->delivery_status = 'You canceled the order';
        $order->save();

        Cache::forget('user_orders');  // Clear orders cache after canceling

        Log::info('Order canceled successfully.', ['order_id' => $id]);

        return response()->json(['message' => 'Order canceled'], 200);
    }

    // Add comment to product (API)
    public function add_comment(Request $request)
    {
        Log::info('Adding comment to product.');

        $comment = new Comment;
        $comment->comment = $request->comment;
        $comment->save();

        Cache::forget('comments');  // Clear comments cache after adding a new one

        Log::info('Comment added successfully.');

        return response()->json(['message' => 'Comment added'], 200);
    }

    // Add reply to comment (API)
    public function add_reply(Request $request)
    {
        Log::info('Adding reply to comment ID: ' . $request->commentId);

        $reply = new Reply;
        $reply->comment_id = $request->commentId;
        $reply->reply = $request->reply;
        $reply->save();

        Cache::forget('replies');  // Clear replies cache after adding a new reply

        Log::info('Reply added successfully.');

        return response()->json(['message' => 'Reply added'], 200);
    }

    // Search products (API) with Redis caching
    public function product_search(Request $request)
    {
        $search_text = $request->search;
        $cacheKey = 'product_search_' . md5($search_text);

        Log::info('Searching products with text: ' . $search_text);

        $products = Cache::remember($cacheKey, config('settings.cache_expiration'), function () use ($search_text) {
            return Product::where('title', 'LIKE', "%$search_text%")
                ->orWhere('category', 'LIKE', "%$search_text%")
                ->paginate(10);
        });

        Log::info('Search results fetched successfully.');

        return response()->json($products);
    }

    // Get all products (API) with Redis caching
    public function product()
    {
        Log::info('Fetching all products for regular users.');

        $products = Cache::remember('products_page_1', config('settings.cache_expiration'), function () {
            return Product::paginate(10);
        });

        $comments = Cache::remember('comments', config('settings.cache_expiration'), function () {
            return Comment::orderBy('id', 'desc')->get();
        });

        $replies = Cache::remember('replies', config('settings.cache_expiration'), function () {
            return Reply::all();
        });

        Log::info('Products, comments, and replies fetched successfully for regular user.');

        return response()->json([
            'products' => $products,
            'comments' => $comments,
            'replies' => $replies,
        ]);
    }
}
