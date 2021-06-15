<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Group;

class Zone extends Model
{
    use HasFactory;

    public function getZone()
    {
        return $this->hasMany(Group::class, 'id', 'groups_id');
    }
}
