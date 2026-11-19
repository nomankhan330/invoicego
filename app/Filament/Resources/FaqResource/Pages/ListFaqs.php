<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Models\SystemSetting;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle_faq_section')
                ->label(fn () => SystemSetting::get('show_faq_section', true)
                    ? 'Website FAQ Section: VISIBLE (Click to Hide)'
                    : 'Website FAQ Section: HIDDEN (Click to Show)')
                ->color(fn () => SystemSetting::get('show_faq_section', true) ? 'success' : 'danger')
                ->icon(fn () => SystemSetting::get('show_faq_section', true) ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                ->action(function () {
                    $current = SystemSetting::get('show_faq_section', true);
                    SystemSetting::set('show_faq_section', $current ? '0' : '1', 'general', 'boolean');

                    Notification::make()
                        ->title($current ? 'FAQ Section Hidden' : 'FAQ Section Visible')
                        ->body($current
                            ? 'The Frequently Asked Questions section and its navigation links are now hidden from the public website.'
                            : 'The Frequently Asked Questions section is now visible to all visitors on the public website.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Toggle Public Website FAQ Section Visibility')
                ->modalDescription(fn () => SystemSetting::get('show_faq_section', true)
                    ? 'Are you sure you want to hide the FAQ section from the website? Visitors will not see questions or FAQ navigation links.'
                    : 'Are you sure you want to show the FAQ section on the website?'),

            Actions\CreateAction::make()
                ->label('Add New FAQ'),
        ];
    }
}
