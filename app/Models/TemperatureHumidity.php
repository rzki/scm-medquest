<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemperatureHumidity extends Model
{
    protected $guarded = ['id'];

    public function temperatureDeviations()
    {
        return $this->hasMany(TemperatureDeviation::class);
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
