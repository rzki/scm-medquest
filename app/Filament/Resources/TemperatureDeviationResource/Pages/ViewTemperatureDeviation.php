<?php

namespace App\Filament\Resources\TemperatureDeviationResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\TemperatureDeviationResource;

class ViewTemperatureDeviation extends ViewRecord
{
    protected static string $resource = TemperatureDeviationResource::class;
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => Auth::user()->hasRole('Supply Chain Officer')),
        ];
    }
}
