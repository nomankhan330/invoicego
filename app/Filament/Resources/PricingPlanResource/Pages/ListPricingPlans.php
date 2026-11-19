<?php

namespace App\Filament\Resources\PricingPlanResource\Pages;

use App\Filament\Resources\PricingPlanResource;
use App\Models\SystemSetting;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPricingPlans extends ListRecords
{
    protected static string $resource = PricingPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle_pricing_section')
                ->label(fn () => SystemSetting::get('show_pricing_plans', true)
                    ? 'Website Pricing Section: VISIBLE (Click to Hide)'
                    : 'Website Pricing Section: HIDDEN (Click to Show)')
                ->color(fn () => SystemSetting::get('show_pricing_plans', true) ? 'success' : 'danger')
                ->icon(fn () => SystemSetting::get('show_pricing_plans', true) ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                ->action(function () {
                    $current = SystemSetting::get('show_pricing_plans', true);
                    SystemSetting::set('show_pricing_plans', $current ? '0' : '1', 'general', 'boolean');

                    Notification::make()
                        ->title($current ? 'Pricing Section Hidden' : 'Pricing Section Visible')
                        ->body($current
                            ? 'The pricing section and its navigation links are now hidden from the public website.'
                            : 'The pricing section is now visible to all visitors on the public website.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Toggle Public Website Pricing Visibility')
                ->modalDescription(fn () => SystemSetting::get('show_pricing_plans', true)
                    ? 'Are you sure you want to hide the Pricing section from the website? Visitors will not see pricing plans or pricing navigation links.'
                    : 'Are you sure you want to show the Pricing section on the website?'),

            Actions\CreateAction::make()
                ->label('Add Pricing Plan'),
        ];
    }
}
