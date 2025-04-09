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
}
