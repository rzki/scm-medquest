<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Set default password if not provided
                        if (empty($data['password'])) {
                            $data['password'] = Hash::make('Scm2025!');
                        }
                        // Set default password change requirement for new users
                        $data['password_change_required'] = $data['password_change_required'] ?? true;
                        return $data;
                    }),
        ];
    }
}
