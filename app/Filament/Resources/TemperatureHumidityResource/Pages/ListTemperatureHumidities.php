<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use App\Filament\Resources\TemperatureHumidityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemperatureHumidities extends ListRecords
{
    protected static string $resource = TemperatureHumidityResource::class;
    protected static ?string $title = 'All Temperature & Humidity';
    public function getBreadcrumb(): string
    {
        return 'All'; // or any label you want
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
