<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Category;
use App\Models\Product;
use App\Notifications\MyFirstNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use PDF;

class AdminController extends Controller
{



    // View categories (API)
    public function view_category()
    {
        $data = Category::all();
        return response()->json(['categories' => $data]);
    }

    // Add category (API)
    public function add_category(Request $request)
    {
        $data = new Category;
        $data->category_name = $request->category;
        $data->save();

        return response()->json(['message' => 'Category Added Successfully']);
    }

    // Delete category (API)
    public function delete_category($id)
    {
        $data = Category::find($id);
        if ($data) {
            $data->delete();
            return response()->json(['message' => 'Category Deleted Successfully']);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    // View products (API)
    public function view_product()
    {
        $category = Category::all();
        return response()->json(['categories' => $category]);
    }

    // Add product (API)
    public function add_product(Request $request)
    {
        // اعتبارسنجی داده‌ها
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'discount' => 'nullable|numeric|min:0',
            'category' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // ایجاد شیء محصول
        $product = new Product;
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->quantity = $request->quantity;
        $product->discount_price = $request->discount;
        $product->category = $request->category;

        // بررسی و ذخیره تصویر
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('product'), $imagename);
            $product->image = $imagename;
        }

        // ذخیره محصول در پایگاه داده
        $product->save();

        // بازگشت پیام موفقیت
        return response()->json(['message' => 'Product Added Successfully']);
    }


    // Show products (API)
    public function show_product()
    {
        $products = Product::all();
        return response()->json(['products' => $products]);
    }

    // Delete product (API)
    public function delete_product($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
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

            return response()->json(['message' => 'Product Updated Successfully']);
        } else {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    // Get all orders (API)
    public function order()
    {
        $orders = Order::all();
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

            // ارسال نوتیفیکیشن (با استفاده از MyFirstNotification)
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
