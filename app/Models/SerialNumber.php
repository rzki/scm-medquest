<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerialNumber extends Model
{
    protected $guarded = ['id'];
    public function devices()
    {
        return $this->belongsTo(Device::class, 'device_id', 'id');
    }
}
