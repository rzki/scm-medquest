<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\TemperatureDeviation;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

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
                Section::make('Temperature Range')
                ->schema([
                    Radio::make('observed_temperature')
                        ->label('Observed Temperature')
                        ->options([
                            '15|30' => '15°C to 30°C',
                            '15|25' => '15°C to 25°C',
                            '2|8' => '2°C to 8°C',
                            '-35|-15' => '-35°C to -15°C',
                            '-25|-10' => '-25°C to -10°C',
                        ])
                        ->formatStateUsing(function () {
                            $tempRange = request()->get('temp_range');
                            if ($tempRange && str_contains($tempRange, '|')) {
                                return [$tempRange];
                            }
                            return [];
                        })
                        ->columns(3),
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
                ]),
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
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        ];
    }
}
