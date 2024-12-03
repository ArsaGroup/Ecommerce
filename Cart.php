<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    // فیلدهایی که از طریق Mass Assignment پر می‌شوند
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'product_title',
        'quantity',
        'price',
        'image',
        'product_id',
        'user_id',
    ];

    /**
     * ارتباط با مدل User
     * (اختیاری: اگر نیاز باشد سبد خرید به کاربر مربوط شود)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ارتباط با مدل Product
     * (اختیاری: اگر نیاز باشد سبد خرید به محصول مربوط شود)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
