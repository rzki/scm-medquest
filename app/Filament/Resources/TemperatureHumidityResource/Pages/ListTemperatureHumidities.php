<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use App\Filament\Resources\TemperatureHumidityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemperatureHumidities extends ListRecords
{
    protected static string $resource = TemperatureHumidityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
