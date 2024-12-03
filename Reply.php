<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    // فیلدهایی که از طریق Mass Assignment پر می‌شوند
    protected $fillable = [
        'name',
        'comment_id',
        'reply',
        'user_id',
    ];

    // رابطه با مدل Comment
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    // رابطه با مدل User (اگر نیاز باشد برای ذخیره نام کاربری و مشخصات کاربر)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
