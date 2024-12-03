<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'title',
        'description',
        'image',
        'category',
        'quantity',
        'price',
        'discount_price',
    ];


    /**
     * Accessor to return the full URL of the product image.
     */
    public function getImageAttribute($value)
    {
        return $value ? asset('storage/product/' . $value) : null;
    }
}
