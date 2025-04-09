<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\TemperatureHumidity;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\TemperatureHumidityResource;
use App\Filament\Resources\TemperatureDeviationResource;

class EditTemperatureHumidity extends EditRecord
{
    protected static string $resource = TemperatureHumidityResource::class;
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure temperature fields are set
        $tempFields = ['temp_0800', 'temp_1100', 'temp_1400', 'temp_1700'];
        foreach ($tempFields as $temp) {
            $data[$temp] = $data[$temp] ?? null;
        }

        // Assign observed temperature values correctly
        $observedTempsArray = implode('', $data['observed_temperature']);
        $observedTemps = explode('|', $observedTempsArray);
        [$minTemp, $maxTemp] = $observedTemps;

        // Store observed temperature range
        $data['observed_temperature_start'] = $minTemp;
        $data['observed_temperature_end'] = $maxTemp;   

        // ✅ Auto-fill signature into current time window's PIC field
        $now = Carbon::now()->timezone('Asia/Jakarta');
        $signature = auth()->user()->initial . ' ' . strtoupper($now->format('d M Y'));

        $timeWindows = [
            'pic_0800' => ['start' => '08:00', 'end' => '10:59'],
            'pic_1100' => ['start' => '11:00', 'end' => '13:59'],
            'pic_1400' => ['start' => '14:00', 'end' => '16:59'],
            'pic_1700' => ['start' => '17:00', 'end' => '18:59'],
        ];

        foreach ($timeWindows as $picField => $window) {
            $start = Carbon::createFromTimeString($window['start'], 'Asia/Jakarta');
            $end = Carbon::createFromTimeString($window['end'], 'Asia/Jakarta');

            if ($now->between($start, $end)) {
                $data[$picField] = $signature;
                break;
            }
        }
            
        // Trigger deviation if value out of range
        $deviationDetected = false;
        foreach ($tempFields as $field) {
            if (!is_null($data[$field]) && ($data[$field] < $minTemp || $data[$field] > $maxTemp)) {
                $deviationDetected = true;
                break;
            }
        }

        if ($deviationDetected) {
            session()->put('deviation_triggered', true);
        }

        return $data;
    }
    protected function afterSave(): void
    {
        $record = $this->record;

        // Get observed limits
        $minTemp = $record->observed_temperature_start;
        $maxTemp = $record->observed_temperature_end;
        $observedTemperature = $minTemp . '|' . $maxTemp;
        $now = Carbon::now()->timezone('Asia/Jakarta');

        $timeWindows = [
            'temp_0800' => ['start' => '08:00', 'end' => '10:59', 'time_field' => 'time_0800'],
            'temp_1100' => ['start' => '11:00', 'end' => '13:59', 'time_field' => 'time_1100'],
            'temp_1400' => ['start' => '14:00', 'end' => '16:59', 'time_field' => 'time_1400'],
            'temp_1700' => ['start' => '17:00', 'end' => '18:59', 'time_field' => 'time_1700'],
        ];

        foreach ($timeWindows as $tempField => $window) {
            $start = Carbon::createFromTimeString($window['start'], 'Asia/Jakarta');
            $end = Carbon::createFromTimeString($window['end'], 'Asia/Jakarta');

            if ($now->between($start, $end)) {
                $tempValue = $record->$tempField;
                $timeValue = $record->{$window['time_field']};

                if (!is_null($tempValue) && ($tempValue < $minTemp || $tempValue > $maxTemp)) {
                    session()->put('deviation_triggered', true);
                    session()->put('deviation_data', [[ // wrap in array to match expected format
                        'temperature_id' => $record->id,
                        'temp_range' => $observedTemperature,
                        'time' => $timeValue,
                        'temperature_deviation' => $tempValue,
                    ]]);
                }

                break; // Only evaluate the current time window
            }
        }
    }
    protected function getRedirectUrl(): string
    {
        // Check if deviation was triggered
        if (session()->pull('deviation_triggered', false)) {
            $deviations = session()->pull('deviation_data', []);

            if (!empty($deviations) && isset($deviations[0])) {
                return TemperatureDeviationResource::getUrl('create', [
                    'temp_id' => $deviations[0]['temperature_id'],
                    'temp_range' => $deviations[0]['temp_range'],
                    'time' => $deviations[0]['time'],
                    'temperature_deviation' => $deviations[0]['temperature_deviation'],
                ]);
            }
        }

        // Default fallback
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
