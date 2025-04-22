<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('location_name')
                    ->label('Location Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter location name'),
                TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter serial number'),
                TextInput::make('temperature_start')
                    ->label('Temperature Start')
                    ->required()
                    ->numeric()
                    ->maxLength(255)
                    ->placeholder('Enter temperature start'),
                TextInput::make('temperature_end')
                    ->label('Temperature End')
                    ->required()
                    ->numeric()
                    ->maxLength(255)
                    ->placeholder('Enter temperature end'),
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
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
