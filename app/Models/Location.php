<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded = ['id'];

    public function temperatureHumidity()
    {
        return $this->hasMany(TemperatureHumidity::class);
    }
    public function temperatureDeviations()
    {
        return $this->hasMany(TemperatureDeviation::class);
    }
}
