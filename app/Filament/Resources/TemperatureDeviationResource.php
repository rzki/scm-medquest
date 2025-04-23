<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Location;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use App\Models\TemperatureHumidity;
use App\Models\TemperatureDeviation;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
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
                Hidden::make('temperature_id')
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
                            ->relationship('location')
                            ->default(fn () => request()->get('location_id') ?? null)
                            ->required()
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
                Section::make('Temperature Deviation & Reason (Filled by Staff) ')
                ->columns(2)
                ->schema([
                    TextInput::make('temperature_deviation')
                        ->label('Temperature deviation (°C)')
                        ->required(Auth::user()->hasRole('Staff'))
                        ->default(fn() => request()->get('temperature_deviation')),
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
                ])->disabled(fn() => !Auth::user()->hasRole(['QA Staff', 'QA Supervisor'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListTemperatureDeviations::route('/'),
            'create' => Pages\CreateTemperatureDeviation::route('/create'),
            'edit' => Pages\EditTemperatureDeviation::route('/{record}/edit'),
            'reviewed' => Pages\ReviewedTemperatureDeviation::route('/reviewed'),
            'acknowledged' => Pages\AcknowledgedTemperatureDeviation::route('/acknowledged'),
        ];
    }
}
