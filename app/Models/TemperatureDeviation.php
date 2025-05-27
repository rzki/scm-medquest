<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemperatureDeviation extends Model
{
    protected $guarded = ['id'];

    public function temperatureHumidity()
    {
        return $this->belongsTo(TemperatureHumidity::class);
    }
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class,'sn_id', 'id');
    }
}
