<?php

namespace App\Filament\Resources\BusinessResource\Widgets;

use App\Models\Business;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class BusinessStatsWidget extends Widget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.resources.business-resource.widgets.business-stats-widget';

    protected function getViewData(): array
    {
        $total = Business::count();
        $approved = Business::where('status', 'approved')->count();
        $pending = Business::where('status', 'pending')->count();
        $suspendedOrRejected = Business::whereIn('status', ['suspended', 'rejected'])->count();
        $newlyCreated = Business::where('created_at', '>=', Carbon::now()->subDays(7))->count();

        return [
            'cards' => [
                [
                    'label' => 'Total Businesses',
                    'value' => number_format($total),
                    'desc' => 'All registered accounts',
                    'tone' => 'blue',
                    'icon' => 'building',
                ],
                [
                    'label' => 'Approved / Active',
                    'value' => number_format($approved),
                    'desc' => 'Active portal access',
                    'tone' => 'emerald',
                    'icon' => 'check',
                ],
                [
                    'label' => 'Pending Approval',
                    'value' => number_format($pending),
                    'desc' => 'Awaiting admin review',
                    'tone' => 'amber',
                    'icon' => 'clock',
                ],
                [
                    'label' => 'Suspended / Rejected',
                    'value' => number_format($suspendedOrRejected),
                    'desc' => 'Restricted accounts',
                    'tone' => 'rose',
                    'icon' => 'ban',
                ],
                [
                    'label' => 'New (7 Days)',
                    'value' => number_format($newlyCreated),
                    'desc' => 'Recent signups',
                    'tone' => 'sky',
                    'icon' => 'spark',
                ],
            ],
        ];
    }
}
