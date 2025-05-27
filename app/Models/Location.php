<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded = ['id'];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
