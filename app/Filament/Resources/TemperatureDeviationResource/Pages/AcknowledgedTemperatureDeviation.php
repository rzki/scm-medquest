<?php

namespace App\Filament\Resources\TemperatureDeviationResource\Pages;

use App\Filament\Resources\TemperatureDeviationResource;
use Filament\Actions;
use Filament\Resources\Pages\listRecords;

class AcknowledgedTemperatureDeviation extends listRecords
{
    protected static string $resource = TemperatureDeviationResource::class;
    protected static ?string $title = 'Pending Acknowledgement';
    public function getBreadcrumb(): string
    {
        return 'Pending Acknowledgement'; // or any label you want
    }
}
