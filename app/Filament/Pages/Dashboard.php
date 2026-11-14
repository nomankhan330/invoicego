<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentActivitiesWidget;
use App\Filament\Widgets\RecentInvoicesWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RecentActivitiesWidget::class,
            RecentInvoicesWidget::class,
        ];
    }
}
