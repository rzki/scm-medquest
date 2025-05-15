<?php

namespace App\Filament\Resources;

use App\Models\SerialNumber;
use Carbon\Carbon;
use App\Models\Location;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\TemperatureHumidity;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
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
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Actions\ActionGroup;
use pxlrbt\FilamentExcel\Columns\Column;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\TemperatureHumidityExport;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
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
                            ->relationship('location', 'location_name')
                            ->preload()
                            ->searchable()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $location = Location::find($state);
                                if ($location) {
                                    $formatted = "{$location->temperature_start}°C to {$location->temperature_end}°C";
                                    $set('observed_temperature', $formatted);
                                    $set('temperature_start', $location->temperature_start);
                                    $set('temperature_end', $location->temperature_end);
                                }
                                $set('serial_number_id', null);
                            })
                            ->afterStateHydrated(function ($state, callable $set) {
                                $location = Location::find($state);
                                if ($location) {
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
                        Select::make('serial_number_id')
                            ->label('Serial Number')
                            ->options(function (callable $get) {
                                $locationId = $get('location_id');

                                if (!$locationId) {
                                    return [];
                                }

                                return SerialNumber::where('location_id', $locationId)
                                    ->pluck('serial_number', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->disabled(fn (callable $get) => ! $get('location_id'))
                            ->preload()
                            ->required(),
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
                                    ->seconds(false)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('temp_0800')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->step(0.1)
                                    ->suffix('°C')
                                    ->maxValue(100)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('rh_0800')
                                    ->label('Humidity')
                                    ->suffix('%')
                                    ->numeric()
                                    ->maxValue(100),
                            ])->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '08:00' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '11:31'
                                )
                            ->dehydrated(),
                        Section::make('1100')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('time_1100')
                                    ->label('Time')
                                    ->seconds(false)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('temp_1100')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->suffix('°C')
                                    ->maxValue(100)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('rh_1100')
                                    ->label('Humidity')
                                    ->suffix('%')
                                    ->numeric()
                                    ->maxValue(100),
                            ])->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '11:31' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '14:31'
                                )
                                ->dehydrated(),
                        Section::make('1400')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('time_1400')
                                    ->label('Time')
                                    ->seconds(false)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('temp_1400')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->suffix('°C')
                                    ->maxValue(100)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('rh_1400')
                                    ->label('Humidity')
                                    ->suffix('%')
                                    ->numeric()
                                    ->maxValue(100),
                            ])->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '14:31' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '17:30'
                                )
                                ->dehydrated(),
                        Section::make('1700')
                            ->columns(3)
                            ->schema([
                                TimePicker::make('time_1700')
                                    ->label('Time')
                                    ->seconds(false)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('temp_1700')
                                    ->label('Temperature')
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->suffix('°C')
                                    ->maxValue(100)
                                    ->required(Auth::user()->hasRole('Supply Chain Officer')),
                                TextInput::make('rh_1700')
                                    ->label('Humidity')
                                    ->suffix('%')
                                    ->numeric()
                                    ->maxValue(100),
                            ])->disabled(fn () => 
                                    Carbon::now('Asia/Jakarta')->format('H:i') < '17:31' || 
                                    Carbon::now('Asia/Jakarta')->format('H:i') >= '19:30'
                                )
                                ->dehydrated(),
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->orderByDesc('date'))
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->formatStateUsing(fn($record) => Carbon::parse($record->date)->format('d'))
                    ->searchable(),
                TextColumn::make('period')
                    ->label('Period')
                    ->formatStateUsing(fn($record) => strtoupper(Carbon::parse($record->period)->format('M Y')))
                    ->searchable(),
                TextColumn::make('location.location_name')
                    ->label('Location')
                    ->getStateUsing(function ($record) {
                        return $record->location->location_name . ' / ' . $record->serialNumber->serial_number;
                    }),
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
                    ->getStateUsing(function ($record) {
                        return $record->reviewed_by ? $record->reviewed_by : '-';
                    }),
                TextColumn::make('acknowledged_by')
                    ->label('Acknowledged By')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->acknowledged_by ? $record->acknowledged_by : '-';
                    }),
            ])
            ->filters([
                SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'location_name')
                    ->searchable()
                    ->preload()
            ])
            ->headerActions([
                Action::make('custom_export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        Select::make('location_id')
                            ->label('Location')
                            ->options(Location::pluck('location_name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('month_type')
                            ->label('Month Type')
                            ->options([
                                'this_month' => 'This Month',
                                'choose' => 'Choose Month',
                            ])
                            ->default('this_month')
                            ->reactive(),

                        DatePicker::make('chosen_month')
                            ->label('Choose Month')
                            ->displayFormat('F Y')
                            ->visible(fn ($get) => $get('month_type') === 'choose')
                            ->required(fn ($get) => $get('month_type') === 'choose'),
                    ])
                    ->action(function (array $data) {
                        $locationId = $data['location_id'];
                        $location = Location::find($locationId);

                        $query = TemperatureHumidity::query()->where('location_id', $locationId);

                        if ($data['month_type'] === 'this_month') {
                            $month = now()->month;
                            $year = now()->year;
                        } else {
                            $chosenMonth = Carbon::parse($data['chosen_month']);
                            $month = $chosenMonth->month;
                            $year = $chosenMonth->year;
                        }

                        $query->whereMonth('period', $month)->whereYear('period', $year);

                        $records = $query->get();

                        $monthName = strtoupper(Carbon::createFromDate($year, $month)->format('M')); // e.g., "April"
                        $sluggedLocation = strtoupper(Str::slug($location->location_name, '_'));
                        $filename = "TemperatureHumidity_{$monthName}{$year}_{$sluggedLocation}.xlsx";

                        return Excel::download(new TemperatureHumidityExport($records), $filename);
                    })
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                ->visible(fn($record) => $record->date == now()->toDateString() && Auth::user()->hasRole('Supply Chain Officer')),
                DeleteAction::make()
                ->visible(fn($record) => $record->date == now()->toDateString() && Auth::user()->hasRole('Supply Chain Officer')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
                        ->columns(4)
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
                            TextEntry::make('pic_0800')
                                ->label('PIC')
                        ]),
                        InfoSection::make('11:00')
                        ->columns(4)
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
                            TextEntry::make('pic_1100')
                                ->label('PIC')
                        ]),
                        InfoSection::make('14:00')
                        ->columns(4)
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
                            TextEntry::make('pic_1400')
                                ->label('PIC')
                        ]),
                        InfoSection::make('17:00')
                        ->columns(4)
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
                            TextEntry::make('pic_1700')
                                ->label('PIC')
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
