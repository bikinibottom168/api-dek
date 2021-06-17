<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $visible = ['label', 'children'];

    public function children()
    {
        return $this->hasMany(Zone::class, 'groups_id', 'id');
    }
}
