<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['userId'] = Str::orderedUuid();
        $data['username'] = substr(strtolower(str_replace(' ', '.', $data['name'])), 0, 8);
        $data['initial'] = Str::upper($data['initial']);
        $data['password'] = Hash::make('Scm2025!');
        $data['password_change_required'] = $data['password_change_required'] ?? true;
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User created successfully')
            ->body('They will be required to change their password on first login.');
    }
}
