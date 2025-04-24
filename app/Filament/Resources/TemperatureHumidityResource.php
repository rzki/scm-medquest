<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Location;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\TemperatureHumidity;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Infolists\Components\Section as InfoSection;
use App\Filament\Resources\TemperatureHumidityResource\Pages;

class TemperatureHumidityResource extends Resource
{
    protected static ?string $model = TemperatureHumidity::class;
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'All';
    protected static ?string $navigationGroup = 'Temperature & Humidity';
    protected static bool $shouldRegisterNavigation = false;
    // public static function canCreate(): bool
    // {
    //     return !TemperatureHumidity::whereDate('created_at', Carbon::today())->exists();
    // }
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
                Section::make('Location & Storage Temperature Standards')
                    ->columns(3)
                    ->schema([
                        Select::make('location_id')
                            ->label('Location')
                            ->relationship('location')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                return "{$record->location_name} / {$record->serial_number}";
                            })
                            ->preload()
                            ->searchable()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $location = Location::find($state);
                                if ($location) {
                                    $set('serial_number', $location->serial_number); // set the serial_number field
                                    $formatted = "{$location->temperature_start}°C to {$location->temperature_end}°C";
                                    $set('observed_temperature', $formatted);
                                    $set('temperature_start', $location->temperature_start);
                                    $set('temperature_end', $location->temperature_end);
                                }
                            })
                            ->afterStateHydrated(function ($state, callable $set) {
                                // Load values when editing
                                $location = Location::find($state);

                                if ($location) {
                                    $set('serial_number', $location->serial_number);
                                    $set('observed_temperature', "{$location->temperature_start}°C to {$location->temperature_end}°C");
                                    $set('temperature_start', $location->temperature_start);
                                    $set('temperature_end', $location->temperature_end);
                                }
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $exists = TemperatureHumidity::where('location_id', $state)
                                    ->whereDate('date', Carbon::today())
                                    ->exists();

                                if ($exists) {
                                    Notification::make()
                                        ->title('⚠️ A record for this location already exists today.')
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->required(),
                        TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->required()
                            ->disabled(),
                        TextInput::make('observed_temperature')
                            ->label('Storage Temperature Standards')
                            ->disabled()
                            ->dehydrated(false),
                        Hidden::make('temperature_start'),
                        Hidden::make('temperature_end')
                    ]),
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
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '11:31'
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
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '11:31' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '14:31'
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
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '14:31' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '17:30'
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
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '17:31' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '19:30'
                                ),
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('date')->where('is_reviewed', false)->orWhere('is_acknowledged', false))
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
                EditAction::make(),
                Action::make('is_reviewed')
                    ->label('Mark as Reviewed')
                    ->visible(fn () => Auth::user()->hasRole(['Supply Chain Manager']))
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
                    ->visible(fn () => Auth::user()->hasRole(['QA Manager']))
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
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('is_reviewed')
                    ->label('Mark as Reviewed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn () => Auth::user()->hasRole(['Supply Chain Manager']))
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->is_reviewed = true;
                            $record->reviewed_by = auth()->user()->initial . ' ' . strtoupper(now('Asia/Jakarta')->format('d M Y'));
                            $record->reviewed_at = now('Asia/Jakarta');
                            $record->save();
                        }
                        
                    Notification::make()
                        ->title('Success!')
                        ->body('Selected data marked as reviewed successfully')
                        ->success()
                        ->send();
                    }),
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
                            ->formatStateUsing(fn ($record) => $record->location->location_name.' / '.$record->location->serial_number),
                        TextEntry::make('location.temperature_start')
                            ->label('Storage Temperature Standards')
                            ->formatStateUsing(fn ($record) => $record->location->temperature_start.'°C to '.$record->location->temperature_end.'°C'),
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
            'index' => Pages\ListTemperatureHumidities::route('/all'),
            'create' => Pages\CreateTemperatureHumidity::route('/create'),
            'edit' => Pages\EditTemperatureHumidity::route('/{record}/edit'),
            'view' => Pages\ViewTemperatureHumidity::route('/view/{record}'),
            'reviewed' => Pages\ReviewedTempHumidity::route('/reviewed'),
            'acknowledged' => Pages\AcknowledgedTemperatureHumidity::route('/acknowledged'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make()
                ->label('All')
                ->url(fn()=>TemperatureHumidityResource::getUrl('index'))
                ->isActiveWhen(fn() => !request()->routeIs('filament.dashboard.resources.temperature-humidities.reviewed'))
                ->group('Temperature & Humidity')
                ->sort(0),
            NavigationItem::make()
                ->label('Reviewed')
                ->isActiveWhen(fn()=> request()->routeIs('filament.dashboard.resources.temperature-humidities.reviewed'))
                ->sort(1),
            NavigationItem::make()
                ->label('Acknowledged')
                ->isActiveWhen(fn()=> request()->routeIs('filament.dashboard.resources.temperature-humidities.acknowledged'))
                ->sort(1),
        ];
    }
}
