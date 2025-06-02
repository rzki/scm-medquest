<?php

namespace App\Filament\Resources\TemperatureHumidityResource\Pages;

use Carbon\Carbon;
use Filament\Tables\Table;
use App\Models\TemperatureHumidity;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;     
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\TemperatureHumidityResource;

class ReviewedTempHumidity extends ListRecords
{
    protected static string $resource = TemperatureHumidityResource::class;
    protected static ?string $title = 'Pending Review';
    public function getBreadcrumb(): string
    {
        return 'Pending Review'; // or any label you want
    }
    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('date')->where('is_reviewed', false)
            ->whereNotNull('time_0800')->whereNotNull('time_1100')->whereNotNull('time_1400')->whereNotNull('time_1700')
            ->whereNotNull('time_2000')->whereNotNull('time_2300')->whereNotNull('time_0200')->whereNotNull('time_0500')
            ->whereNotNull('temp_0800')->whereNotNull('temp_1100')->whereNotNull('temp_1400')->whereNotNull('temp_1700')
            ->whereNotNull('temp_2000')->whereNotNull('temp_2300')->whereNotNull('temp_0200')->whereNotNull('temp_0500')
            ->whereNotNull('rh_0800')->whereNotNull('rh_1100')->whereNotNull('rh_1400')->whereNotNull('rh_1700')->whereNotNull('rh_2000')->whereNotNull('rh_2300')->whereNotNull('rh_0200')->whereNotNull('rh_0500'))
            ->emptyStateHeading('No pending review data is found')
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
                TextColumn::make('2000_data')
                    ->label('20:00')
                    ->getStateUsing(function ($record) {
                        $temp2000 = $record->temp_2000 ?? '-';
                        $time2000 = $record->time_2000 ? Carbon::parse($record->time_2000)->format('H:i') : '-';
                        $rh2000 = $record->rh_2000 ?? '-';
                        $pic2000 = $record->pic_2000 ?? '-';
                        return "Time: $time2000 <br> Temp: $temp2000 °C <br> Humidity: $rh2000% <br> PIC: $pic2000";
                    })->html(),
                TextColumn::make('2300_data')
                    ->label('23:00')
                    ->getStateUsing(function ($record) {
                        $temp2300 = $record->temp_2300 ?? '-';
                        $time2300 = $record->time_2300 ? Carbon::parse($record->time_2300)->format('H:i') : '-';
                        $rh2300 = $record->rh_2300 ?? '-';
                        $pic2300 = $record->pic_2300 ?? '-';
                        return "Time: $time2300 <br> Temp: $temp2300 °C <br> Humidity: $rh2300% <br> PIC: $pic2300";
                    })->html(),
                TextColumn::make('0200_data')
                    ->label('02:00')
                    ->getStateUsing(function ($record) {
                        $temp0200 = $record->temp_0200 ?? '-';
                        $time0200 = $record->time_0200 ? Carbon::parse($record->time_0200)->format('H:i') : '-';
                        $rh0200 = $record->rh_0200 ?? '-';
                        $pic0200 = $record->pic_0200 ?? '-';
                        return "Time: $time0200 <br> Temp: $temp0200 °C <br> Humidity: $rh0200% <br> PIC: $pic0200";
                    })->html(),
                TextColumn::make('0500_data')
                    ->label('05:00')
                    ->getStateUsing(function ($record) {
                        $temp0500 = $record->temp_0500 ?? '-';
                        $time0500 = $record->time_0500 ? Carbon::parse($record->time_0500)->format('H:i') : '-';
                        $rh0500 = $record->rh_0500 ?? '-';
                        $pic0500 = $record->pic_0500 ?? '-';
                        return "Time: $time0500 <br> Temp: $temp0500 °C <br> Humidity: $rh0500% <br> PIC: $pic0500";
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
                Action::make('is_reviewed')
                    ->label('Mark as Reviewed')
                    ->visible(function (TemperatureHumidity $record) {
                        $isAcknowledged = $record->is_acknowledged == false && $record->time_0800 != null && $record->time_1100 != null && $record->time_1400 != null && $record->time_1700 != null && $record->temp_0800 != null && $record->temp_1100 != null && $record->temp_1400 != null && $record->temp_1700 != null;    
                        $admin = Auth::user()->hasRole('Supply Chain Manager');
                        return $isAcknowledged && $admin;
                    })
                    ->action(function (TemperatureHumidity $record) {
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
                    ->icon('heroicon-o-check-circle'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('is_reviewed')
                    ->label('Mark as Reviewed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function (TemperatureHumidity $record) {
                        $isAcknowledged = $record->is_acknowledged == false && $record->time_0800 != null && $record->time_1100 != null && $record->time_1400 != null && $record->time_1700 != null && $record->temp_0800 != null && $record->temp_1100 != null && $record->temp_1400 != null && $record->temp_1700 != null;    
                        $admin = Auth::user()->hasRole('Supply Chain Manager');
                        return $isAcknowledged && $admin;
                    })
                    ->action(function (Collection $records) {
                        $alreadyReviewed = $records->every(fn ($record) => $record->is_reviewed);

                        if ($alreadyReviewed) {
                            Notification::make()
                                ->title('All selected records are already reviewed.')
                                ->warning()
                                ->send();

                            return;
                        }
                        foreach ($records as $record) {
                            if (! $record->is_reviewed) {
                                $record->is_reviewed = true;
                                $record->reviewed_by = auth()->user()->initial . ' ' . strtoupper(now('Asia/Jakarta')->format('d M Y'));
                                $record->reviewed_at = now('Asia/Jakarta');
                                $record->save();
                            }
                        }
                        
                        Notification::make()
                            ->title('Success!')
                            ->body('Selected data marked as reviewed successfully')
                            ->success()
                            ->send();
                        }),

                ]),
            ]);
    }
}
