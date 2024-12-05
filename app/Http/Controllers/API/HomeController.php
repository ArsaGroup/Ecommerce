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

class HomeController extends Controller
{
    // Get all products (API)
    // متد index برای بازگشت لیست محصولات و نظرات و پاسخ‌ها
    public function index()
    {
        $product = Product::paginate(10); // گرفتن محصولات با صفحه‌بندی
        $comment = Comment::orderby('id', 'desc')->get(); // گرفتن نظرات
        $reply = Reply::all(); // گرفتن تمام پاسخ‌ها

        return response()->json([
            'products' => $product,
            'comments' => $comment,
            'replies' => $reply,
        ]);
    }

    // متد redirect برای بررسی نوع کاربر و ارسال داده‌ها
    public function redirect()
    {
        $usertype = Auth::user()->usertype;

        if ($usertype == '1') {  // اگر نوع کاربر admin باشد
            $total_product = Product::all()->count();
            $total_order = Order::all()->count();
            $total_user = User::all()->count();
            $order = Order::all();
            $total_revenue = 0;

            foreach ($order as $order) {
                $total_revenue += $order->price;
            }

            $total_delivered = Order::where('delivery_status', 'delivered')->count();
            $total_processing = Order::where('delivery_status', 'processing')->count();

            return response()->json([
                'total_product' => $total_product,
                'total_order' => $total_order,
                'total_user' => $total_user,
                'total_revenue' => $total_revenue,
                'total_delivered' => $total_delivered,
                'total_processing' => $total_processing,
            ]);
        } else {  // اگر نوع کاربر معمولی باشد
            $product = Product::paginate(10);
            $comment = Comment::orderby('id', 'desc')->get();
            $reply = Reply::all();

            return response()->json([
                'products' => $product,
                'comments' => $comment,
                'replies' => $reply,
            ]);
        }
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
    public function product()
    {
        // دریافت محصولات با صفحه‌بندی
        $products = Product::paginate(10);

        // دریافت کامنت‌ها به ترتیب نزولی
        $comments = Comment::orderBy('id', 'desc')->get();

        // دریافت پاسخ‌ها
        $replies = Reply::all();

        // بازگشت اطلاعات به صورت JSON
        return response()->json([
            'products' => $products,
            'comments' => $comments,
            'replies' => $replies,
        ], 200); // 200 به معنی موفقیت در پاسخ API
    }

}
