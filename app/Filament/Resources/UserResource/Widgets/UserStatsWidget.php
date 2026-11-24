<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $total = User::count();
        $active = User::where('status', 'active')->count();
        $inactive = User::where('status', 'inactive')->count();
        $suspended = User::where('status', 'suspended')->count();
        $newlyCreated = User::where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make('Total Users', number_format($total))
                ->description('All registered accounts')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Active Users', number_format($active))
                ->description('Authorized to sign in')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Inactive Users', number_format($inactive))
                ->description('Draft or deactivated accounts')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Suspended Users', number_format($suspended))
                ->description('Blocked from portal access')
                ->icon('heroicon-o-no-symbol')
                ->color('danger'),

            Stat::make('Newly Created Users', number_format($newlyCreated))
                ->description('Registered in last 7 days')
                ->icon('heroicon-o-sparkles')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 5;
    }
}
