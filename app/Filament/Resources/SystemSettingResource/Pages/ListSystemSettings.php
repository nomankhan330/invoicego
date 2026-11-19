<?php

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use App\Models\SystemSetting;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSystemSettings extends ListRecords
{
    protected static string $resource = SystemSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle_registration')
                ->label(fn () => SystemSetting::get('enable_public_registration', false)
                    ? 'Public Registration: ENABLED (Click to Disable)'
                    : 'Public Registration: DISABLED (Click to Enable)')
                ->color(fn () => SystemSetting::get('enable_public_registration', false) ? 'success' : 'danger')
                ->icon(fn () => SystemSetting::get('enable_public_registration', false) ? 'heroicon-o-check-circle' : 'heroicon-o-no-symbol')
                ->action(function () {
                    $current = SystemSetting::get('enable_public_registration', false);
                    SystemSetting::set('enable_public_registration', $current ? '0' : '1', 'general', 'boolean');

                    Notification::make()
                        ->title($current ? 'Public Registration Disabled' : 'Public Registration Enabled')
                        ->body($current
                            ? 'Create Account links are now hidden and public registrations are blocked on the website.'
                            : 'Users can now register publicly via the website.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Toggle Public Account Registration')
                ->modalDescription(fn () => SystemSetting::get('enable_public_registration', false)
                    ? 'Are you sure you want to disable public registration? "Get Started" will redirect to Sign In and the Create Account link will be hidden from the website.'
                    : 'Are you sure you want to enable public registration? Users will be able to register accounts directly from the website.'),

            Actions\CreateAction::make(),
        ];
    }
}
