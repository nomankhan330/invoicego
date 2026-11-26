<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\UserStatsWidget;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'User Management';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Create New User')
                ->icon('heroicon-o-user-plus')
                ->color('primary'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Business Users')
                ->badge(User::clients()->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->clients()),

            'active' => Tab::make('Active')
                ->badge(User::clients()->where('status', 'active')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->clients()->where('status', 'active')),

            'inactive' => Tab::make('Inactive / Draft')
                ->badge(User::clients()->where('status', 'inactive')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->clients()->where('status', 'inactive')),

            'suspended' => Tab::make('Suspended')
                ->badge(User::clients()->where('status', 'suspended')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->clients()->where('status', 'suspended')),

            'recent' => Tab::make('Newly Created (7d)')
                ->badge(User::clients()->where('created_at', '>=', now()->subDays(7))->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->clients()->where('created_at', '>=', now()->subDays(7))),
        ];

        if (auth()->user()?->isSuperAdmin()) {
            $tabs['staff'] = Tab::make('Admin & Staff')
                ->badge(User::adminStaff()->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->adminStaff());
        }

        return $tabs;
    }
}
