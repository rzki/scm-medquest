<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use Filament\Enums\ThemeMode;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use App\Filament\Pages\EditProfile;
use Illuminate\Support\Facades\Auth;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\TemperatureHumidityResource;
use App\Filament\Resources\TemperatureDeviationResource;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Resources\TemperatureHumidityResource\Pages\ReviewedTempHumidity;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('/')
            ->login(Login::class)
            ->profile(EditProfile::class)
            ->spa()
            ->brandLogo(asset('assets/images/LOGO-MEDQUEST-HD.png'))
            ->brandLogoHeight('2rem')
            ->defaultThemeMode(ThemeMode::Light)
            ->favicon(asset('assets/images/Medquest-Favicon.png'))
            ->databaseNotifications()
            ->plugins([
                FilamentSpatieRolesPermissionsPlugin::make()
            ])
            ->colors([
                'primary' => Color::Blue,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn() => Auth::user()->name)
                    ->url(fn() => EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
            ])
            ->navigationItems([
                // Temperature & Humidity
                NavigationItem::make('All')
                    ->label('All')
                    ->url(fn() => TemperatureHumidityResource::getUrl('index'))
                    ->group('Temperature & Humidity')
                    ->isActiveWhen(fn () => request()->routeIs('filament.dashboard.resources.temperature-humidities.index'))
                    ->sort(1),
                NavigationItem::make('Pending Review')
                    ->label('Pending Review')
                    ->url(fn() => TemperatureHumidityResource::getUrl('reviewed'))
                    ->group('Temperature & Humidity')
                    ->isActiveWhen(fn () => request()->routeIs('filament.dashboard.resources.temperature-humidities.reviewed'))
                    ->sort(1),
                NavigationItem::make('Pending Acknowledgement')
                    ->label('Pending Acknowledgement')
                    ->url(fn() => TemperatureHumidityResource::getUrl('acknowledged'))                    
                    ->group('Temperature & Humidity')
                    ->isActiveWhen(fn () => request()->routeIs('filament.dashboard.resources.temperature-humidities.acknowledged'))
                    ->sort(2),

                // Temperature Deviation
                NavigationItem::make('All')
                    ->label('All')
                    ->url(fn() => TemperatureDeviationResource::getUrl('index'))
                    ->group('Temperature Deviation')
                    ->isActiveWhen(fn () => request()->routeIs('filament.dashboard.resources.temperature-deviations.index'))
                    ->sort(1),
                NavigationItem::make('Pending Review')
                    ->label('Pending Review')
                    ->url(fn() => TemperatureDeviationResource::getUrl('reviewed'))
                    ->group('Temperature Deviation')
                    ->isActiveWhen(fn () => request()->routeIs('filament.dashboard.resources.temperature-deviations.reviewed'))
                    ->sort(1),
                NavigationItem::make('Pending Acknowledgement')
                    ->label('Pending Acknowledgement')
                    ->url(fn() => TemperatureDeviationResource::getUrl('acknowledged'))                    
                    ->group('Temperature Deviation')
                    ->isActiveWhen(fn () => request()->routeIs('filament.dashboard.resources.temperature-deviations.acknowledged'))
                    ->sort(2),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                ->label('Temperature & Humidity')
                ->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make()
                ->label('Temperature Deviation')
                ->icon('heroicon-o-clipboard-document-list')
                ->collapsed(true),
                NavigationGroup::make()
                ->label('Location Management')
                ->icon('heroicon-o-map-pin')
                ->collapsed(true),
                NavigationGroup::make()
                ->label('Admin Settings')
                ->icon('heroicon-o-cog')
                ->collapsed(true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CheckPasswordChangeRequired::class,
            ]);
    }
}
