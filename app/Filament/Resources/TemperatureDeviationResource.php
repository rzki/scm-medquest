<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Location;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\SerialNumber;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use App\Models\TemperatureHumidity;
use Filament\Tables\Actions\Action;
use App\Models\TemperatureDeviation;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\TemperatureDeviationExport;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TemperatureDeviationResource\Pages;
use App\Filament\Resources\TemperatureDeviationResource\RelationManagers;

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
                            ->preload()
                            ->required(),
                        TextInput::make('observed_temperature')
                            ->label('Storage Temperature Standards')
                            ->disabled()
                            ->dehydrated(false),
                        Hidden::make('temperature_start'),
                        Hidden::make('temperature_end')
                    ]),
                Section::make('Temperature Deviation & Reason (Filled by Staff) ')
                ->columns(2)
                ->schema([
                    TextInput::make('temperature_deviation')
                        ->label('Temperature deviation (°C)')
                        ->required(Auth::user()->hasRole('Staff'))
                        ->default(fn() => request()->get('temperature_deviation'))
                        ->dehydrated(true),
                    TextArea::make('deviation_reason')
                        ->label('Reason for deviation')
                        ->required(Auth::user()->hasRole('Staff')),
                ])->disabled(fn() => !Auth::user()->hasRole('Supply Chain Officer')),
                Section::make('Length of Temperature Deviation & Risk Analysis (Filled by QA Staff / Supervisor)')
                ->columns(2)
                ->schema([
                    TextInput::make('length_temperature_deviation')
                        ->label('Length Temperature deviation (Minutes/Hours)')
                        ->required(Auth::user()->hasRole(['QA Staff', 'QA Supervisor'])),
                    TextArea::make('risk_analysis')
                        ->label('Risk Analysis')
                        ->required(Auth::user()->hasRole(['QA Staff', 'QA Supervisor'])),
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
                Action::make('is_reviewed')
                    ->label('Mark as Reviewed')
                    ->visible(function (TemperatureDeviation $record) {
                        $isReviewed = $record->is_reviewed == false;
                        $admin = Auth::user()->hasRole(['Super Admin', 'Supply Chain Manager']);
                        return $isReviewed && $admin;
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
                    ->visible(function (TemperatureDeviation $record) {
                        $isAcknowledged = $record->is_acknowledged == false;
                        $admin = Auth::user()->hasRole(['Super Admin', 'QA Manager']);
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('is_reviewed')
                    ->label('Mark as Reviewed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn() => Auth::user()->hasRole(['Supply Chain Manager']))
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
                BulkAction::make('is_acknowledged')
                    ->label('Mark as Acknowledged')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn() => Auth::user()->hasRole(['QA Manager']))
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->is_acknowledged = true;
                            $record->acknowledged_by = auth()->user()->initial . ' ' . strtoupper(now('Asia/Jakarta')->format('d M Y'));
                            $record->acknowledged_at = now('Asia/Jakarta');
                            $record->save();
                        }
                        
                        Notification::make()
                            ->title('Success!')
                            ->body('Selected data marked as acknowledged successfully')
                            ->success()
                            ->send();
                    }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Date & Time')
                    ->columns(2)
                    ->schema([
                        TextColumn::make('date')
                            ->label('Date')
                            ->date('d/m/Y'),
                        TextColumn::make('time')
                            ->label('Time')
                            ->time('H:i'),
                    ]),
                Section::make('Location & Storage Temperature Standards')
                    ->columns(3)
                    ->schema([
                        TextColumn::make('location.location_name')
                            ->label('Location'),
                        TextColumn::make('location.serial_number')
                            ->label('Serial Number'),
                        TextColumn::make('location.temperature_start')
                            ->label('Storage Temperature Standards'),
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
            'reviewed' => Pages\ReviewedTemperatureDeviation::route('/reviewed'),
            'acknowledged' => Pages\AcknowledgedTemperatureDeviation::route('/acknowledged'),
        ];
    }
}
