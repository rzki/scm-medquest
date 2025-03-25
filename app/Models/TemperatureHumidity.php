<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemperatureHumidity extends Model
{
    protected $guarded = ['id'];
    protected $table = 'temperatures_humidities';

    public function temperatureDeviations()
    {
        return $this->hasMany(TemperatureDeviation::class);
    }
}
