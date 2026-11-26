<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class UserStatsWidget extends Widget
{
    protected static string $view = 'filament.resources.user-resource.widgets.user-stats-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        $total = User::clients()->count();
        $active = User::clients()->where('status', 'active')->count();
        $inactive = User::clients()->where('status', 'inactive')->count();
        $suspended = User::clients()->where('status', 'suspended')->count();
        $newlyCreated = User::clients()->where('created_at', '>=', now()->subDays(7))->count();

        $activePct = $total > 0 ? round(($active / $total) * 100) : 0;

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'suspended' => $suspended,
            'newlyCreated' => $newlyCreated,
            'activePct' => $activePct,
        ];
    }
}
