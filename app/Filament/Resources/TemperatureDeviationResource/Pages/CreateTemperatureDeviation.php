<?php

namespace App\Filament\Resources\TemperatureDeviationResource\Pages;

use Carbon\Carbon;
use App\Models\Location;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TemperatureDeviationResource;

class CreateTemperatureDeviation extends CreateRecord
{
    protected static string $resource = TemperatureDeviationResource::class;
    public function mount(): void
    {
        parent::mount();

        $locationId = request()->get('location_id');
        $time = request()->get('time');
        if ($locationId && $location = Location::find($locationId)) {
            $this->form->fill([
                'location_id' => $location->id,
                'temperature_humidity_id' => request()->get('temp_id'),
                'date' => Carbon::now(),
                'time' => $time ? Carbon::createFromFormat('H:i', $time)->format('H:i') : null,
                'serial_number' => $location->serial_number,
                'observed_temperature' => "{$location->temperature_start}°C to {$location->temperature_end}°C",
                'temperature_start' => $location->temperature_start,
                'temperature_end' => $location->temperature_end,
                'temperature_deviation' => request()->get('temperature_deviation'),
            ]);
        }
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['temperatureDeviationId'] = Str::orderedUuid();
        $data['date'] = Carbon::now();
        $data['temperature_deviation'] = request()->get('temperature_deviation') ?? $data['temperature_deviation'];
        $data['pic'] = auth()->user()->initial.' ' . strtoupper(Carbon::now()->format('d M Y'));
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        // Default fallback
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Temperature Deviation successfully created');
    }
}
