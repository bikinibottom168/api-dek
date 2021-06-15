<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public function getComment()
    {
        return $this->hasMany(Review::class, 'posts_id', 'id');
    }

    public function getUser()
    {
        return $this->hasOne(User::class, 'users_id', 'id');
    }
}
