<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use App\Filament\Resources\TemperatureHumidityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTemperatureHumidity extends EditRecord
{
    protected static string $resource = TemperatureHumidityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
