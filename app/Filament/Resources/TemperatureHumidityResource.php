<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
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
                    ->label('Observed Temperature')
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
                            ]),
                            // ->disabled(fn () => 
                            //         Carbon::now('Asia/Jakarta')->format('H:i') < '08:00' || 
                            //         Carbon::now('Asia/Jakarta')->format('H:i') > '11:00'
                            //     ),
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
                                    Carbon::now('Asia/Jakarta')->format('H:i') > '14:00'
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
                                    Carbon::now('Asia/Jakarta')->format('H:i') > '17:00'
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
                                    Carbon::now('Asia/Jakarta')->format('H:i') > '08:00'
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
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Location / Serial No.')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->location.' / '.$record->serial_no),
                TextColumn::make('observed_temperature_start')
                    ->label('Observed Temperature')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->observed_temperature_start.'°C to '.$record->observed_temperature_end. '°C'),
                TextColumn::make('readings_summary')
                    ->label('Temperature & Humidity Readings')
                    ->formatStateUsing(function ($record) {
                        $stack = [];

                        $slots = [
                            '08:00' => ['time' => 'time_0800', 'temp' => 'temp_0800', 'rh' => 'rh_0800'],
                            '11:00' => ['time' => 'time_1100', 'temp' => 'temp_1100', 'rh' => 'rh_1100'],
                            '14:00' => ['time' => 'time_1400', 'temp' => 'temp_1400', 'rh' => 'rh_1400'],
                            '17:00' => ['time' => 'time_1700', 'temp' => 'temp_1700', 'rh' => 'rh_1700'],
                        ];

                        foreach ($slots as $label => $fields) {
                            $time = $record->{$fields['time']} ?? '-';
                            $temp = $record->{$fields['temp']} ?? '-';
                            $rh   = $record->{$fields['rh']} ?? '-';
                            $stack[] = "$label ($time) → Temp: $temp °C | RH: $rh%";
                        }

                        return implode("\n", $stack);
                    })
                    ->wrap()
                // Stack::make([
                //         TextColumn::make('time_0800')
                //             ->label('Time')
                //             ->formatStateUsing(fn ($record) => $record->time_0800 ? Carbon::parse($record->time_0800)->format('H:i') : '-'),
                //         TextColumn::make('temp_0800')
                //             ->label('Temperature')
                //             ->suffix('°C'),
                //         TextColumn::make('rh_0800')
                //             ->label('Humidity')
                //             ->suffix('%'),
                //     ]),
                // Stack::make([
                //         TextColumn::make('time_1100')
                //             ->formatStateUsing(fn ($record) => $record->time_1100 ? Carbon::parse($record->time_1100)->format('H:i') : '-'),
                //         TextColumn::make('temp_1100')
                //             ->suffix('°C'),
                //         TextColumn::make('rh_1100')
                //             ->suffix('%'),
                //     ]),
                // Stack::make([
                //         TextColumn::make('time_1400')
                //             ->formatStateUsing(fn ($record) => $record->time_1400 ? Carbon::parse($record->time_1400)->format('H:i') : '-'),
                //         TextColumn::make('temp_1400')
                //             ->suffix('°C'),
                //         TextColumn::make('rh_1400')
                //             ->suffix('%'),
                //     ]),
                // Stack::make([
                //         TextColumn::make('time_1700')
                //             ->formatStateUsing(fn ($record) => $record->time_1700 ? Carbon::parse($record->time_1700)->format('H:i') : '-'),
                //         TextColumn::make('temp_1700')
                //             ->suffix('°C'),
                //         TextColumn::make('rh_1700')
                //             ->suffix('%'),
                //     ]),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
        ];
    }
}
