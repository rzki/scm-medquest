<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use App\Models\SerialNumber;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\SerialNumberResource\Pages;

class SerialNumberResource extends Resource
{
    protected static ?string $model = SerialNumber::class;

    protected static ?string $navigationGroup = 'Location & Serial Number';
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['Super Admin', 'Admin']);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('location_id')
                    ->relationship('locations', 'location_name')
                    ->label('Location')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('serial_number')
                    ->label('Serial Number')
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->orderByDesc('created_at'))
            ->columns([
                TextColumn::make('locations.location_name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->sortable()
                    ->searchable(),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListSerialNumbers::route('/'),
            'create' => Pages\CreateSerialNumber::route('/create'),
            'edit' => Pages\EditSerialNumber::route('/{record}/edit'),
        ];
    }
}
