<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    protected $guarded = ['id'];
    public function locations()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }
}
