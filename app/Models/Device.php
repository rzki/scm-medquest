<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $guarded = ['id'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function temperatureHumidity()
    {
        return $this->hasMany(TemperatureHumidity::class);
    }
    public function temperatureDeviations()
    {
        return $this->hasMany(TemperatureDeviation::class);
    }
    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }
}
