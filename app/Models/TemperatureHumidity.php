<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\TemperatureHumidityNotificationService;

class TemperatureHumidity extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::updated(function (TemperatureHumidity $temperatureHumidity) {
            // Check if any of the monitored fields were updated
            $monitoredFields = [
                'time_0800', 'time_1100', 'time_1400', 'time_1700',
                'time_2000', 'time_2300', 'time_0200', 'time_0500',
                'temp_0800', 'temp_1100', 'temp_1400', 'temp_1700',
                'temp_2000', 'temp_2300', 'temp_0200', 'temp_0500',
                'rh_0800', 'rh_1100', 'rh_1400', 'rh_1700',
                'rh_2000', 'rh_2300', 'rh_0200', 'rh_0500',
                'is_reviewed'
            ];

            $hasRelevantChanges = false;
            foreach ($monitoredFields as $field) {
                if ($temperatureHumidity->wasChanged($field)) {
                    $hasRelevantChanges = true;
                    break;
                }
            }

            if ($hasRelevantChanges) {
                // Dispatch notification check asynchronously to avoid blocking the update
                dispatch(function () use ($temperatureHumidity) {
                    app(TemperatureHumidityNotificationService::class)
                        ->checkAndSendNotifications($temperatureHumidity);
                })->afterResponse();
            }
        });
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    
    public function roomTemperature()
    {
        return $this->belongsTo(RoomTemperature::class);
    }
    
    public function temperatureDeviations()
    {
        return $this->hasMany(TemperatureDeviation::class);
    }
    
    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class);
    }
}
