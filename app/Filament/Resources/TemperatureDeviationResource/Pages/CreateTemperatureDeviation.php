<?php

namespace App\Filament\Resources\TemperatureDeviationResource\Pages;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TemperatureDeviationResource;

class CreateTemperatureDeviation extends CreateRecord
{
    protected static string $resource = TemperatureDeviationResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['temperatureDeviationId'] = Str::orderedUuid();
        $data['date'] = Carbon::now();
        $data['time'] = request()->get('time');
        return $data;
    }
}
