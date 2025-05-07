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
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class);
    }
}
