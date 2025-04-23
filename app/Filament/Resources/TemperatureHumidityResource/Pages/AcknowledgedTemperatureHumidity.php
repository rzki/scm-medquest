<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Filament\Tables\Table;
use App\Models\TemperatureHumidity;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\listRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\TemperatureHumidityResource;

class AcknowledgedTemperatureHumidity extends listRecords
{
    protected static string $resource = TemperatureHumidityResource::class;
    protected static ?string $title = 'Pending Acknowledgement';
    public function getBreadcrumb(): string
    {
        return 'Pending Acknowledgement'; // or any label you want
    }
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('date'))
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->searchable(),
                TextColumn::make('period')
                    ->label('Period')
                    ->formatStateUsing(fn ($record) => strtoupper(Carbon::parse($record->period)->format('M Y')))
                    ->searchable(),
                TextColumn::make('0800_data')
                    ->label('08:00')
                    ->getStateUsing(function ($record) {
                        $temp0800 = $record->temp_0800 ?? '-';
                        $time0800 = $record->time_0800 ? Carbon::parse($record->time_0800)->format('H:i') : '-';
                        $rh0800 = $record->rh_0800 ?? '-';
                        $pic0800 = $record->pic_0800 ?? '-';
                        return "Time: $time0800 <br> Temp: $temp0800 °C <br> Humidity: $rh0800% <br> PIC: $pic0800";
                    })->html(),
                TextColumn::make('1100_data')
                    ->label('11:00')
                    ->getStateUsing(function ($record) {
                        $temp1100 = $record->temp_1100 ?? '-';
                        $time1100 = $record->time_1100 ? Carbon::parse($record->time_1100)->format('H:i') : '-';
                        $rh1100 = $record->rh_1100 ?? '-';
                        $pic1100 = $record->pic_1100 ?? '-';
                        return "Time: $time1100 <br> Temp: $temp1100 °C <br> Humidity: $rh1100% <br> PIC: $pic1100";
                    })->html(),
                TextColumn::make('1400_data')
                    ->label('14:00')
                    ->getStateUsing(function ($record) {
                        $temp1400 = $record->temp_1400 ?? '-';
                        $time1400 = $record->time_1400 ? Carbon::parse($record->time_1400)->format('H:i') : '-';
                        $rh1400 = $record->rh_1400 ?? '-';
                        $pic1400 = $record->pic_1400 ?? '-';
                        return "Time: $time1400 <br> Temp: $temp1400 °C <br> Humidity: $rh1400% <br> PIC: $pic1400";
                    })->html(),
                    
                TextColumn::make('1700_data')
                    ->label('17:00')
                    ->getStateUsing(function ($record) {
                        $temp1700 = $record->temp_1700 ?? '-';
                        $time1700 = $record->time_1700 ? Carbon::parse($record->time_1700)->format('H:i') : '-';
                        $rh1700 = $record->rh_1700 ?? '-';
                        $pic1700 = $record->pic_1700 ?? '-';
                        return "Time: $time1700 <br> Temp: $temp1700 °C <br> Humidity: $rh1700% <br> PIC: $pic1700";
                    })->html(),
                TextColumn::make('reviewed_by')
                    ->label('Reviewed By')
                    ->searchable()
                    ->getStateUsing(function ($record){
                        return $record->reviewed_by ? $record->reviewed_by : '-';
                    }),
                TextColumn::make('acknowledged_by')
                    ->label('Acknowledged By')
                    ->searchable()
                    ->getStateUsing(function ($record){
                        return $record->acknowledged_by ? $record->acknowledged_by : '-';
                    }),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                Action::make('is_acknowledged')
                    ->label('Mark as Acknowledged')
                    ->visible(function (TemperatureHumidity $record) {
                        $isAcknowledged = $record->is_acknowledged == false;
                        $admin = Auth::user()->hasRole(['Super Admin', 'QA Manager']);
                        return $isAcknowledged && $admin;
                    })
                    ->action(function (TemperatureHumidity $record) {
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
                    ->icon('heroicon-o-check-circle'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('is_acknowledged')
                    ->label('Mark as Acknowledged')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(function (TemperatureHumidity $record) {
                        $isAcknowledged = $record->is_acknowledged == true;
                        $admin = Auth::user()->hasRole(['Super Admin', 'QA Manager']);
                        return !$isAcknowledged && $admin;
                    })
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->is_acknowledged = true;
                            $record->acknowledged_by = auth()->user()->initial . ' ' . strtoupper(now('Asia/Jakarta')->format('d M Y'));
                            $record->acknowledged_at = now('Asia/Jakarta');
                            $record->save();
                        }
                        
                    Notification::make()
                        ->title('Success!')
                        ->body('Selected data marked as reviewed successfully')
                        ->success()
                        ->send();
                    }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
