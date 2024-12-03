<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // فیلدهایی که از طریق Mass Assignment پر می‌شوند
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'user_id',
        'product_title',
        'quantity',
        'price',
        'image',
        'product_id',
        'payment_status',
        'delivery_status',
    ];

    // رابطه با مدل User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // رابطه با مدل Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
