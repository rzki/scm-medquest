<?php

namespace App\Filament\Resources\TemperatureDeviationResource\Pages;

use App\Filament\Resources\TemperatureDeviationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTemperatureDeviation extends EditRecord
{
    protected static string $resource = TemperatureDeviationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
