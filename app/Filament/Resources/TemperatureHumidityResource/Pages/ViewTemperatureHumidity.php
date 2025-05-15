<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Models\TemperatureHumidity;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\TemperatureHumidityResource;

class ViewTemperatureHumidity extends ViewRecord
{
    protected static string $resource = TemperatureHumidityResource::class;
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => Auth::user()->hasRole('Supply Chain Officer')),
            Action::make('is_reviewed')
                ->label('Mark as Reviewed')
                    ->visible(function (TemperatureHumidity $record) {
                        $isAcknowledged = $record->is_acknowledged == false && $record->time_0800 != null && $record->time_1100 != null && $record->time_1400 != null && $record->time_1700 != null && $record->temp_0800 != null && $record->temp_1100 != null && $record->temp_1400 != null && $record->temp_1700 != null;    
                        $admin = Auth::user()->hasRole(['Supply Chain Manager']);
                        return $isAcknowledged && $admin;
                    })
                ->action(function (Model $record) {
                    $record->update([
                        'is_reviewed' => true,
                        'reviewed_by' => auth()->user()->initial . ' ' . strtoupper(now('Asia/Jakarta')->format('d M Y')),
                        'reviewed_at' => now('Asia/Jakarta'),
                    ]);
                Notification::make()
                    ->title('Success!')
                    ->body('Marked as reviewed successfully by Supply Chain Manager.')
                    ->success()
                    ->send();
                })
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check'),
            Action::make('is_acknowledged')
                ->label('Mark as Acknowledged')
                    ->visible(function (TemperatureHumidity $record) {
                        $isAcknowledged = $record->is_acknowledged == false && $record->time_0800 != null && $record->time_1100 != null && $record->time_1400 != null && $record->time_1700 != null && $record->temp_0800 != null && $record->temp_1100 != null && $record->temp_1400 != null && $record->temp_1700 != null;    
                        $admin = Auth::user()->hasRole(['QA Manager']);
                        return $isAcknowledged && $admin;
                    })
                ->action(function (Model $record) {
                    $record->update([
                        'is_acknowledged' => true,
                        'acknowledged_by' => auth()->user()->initial . ' ' . strtoupper(now('Asia/Jakarta')->format('d M Y')),
                        'acknowledged_at' => now('Asia/Jakarta'),
                    ]);
                Notification::make()
                    ->title('Success!')
                    ->body('Marked as acknowledged successfully by QA Manager.')
                    ->success()
                    ->send();
                })
                ->requiresConfirmation()
                ->color('info')
                ->icon('heroicon-o-check'),
        ];
    }
}
