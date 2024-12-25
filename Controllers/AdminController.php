<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Notifications\MyFirstNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use PDF;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Http\Interfaces\AdminControllerInterface;

class AdminController extends Controller implements AdminControllerInterface
{
    // View categories (API) with Redis caching
    public function view_category()
    {
        $cachedCategories = Redis::get(config('admin.redis_keys.categories'));

        if ($cachedCategories) {
            $categories = json_decode($cachedCategories);
            Log::info('Fetched categories from Redis cache');
        } else {
            $categories = Category::all();
            Redis::setex(config('admin.redis_keys.categories'), config('admin.cache_expiration'), json_encode($categories));
            Log::info('Fetched categories from database and cached them in Redis');
        }

        return response()->json(['categories' => $categories]);
    }

    // Add category (API)
    public function add_category(Request $request)
    {
        $data = new Category;
        $data->category_name = $request->category;
        $data->save();

        Redis::del(config('admin.redis_keys.categories'));
        Log::info('Added new category', ['category_name' => $data->category_name]);

        return response()->json(['message' => 'Category Added Successfully']);
    }

    // Delete category (API)
    public function delete_category($id)
    {
        $data = Category::find($id);
        if ($data) {
            $data->delete();
            Redis::del(config('admin.redis_keys.categories'));
            Log::info('Deleted category', ['category_id' => $id, 'category_name' => $data->category_name]);
            return response()->json(['message' => 'Category Deleted Successfully']);
        } else {
            Log::warning('Attempted to delete non-existing category', ['category_id' => $id]);
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    // View products (API) with Redis caching
    public function view_product()
    {
        $cachedProducts = Redis::get(config('admin.redis_keys.products'));

        if ($cachedProducts) {
            $products = json_decode($cachedProducts);
            Log::info('Fetched products from Redis cache');
        } else {
            $products = Product::all();
            Redis::setex(config('admin.redis_keys.products'), config('admin.cache_expiration'), json_encode($products));
            Log::info('Fetched products from database and cached them in Redis');
        }

        return response()->json(['products' => $products]);
    }

    // Add product (API)
    public function add_product(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'discount' => 'nullable|numeric|min:0',
            'category' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:' . implode(',', config('admin.image.allowed_extensions')) . '|max:' . config('admin.image.max_size'),
        ]);

        $product = new Product;
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->discount_price = $request->discount;
        $product->category = $request->category;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('product'), $imagename);
            $product->image = $imagename;
        }

        $product->save();

        Redis::del(config('admin.redis_keys.products'));
        Log::info('Added new product', ['product_id' => $product->id, 'product_name' => $product->title]);

        return response()->json(['message' => 'Product Added Successfully']);
    }

    // Show products (API) with Redis caching
    public function show_product()
    {
        $products = Redis::get(config('admin.redis_keys.products'));

        if ($products) {
            $products = json_decode($products);
            Log::info('Fetched products from Redis cache');
        } else {
            $products = Product::all();
            Redis::setex(config('admin.redis_keys.products'), config('admin.cache_expiration'), json_encode($products));
            Log::info('Fetched products from database and cached them in Redis');
        }

        return response()->json(['products' => $products]);
    }

    // Delete product (API)
    public function delete_product($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            Redis::del(config('admin.redis_keys.products'));
            Log::info('Deleted product', ['product_id' => $id, 'product_name' => $product->title]);
            return response()->json(['message' => 'Product Deleted Successfully']);
        } else {
            Log::warning('Attempted to delete non-existing product', ['product_id' => $id]);
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    // Update product (API)
    public function update_product($id)
    {
        $product = Product::find($id);
        if ($product) {
            $category = Category::all();
            Log::info('Fetching product details for update', ['product_id' => $id]);
            return response()->json(['product' => $product, 'categories' => $category]);
        } else {
            Log::warning('Attempted to update non-existing product', ['product_id' => $id]);
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    // Confirm product update (API)
    public function update_product_confirm(Request $request, $id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->title = $request->title;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->discount_price = $request->discount;
            $product->category = $request->category;
            $product->quantity = $request->quantity;

            if ($request->hasFile('image')) {
                $image = $request->image;
                $imagename = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('product'), $imagename);
                $product->image = $imagename;
            }

            $product->save();

            Redis::del(config('admin.redis_keys.products'));
            Log::info('Updated product details', ['product_id' => $id]);

            return response()->json(['message' => 'Product Updated Successfully']);
        } else {
            Log::warning('Attempted to update non-existing product', ['product_id' => $id]);
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    // Get all orders (API) with Redis caching
    public function order()
    {
        $cachedOrders = Redis::get(config('admin.redis_keys.orders'));

        if ($cachedOrders) {
            $orders = json_decode($cachedOrders);
            Log::info('Fetched orders from Redis cache');
        } else {
            $orders = Order::all();
            Redis::setex(config('admin.redis_keys.orders'), config('admin.cache_expiration'), json_encode($orders));
            Log::info('Fetched orders from database and cached them in Redis');
        }

        return response()->json(['orders' => $orders]);
    }

    // Mark order as delivered (API)
    public function delivered($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->delivery_status = 'delivered';
            $order->payment_status = 'Paid';
            $order->save();

            Redis::del(config('admin.redis_keys.orders'));
            Log::info('Marked order as delivered', ['order_id' => $id]);

            return response()->json(['message' => 'Order Delivered Successfully']);
        } else {
            Log::warning('Attempted to mark non-existing order as delivered', ['order_id' => $id]);
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    // Generate order PDF (API)
    public function print_pdf($id)
    {
        $order = Order::find($id);
        if ($order) {
            $pdf = PDF::loadView('pdf.order', compact('order'));
            Log::info('Generated order PDF', ['order_id' => $id]);
            return $pdf->download('order_' . $id . '.pdf');
        } else {
            Log::warning('Attempted to generate PDF for non-existing order', ['order_id' => $id]);
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    // Send user email (API)
    public function send_user_email(Request $request, $id)
    {
        $order = Order::find($id);
        if ($order) {
            $details = config('admin.notification');
            $details['greeting'] = $request->greeting;
            $details['firstline'] = $request->firstline;
            $details['body'] = $request->body;
            $details['button'] = $request->button;
            $details['url'] = $request->url;
            $details['lastline'] = $request->lastline;

            Notification::send($order, new MyFirstNotification($details));
            Log::info('Sent user email', ['order_id' => $id]);

            return response()->json(['message' => 'User email sent successfully']);
        }

        Log::warning('Attempted to send email for non-existing order', ['order_id' => $id]);
        return response()->json(['message' => 'Order not found'], 404);
    }

    // Search data (API)
    public function searchdata(Request $request)
    {
        $query = $request->input('query');
        $products = Product::where('title', 'like', "%$query%")->get();
        Log::info('Performed product search', ['query' => $query]);

        return response()->json($products);
    }
}
