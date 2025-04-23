<?php

namespace App\Filament\Resources\TemperatureDeviationResource\Pages;

use App\Filament\Resources\TemperatureDeviationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ReviewedTemperatureDeviation extends ListRecords
{
    protected static string $resource = TemperatureDeviationResource::class;
    protected static ?string $title = 'Pending Review';
    public function getBreadcrumb(): string
    {
        return 'Pending Review'; // or any label you want
    }
}
