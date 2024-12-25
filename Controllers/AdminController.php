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

class AdminController extends Controller
{
    // View categories (API) with Redis caching
    public function view_category()
    {
        // Try to fetch categories from cache
        $cachedCategories = Redis::get('categories');

        if ($cachedCategories) {
            // If categories are cached, return them
            $categories = json_decode($cachedCategories);
        } else {
            // Otherwise, fetch from the database and cache for 10 minutes
            $categories = Category::all();
            Redis::setex('categories', 600, json_encode($categories));
        }

        return response()->json(['categories' => $categories]);
    }

    // Add category (API)
    public function add_category(Request $request)
    {
        $data = new Category;
        $data->category_name = $request->category;
        $data->save();

        // Clear the category cache after adding a new category
        Redis::del('categories');

        return response()->json(['message' => 'Category Added Successfully']);
    }

    // Delete category (API)
    public function delete_category($id)
    {
        $data = Category::find($id);
        if ($data) {
            $data->delete();

            // Clear the category cache after deleting a category
            Redis::del('categories');
            return response()->json(['message' => 'Category Deleted Successfully']);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    // View products (API) with Redis caching
    public function view_product()
    {
        // Try to fetch categories from cache
        $cachedProducts = Redis::get('products');

        if ($cachedProducts) {
            // If products are cached, return them
            $products = json_decode($cachedProducts);
        } else {
            // Otherwise, fetch from the database and cache for 10 minutes
            $products = Product::all();
            Redis::setex('products', 600, json_encode($products));
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Create new product
        $product = new Product;
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->discount_price = $request->discount;
        $product->category = $request->category;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('product'), $imagename);
            $product->image = $imagename;
        }

        // Save product in the database
        $product->save();

        // Clear the product cache after adding a new product
        Redis::del('products');

        return response()->json(['message' => 'Product Added Successfully']);
    }

    // Show products (API) with Redis caching
    public function show_product()
    {
        $products = Redis::get('products');

        if ($products) {
            // If products are cached, return them
            $products = json_decode($products);
        } else {
            // Otherwise, fetch from the database and cache for 10 minutes
            $products = Product::all();
            Redis::setex('products', 600, json_encode($products));
        }

        return response()->json(['products' => $products]);
    }

    // Delete product (API)
    public function delete_product($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();

            // Clear the product cache after deleting a product
            Redis::del('products');
            return response()->json(['message' => 'Product Deleted Successfully']);
        } else {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    // Update product (API)
    public function update_product($id)
    {
        $product = Product::find($id);
        if ($product) {
            $category = Category::all();
            return response()->json(['product' => $product, 'categories' => $category]);
        } else {
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

            // Clear the product cache after updating a product
            Redis::del('products');

            return response()->json(['message' => 'Product Updated Successfully']);
        } else {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    // Get all orders (API) with Redis caching
    public function order()
    {
        // Try to fetch orders from cache
        $cachedOrders = Redis::get('orders');

        if ($cachedOrders) {
            // If orders are cached, return them
            $orders = json_decode($cachedOrders);
        } else {
            // Otherwise, fetch from the database and cache for 10 minutes
            $orders = Order::all();
            Redis::setex('orders', 600, json_encode($orders));
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

            // Clear the orders cache after updating the order
            Redis::del('orders');

            return response()->json(['message' => 'Order Delivered Successfully']);
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    // Generate order PDF (API)
    public function print_pdf($id)
    {
        $order = Order::find($id);
        if ($order) {
            $pdf = PDF::loadView('pdf.order', compact('order'));
            return $pdf->download('order_' . $id . '.pdf');
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    // Send user email (API)
    public function send_user_email(Request $request, $id)
    {
        $order = Order::find($id);
        if ($order) {
            $details = [
                'greeting' => $request->greeting,
                'firstline' => $request->firstline,
                'body' => $request->body,
                'button' => $request->button,
                'url' => $request->url,
                'lastline' => $request->lastline,
            ];

            // Send notification using MyFirstNotification
            Notification::send($order, new MyFirstNotification($details));
            return response()->json(['message' => 'User email sent successfully']);
        }
        return response()->json(['message' => 'Order not found'], 404);
    }

    // Search data (API)
    public function searchdata(Request $request)
    {
        $query = $request->input('query');
        $products = Product::where('title', 'like', "%$query%")->get();
        return response()->json($products);
    }
}
