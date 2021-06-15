<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    public function getUser()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

    // public function getUser()
    // {
    //     return $this->hasOne(User::class, 'id', 'users_id');
    // }
}
