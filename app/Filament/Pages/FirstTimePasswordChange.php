<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Illuminate\Validation\Rules\Password;

class FirstTimePasswordChange extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static string $view = 'filament.pages.first-time-password-change';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ]);
    }

    public function getTitle(): string
    {
        return 'Change Password Required';
    }

    public function getSubheading(): ?string
    {
        return 'Welcome! You must change your password before continuing to use the system.';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Welcome!')
                    ->description('For security reasons, you must change your password before accessing the system.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Set New Password')
                    ->description('Please choose a strong password for your account.')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->confirmed()
                            ->revealable()
                            ->helperText('Your password must be at least 8 characters long.')
                            ->validationAttribute('password'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->dehydrated(false),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('changePassword')
                ->label('Change Password & Continue')
                ->submit('changePassword')
                ->color('primary'),
        ];
    }

    public function changePassword(): void
    {
        try {
            $data = $this->form->getState();
            
            $user = Auth::user();
            
            // Update password
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
            
            // Mark password as changed
            $user->markPasswordAsChanged();
            
            // Clear the session flag
            session()->forget('password_change_required');

            // Update session password hash
            if (request()->hasSession()) {
                request()->session()->put([
                    'password_hash_web' => $data['password'],
                ]);
            }

            Notification::make()
                ->success()
                ->title('Password Changed Successfully')
                ->body('Your password has been updated. You can now access the system.')
                ->send();

            // Redirect to dashboard
            $this->redirect('/', navigate: false);
            
        } catch (Halt $exception) {
            return;
        }
    }

    public static function getSlug(): string
    {
        return 'first-time-password-change';
    }

    public static function getRouteName(?string $panel = null): string
    {
        return 'filament.dashboard.pages.first-time-password-change';
    }
}
