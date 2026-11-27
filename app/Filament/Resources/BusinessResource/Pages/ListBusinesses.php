<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use App\Filament\Resources\BusinessResource\Widgets\BusinessStatsWidget;
use App\Filament\Resources\UserResource;
use App\Models\Business;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBusinesses extends ListRecords
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            BusinessStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_admin_staff')
                ->label('Admin & Staff Accounts')
                ->icon('heroicon-o-shield-check')
                ->color('gray')
                ->visible(fn () => auth()->user()?->isSuperAdmin() ?? false)
                ->url(fn () => UserResource::getUrl('index', ['activeTab' => 'admin_staff'])),

            Actions\CreateAction::make()
                ->label('Add Business & Owner')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Businesses')
                ->badge(Business::count())
                ->badgeColor('primary'),

            'pending' => Tab::make('Pending Approval')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(Business::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge(Business::where('status', 'approved')->count())
                ->badgeColor('success'),

            'suspended' => Tab::make('Suspended')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'suspended'))
                ->badge(Business::where('status', 'suspended')->count())
                ->badgeColor('danger'),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'rejected'))
                ->badge(Business::where('status', 'rejected')->count())
                ->badgeColor('gray'),
        ];
    }
}
