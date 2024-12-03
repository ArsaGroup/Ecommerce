<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Comment;
use App\Models\Reply;
use Stripe;

class HomeController extends Controller
{
    // Get all products (API)
    public function index()
    {
        $products = Product::paginate(10);
        return response()->json($products);
    }

    // Product details (API)
    public function product_details($id)
    {
        $product = Product::findOrFail($id);
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

        return response()->json(['message' => 'Product added to cart'], 200);
    }

    // Show cart (API)
    public function show_cart()
    {
        $cartItems = Cart::all();
        return response()->json($cartItems);
    }

    // Remove product from cart (API)
    public function remove_cart($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();
        return response()->json(['message' => 'Product removed from cart'], 200);
    }

    // Place order (API)
    public function cash_order()
    {
        $cartItems = Cart::all();

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

        return response()->json(['message' => 'Order placed successfully'], 200);
    }

    // Stripe payment (API)
    public function stripePost(Request $request, $totalprice)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        Stripe\Charge::create([
            "amount" => $totalprice * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "Thanks for payment."
        ]);

        $cartItems = Cart::all();

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

        return response()->json(['message' => 'Payment successful'], 200);
    }

    // Show user's orders (API)
    public function show_order()
    {
        $orders = Order::all();
        return response()->json($orders);
    }

    // Cancel an order (API)
    public function cancel_order($id)
    {
        $order = Order::findOrFail($id);
        $order->delivery_status = 'You canceled the order';
        $order->save();
        return response()->json(['message' => 'Order canceled'], 200);
    }

    // Add comment to product (API)
    public function add_comment(Request $request)
    {
        $comment = new Comment;
        $comment->comment = $request->comment;
        $comment->save();
        return response()->json(['message' => 'Comment added'], 200);
    }

    // Add reply to comment (API)
    public function add_reply(Request $request)
    {
        $reply = new Reply;
        $reply->comment_id = $request->commentId;
        $reply->reply = $request->reply;
        $reply->save();
        return response()->json(['message' => 'Reply added'], 200);
    }

    // Search products (API)
    public function product_search(Request $request)
    {
        $search_text = $request->search;
        $products = Product::where('title', 'LIKE', "%$search_text%")
            ->orWhere('category', 'LIKE', "%$search_text%")
            ->paginate(10);

        return response()->json($products);
    }
}
