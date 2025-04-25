<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    public function getTitle(): string
    {
        return 'Edit Profile';
    }
    public function getRedirectUrl(): string
    {
        return '/';
    }
    public function getSavedNotificationTitle(): string
    {
        return 'Profile updated successfully';
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent()
            ]);
    }
}
