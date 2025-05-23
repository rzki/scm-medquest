<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use App\Models\Location;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\SerialNumber;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Models\TemperatureHumidity;
use Filament\Tables\Actions\Action;
use App\Models\TemperatureDeviation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use App\Exports\TemperatureDeviationExport;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Infolists\Components\Section as InfoSection;
use App\Filament\Resources\TemperatureDeviationResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;

class TemperatureDeviationResource extends Resource
{
    protected static ?string $model = TemperatureDeviation::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Temperature Deviation';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('temperature_humidity_id')
                    ->default(request()->get('temp_id')),
                Section::make('Date & Time')
                ->columns(2)
                ->schema([
                    DatePicker::make('date')
                        ->label('Date')
                        ->default(Carbon::now())
                        ->required(),
                    TimePicker::make('time')
                        ->label('Time')
                        ->seconds(false)
                        ->default(function () {
                            $time = request()->get('time');
                            return $time ? Carbon::createFromFormat('H:i', $time)->format('H:i') : null;
                        })
                        ->required(),
                ]),
                Section::make('Location & Storage Temperature Standards')
                    ->columns(3)
                    ->schema([
                        Hidden::make('temperature_humidity_id')
                        ->default(function () {
                            $humidity = TemperatureHumidity::query()
                                ->whereDate('created_at', now('Asia/Jakarta')->toDateString())
                                ->latest()
                                ->first();

                            return $humidity->id;
                        }),
                        Select::make('location_id')
                            ->label('Location')
                            ->relationship('location', 'location_name')
                            ->default(fn () => request()->get('location_id') ?? null)
                            ->required()
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                return "{$record->location_name}";
                            })
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
                            })
                            ->afterStateHydrated(function ($state, callable $set) {
                                // Load values when editing
                                $location = Location::find($state);

                                if ($location) {
                                    $set('observed_temperature', "{$location->temperature_start}°C to {$location->temperature_end}°C");
                                    $set('temperature_start', $location->temperature_start);
                                    $set('temperature_end', $location->temperature_end);
                                }
                            })
                            ->reactive(),
                        Select::make('sn_id')
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
                            ->default(fn () => request()->get('serial_number_id') ?? null)
                            ->preload()
                            ->required(),
                        TextInput::make('observed_temperature')
                            ->label('Storage Temperature Standards')
                            ->disabled()
                            ->dehydrated(false),
                        Hidden::make('temperature_start'),
                        Hidden::make('temperature_end')
                    ]),
                Section::make('Temperature Deviation & Reason (Filled by Staff / Security) ')
                ->columns(2)
                ->schema([
                    TextInput::make('temperature_deviation')
                        ->label('Temperature deviation (°C)')
                        ->required(Auth::user()->hasRole('Staff'))
                        ->default(fn() => request()->get('temperature_deviation'))
                        ->dehydrated(),
                    TextArea::make('deviation_reason')
                        ->label('Reason for deviation')
                        ->required(Auth::user()->hasRole('Staff'))
                        ->dehydrated(),
                ])->disabled(fn() => !Auth::user()->hasRole(['Supply Chain Officer', 'Security'])),
                Section::make('Length of Temperature Deviation & Risk Analysis (Filled by QA Staff / Supervisor)')
                ->columns(2)
                ->schema([
                    TextInput::make('length_temperature_deviation')
                        ->label('Length Temperature deviation (Minutes/Hours)')
                        ->required(Auth::user()->hasRole(['QA Staff', 'QA Supervisor']))
                        ->dehydrated(),
                    TextArea::make('risk_analysis')
                        ->label('Risk Analysis')
                        ->required(Auth::user()->hasRole(['QA Staff', 'QA Supervisor']))
                        ->dehydrated(),
                ])->disabled(fn() => !Auth::user()->hasAnyRole(['QA Staff', 'QA Supervisor'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('location.location_name')
                    ->label('Location / Serial Number')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->location->location_name . ' / ' . $record->serialNumber->serial_number;
                    }),
                TextColumn::make('date')
                    ->label('Date (Tanggal)')
                    ->sortable()
                    ->searchable()
                    ->date('d/m/Y'),
                TextColumn::make('time')
                    ->label('Time (Jam)')
                    ->sortable()
                    ->searchable()
                    ->time('H:i'),
                TextColumn::make('temperature_deviation')
                    ->label('Temperature Deviation (°C)'),
                TextColumn::make('length_temperature_deviation')
                    ->label('Length of Temperature Deviation (Menit/Jam)'),
                TextColumn::make('deviation_reason')
                    ->label('Reason for Deviation'),
                TextColumn::make('pic')
                    ->label('PIC (SCM)'),
                TextColumn::make('risk_analysis')
                    ->label('Risk Analysis of impact deviation'),
                TextColumn::make('analyzer_pic')
                    ->label('Analyzed by (QA)'),
                TextColumn::make('reviewed_by')
                    ->label('Reviewed by'),
                TextColumn::make('acknowledged_by')
                    ->label('Acknowledged by')
            ])
            ->filters([
                SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'location_name')
                    ->searchable()
                    ->preload(),
                Filter::make('period')
                    ->form([
                        DatePicker::make('period')
                            ->label('Period')
                            ->displayFormat('M Y')
                            ->native(false)
                            ->closeOnDateSelection()
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['period']) {
                            return $query;
                        }
                        
                        $date = Carbon::parse($data['period']);
                        return $query->whereMonth('date', $date->month)
                                   ->whereYear('date', $date->year);
                    })
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

                        $query = TemperatureDeviation::query()->where('location_id', $locationId);

                        if ($data['month_type'] === 'this_month') {
                            $month = now()->month;
                            $year = now()->year;
                        } else {
                            $chosenMonth = Carbon::parse($data['chosen_month']);
                            $month = $chosenMonth->month;
                            $year = $chosenMonth->year;
                        }

                        $records = $query->get();

                        $monthName = strtoupper(Carbon::createFromDate($year, $month)->format('M')); // e.g., "April"
                        $sluggedLocation = strtoupper(Str::slug($location->location_name, '_'));
                        $filename = "TemperatureDeviation_{$monthName}{$year}_{$sluggedLocation}.xlsx";

                        return Excel::download(new TemperatureDeviationExport($records), $filename);
                    })
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                ->visible(fn($record) => $record->date == now()->toDateString() && Auth::user()->hasRole(['Supply Chain Officer', 'QA Staff'])),
                DeleteAction::make()
                ->visible(fn($record) => $record->date == now()->toDateString() && Auth::user()->hasRole(['Supply Chain Officer', 'QA Staff'])),
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
                InfoSection::make('Date & Time')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('date')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextEntry::make('time')
                            ->label('Time')
                            ->time('H:i'),
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
                    ->columns(3)
                    ->schema([
                        TextEntry::make('location.location_name')
                            ->label('Location'),
                        TextEntry::make('serialNumber.serial_number')
                            ->label('Serial Number'),
                        TextEntry::make('location.temperature_start')
                            ->label('Storage Temperature Standards')
                            ->formatStateUsing(fn ($record) => $record->location->temperature_start.'°C to '.$record->location->temperature_end.'°C'),
                    ]),
                InfoSection::make('Temperature Deviation & Reason')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('temperature_deviation')
                            ->label('Temperature Deviation'),
                        TextEntry::make('deviation_reason')
                            ->label('Reason for Deviation'),
                    ]),
                InfoSection::make('Length of Temperature Deviation & Risk Analysis')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('length_temperature_deviation')
                            ->label('Length of Temperature Deviation'),
                        TextEntry::make('risk_analysis')
                            ->label('Risk Analysis'),
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
            'index' => Pages\ListTemperatureDeviations::route('/'),
            'create' => Pages\CreateTemperatureDeviation::route('/create'),
            'edit' => Pages\EditTemperatureDeviation::route('/{record}/edit'),
            'view' => Pages\ViewTemperatureDeviation::route('/view/{record}'),
            'reviewed' => Pages\ReviewedTemperatureDeviation::route('/reviewed'),
            'acknowledged' => Pages\AcknowledgedTemperatureDeviation::route('/acknowledged'),
        ];
    }
}
