<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Illuminate\Support\Str;
use App\Models\TemperatureDeviation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TemperatureHumidityResource;
use App\Filament\Resources\TemperatureDeviationResource;

class CreateTemperatureHumidity extends CreateRecord
{
    protected static string $resource = TemperatureHumidityResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['temperatureId'] = Str::orderedUuid();
        // Ensure temperature fields are set
        $tempFields = ['temp_0800', 'temp_1100', 'temp_1400', 'temp_1700'];
        foreach ($tempFields as $temp) {
            $data[$temp] = $data[$temp] ?? null;
        }

        // Assign observed temperature values correctly
        $observedTempsArray = implode('', $data['observed_temperature']);
        $observedTemps = explode('|', $observedTempsArray);
        $data['location'] = 'Bizpark 1';
        $data['serial_no'] = '001';

        [$minTemp, $maxTemp] = $observedTemps;

        $deviationDetected = false;

        // Validate temperature fields
        foreach ($tempFields as $field) {
            if (!is_null($data[$field]) && ($data[$field] < $minTemp || $data[$field] > $maxTemp)) {
                $deviationDetected = true;
                break; // Stop checking once a deviation is found
            }
        }

        if ($deviationDetected) {
            session()->put('deviation_triggered', true);
        }

        // Store observed temperature range
        $data['observed_temperature_start'] = $minTemp;
        $data['observed_temperature_end'] = $maxTemp;

        return $data;
    }
    protected function afterCreate()
    {
        // Retrieve the created Temperature Humidity record and set temperature and time that triggers deviation
        $temperatureHumidity = $this->record;
        // Retrieve the observed temperature range
        $minTemp = $temperatureHumidity->observed_temperature_start;
        $maxTemp = $temperatureHumidity->observed_temperature_end;
// Define temperature fields linked to their respective time fields
        $timeSlots = [
            'temp_0800' => 'time_0800',
            'temp_1100' => 'time_1100',
            'temp_1400' => 'time_1400',
            'temp_1700' => 'time_1700'
        ];

        // Check all temperature fields and store deviations
        foreach ($timeSlots as $tempField => $timeField) {
            $tempValue = $temperatureHumidity->$tempField; // Get temperature value
            $inputtedTime = $temperatureHumidity->$timeField ?? 'Unknown Time'; // Get user-inputted time
            
            if (!is_null($tempValue) && ($tempValue < $minTemp || $tempValue > $maxTemp)) {
                $deviationData[] = [
                    'temperature_id' => $temperatureHumidity->id,
                    'time' => $inputtedTime,
                    'temperature_deviation' => $tempValue,
                ];
            }
        }
        if (!empty($deviationData)) {
        session()->put('deviation_triggered', true);
        session()->put('deviation_data', $deviationData);
    }
    }
    protected function getRedirectUrl(): string
    {
        // Check if deviation was triggered
        if (session()->pull('deviation_triggered', false)) {
            // Default to false
            $deviations = session()->pull('deviation_data', []);
            return TemperatureDeviationResource::getUrl('create',[
                'temp_id' => $deviations[0]['temperature_id'],
                'time' => $deviations[0]['time'],
                'temperature_deviation' => $deviations[0]['temperature_deviation']
            ]); // Redirect to Deviation form
        }

        return $this->getResource()::getUrl('index'); // Default redirect after successful save
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()->success()->body('Temperature & Humidity log successfully created!');
    }
}
