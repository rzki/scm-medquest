<?php

namespace App\Filament\Resources\TemperatureDeviationResource\Pages;

use App\Filament\Resources\TemperatureDeviationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTemperatureDeviations extends ListRecords
{
    protected static string $resource = TemperatureDeviationResource::class;
    protected static ?string $title = 'All Temperature Deviations';
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
