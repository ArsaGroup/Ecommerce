<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // نام جدول که در پایگاه داده ایجاد شده است
    protected $table = 'comments';

    // فیلدهایی که می‌توانند به طور mass-assignment تغییر کنند
    protected $fillable = [
        'name',
        'comment',
        'user_id',
    ];

    // ارتباط با مدل User (یک کامنت متعلق به یک کاربر)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
