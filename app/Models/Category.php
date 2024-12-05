<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // فیلدهایی که از طریق Mass Assignment پر می‌شوند
    protected $fillable = [
        'category_name',
    ];

    // اگر دسته‌بندی با محصولات رابطه دارد (مثلاً یک دسته‌بندی شامل محصولات متعدد است)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
