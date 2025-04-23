<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\TemperatureHumidity;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TemperatureHumidityStatus extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static bool $isLazy = false;
    protected ?string $heading = 'Temperature & Humidity';
    protected static ?int $sort = 0;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Data (this month)', TemperatureHumidity::whereMonth('date', Carbon::now()->month)->count())
                ->color('dark')
                ->url(route('filament.dashboard.resources.temperature-humidities.index')),
            Stat::make('Pending Review', TemperatureHumidity::where('is_reviewed', false)->count())
                ->color('warning')
                ->url(route('filament.dashboard.resources.temperature-humidities.reviewed')),
            Stat::make('Pending Acknowledged', TemperatureHumidity::where('is_acknowledged', false)->count())
                ->color('info')
                ->url(route('filament.dashboard.resources.temperature-humidities.acknowledged')),        
            ];
    }
}
