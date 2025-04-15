<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\TemperatureHumidity;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Forms\Components\CheckboxList;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as InfoSection;
use App\Filament\Resources\TemperatureHumidityResource\Pages;

class TemperatureHumidityResource extends Resource
{
    protected static ?string $model = TemperatureHumidity::class;
    protected static ?int $navigationSort = 0;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function canCreate(): bool
    {
        return !TemperatureHumidity::whereDate('created_at', Carbon::today())->exists();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Date & Period')
                ->columns(2)
                ->schema([
                    DatePicker::make('date')
                        ->label('Date')
                        ->default(Carbon::now())
                        ->required(),
                    DatePicker::make('period')
                        ->label('Period')
                        ->native(false)
                        ->displayFormat('M Y')
                        ->default(Carbon::now())
                        ->required(),   
                ]),
                Radio::make('observed_temperature')
                    ->label('Storage Temperature')
                    ->options([
                        '15|30' => '15°C to 30°C',
                        '15|25' => '15°C to 25°C',
                        '2|8' => '2°C to 8°C',
                        '-35|-15' => '-35°C to -15°C',
                        '-25|-10' => '-25°C to -10°C',
                    ])
                    ->formatStateUsing(function ($record) {
                        if ($record && $record->observed_temperature_start && $record->observed_temperature_end) {
                            return [$record->observed_temperature_start . '|' . $record->observed_temperature_end];
                        }

                        return [];
                    })
                    ->columns(3),
                Section::make('Time')
                    ->columns(3)
                    ->schema([
                        Section::make('0800')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('time_0800')
                                    ->label('Time')
                                    ->seconds(false),
                                TextInput::make('temp_0800')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step(0.1)
                                    ->suffix('°C'),
                                TextInput::make('rh_0800')
                                    ->label('Humidity')
                                    ->suffix('%'),
                            ])
                            ->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '08:00' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') > '10:59'
                                ),
                        Section::make('1100')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('time_1100')
                                    ->label('Time')
                                    ->seconds(false),
                                TextInput::make('temp_1100')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->suffix('°C'),
                                TextInput::make('rh_1100')
                                    ->label('Humidity')
                                    ->suffix('%'),
                            ])->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '11:00' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') > '13:59'
                                ),
                        Section::make('1400')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('time_1400')
                                    ->label('Time')
                                    ->seconds(false),
                                TextInput::make('temp_1400')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->suffix('°C'),
                                TextInput::make('rh_1400')
                                    ->label('Humidity')
                                    ->suffix('%'),
                            ])->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '14:00' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') > '16:59'
                                ),
                        Section::make('1700')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('time_1700')
                                    ->label('Time')
                                    ->seconds(false),
                                TextInput::make('temp_1700')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->suffix('°C'),
                                TextInput::make('rh_1700')
                                    ->label('Humidity')
                                    ->suffix('%'),
                            ])->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '17:00' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') > '07:59'
                                ),
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->searchable(),
                TextColumn::make('period')
                    ->label('Period')
                    ->formatStateUsing(fn ($record) => strtoupper(Carbon::parse($record->period)->format('M Y')))
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Location / Serial No.')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->location.' / '.$record->serial_no),
                TextColumn::make('storage_temps')
                    ->label('Storage Temps')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->observed_temperature_start.'°C to '.$record->observed_temperature_end.'°C'),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make('Date & Period')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('date')
                            ->label('Date')
                            ->formatStateUsing(fn ($record) => Carbon::parse($record->date)->format('d/m/Y')),
                        TextEntry::make('period')
                            ->label('Period')
                            ->formatStateUsing(fn ($record) => strtoupper(Carbon::parse($record->period)->format('M Y'))),
                    ]),
                InfoSection::make('Reviewed & Acknowledged')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reviewed_by')
                            ->label('Reviewed By')
                            ->formatStateUsing(fn ($record) => $record->reviewed_by ? $record->reviewed_by : '-'),
                        TextEntry::make('acknowledged_by')
                            ->label('Acknowledged By')
                            ->formatStateUsing(fn ($record) => $record->acknowledged_by ? $record->acknowledged_by : '-'),
                    ]),
                InfoSection::make('Location & Storage Temperature Standards')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('location')
                            ->label('Location')
                            ->formatStateUsing(fn ($record) => $record->location.' / '.$record->serial_no),
                        TextEntry::make('observed_temperature_start')
                            ->label('Storage Temperature Standards')
                            ->formatStateUsing(fn ($record) => $record->observed_temperature_start.'°C to '.$record->observed_temperature_end.'°C'),
                    ]),
                InfoSection::make('Time Range')
                    ->columns(2)
                    ->schema([
                        InfoSection::make('08:00')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('time_0800')
                                ->label('Time')
                                ->formatStateUsing(fn ($record) => $record->time_0800 ? Carbon::parse($record->time_0800)->format('H:i') : '-'),
                            TextEntry::make('temp_0800')
                                ->label('Temperature')
                                ->formatStateUsing(fn ($record) => $record->temp_0800.' °C' ?? '-'),
                            TextEntry::make('rh_0800')
                                ->label('Humidity')
                                ->formatStateUsing(fn ($record) => $record->rh_0800.'%' ?? '-'),
                        ]),
                        InfoSection::make('11:00')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('time_1100')
                                ->label('Time')
                                ->formatStateUsing(fn ($record) => $record->time_1100 ? Carbon::parse($record->time_1100)->format('H:i') : '-'),
                            TextEntry::make('temp_1100')
                                ->label('Temperature')
                                ->formatStateUsing(fn ($record) => $record->temp_1100.' °C' ?? '-'),
                            TextEntry::make('rh_1100')
                                ->label('Humidity')
                                ->formatStateUsing(fn ($record) => $record->rh_1100.'%' ?? '-'),
                        ]),
                        InfoSection::make('14:00')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('time_1400')
                                ->label('Time')
                                ->formatStateUsing(fn ($record) => $record->time_1400 ? Carbon::parse($record->time_1400)->format('H:i') : '-'),
                            TextEntry::make('temp_1400')
                                ->label('Temperature')
                                ->formatStateUsing(fn ($record) => $record->temp_1400.' °C' ?? '-'),
                            TextEntry::make('rh_1400')
                                ->label('Humidity')
                                ->formatStateUsing(fn ($record) => $record->rh_1400.'%' ?? '-'),
                        ]),
                        InfoSection::make('17:00')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('time_1700')
                                ->label('Time')
                                ->formatStateUsing(fn ($record) => $record->time_1700 ? Carbon::parse($record->time_1700)->format('H:i') : '-'),
                            TextEntry::make('temp_1700')
                                ->label('Temperature')
                                ->formatStateUsing(fn ($record) => $record->temp_1700.' °C' ?? '-'),
                            TextEntry::make('rh_1700')
                                ->label('Humidity')
                                ->formatStateUsing(fn ($record) => $record->rh_1700.'%' ?? '-'),
                        ]),
                    ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemperatureHumidities::route('/'),
            'create' => Pages\CreateTemperatureHumidity::route('/create'),
            'edit' => Pages\EditTemperatureHumidity::route('/{record}/edit'),
            'view' => Pages\ViewTemperatureHumidity::route('/view/{record}'),
        ];
    }
}
