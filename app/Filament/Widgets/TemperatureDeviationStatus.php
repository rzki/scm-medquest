<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\TemperatureDeviation;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TemperatureDeviationStatus extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected static bool $isLazy = false;
    protected ?string $heading = 'Temperature Deviation';
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Data (this month)', TemperatureDeviation::whereMonth('date', Carbon::now()->month)->count())
                ->color('dark')
                ->url(route('filament.dashboard.resources.temperature-deviations.index')),
            Stat::make('Pending Review', TemperatureDeviation::where('is_reviewed', false)->whereNotNull('length_temperature_deviation')->whereNotNull('risk_analysis')->count())
                ->color('warning')
                ->url(route('filament.dashboard.resources.temperature-deviations.reviewed')),
            Stat::make('Pending Acknowledged', TemperatureDeviation::where('is_acknowledged', false)->whereNotNull('length_temperature_deviation')->whereNotNull('risk_analysis')->count())
                ->color('info')
                ->url(route('filament.dashboard.resources.temperature-deviations.acknowledged')),        
            ];
    }
}
