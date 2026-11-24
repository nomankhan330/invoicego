<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\InvoiceResource\Widgets\InvoiceStatsWidget;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('business_analytics')
                ->label('Business-Wise & Analytics')
                ->icon('heroicon-o-chart-bar-square')
                ->color('primary')
                ->url(fn () => InvoiceResource::getUrl('analytics')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $base = fn () => Invoice::withoutGlobalScopes();

        return [
            'all' => Tab::make('All Invoices')
                ->badge((clone $base())->count()),

            'successful' => Tab::make('Successful')
                ->badge((clone $base())->where('status', 'accepted')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'accepted')),

            'failed' => Tab::make('Failed')
                ->badge((clone $base())->where('status', 'failed')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed')),

            'pending' => Tab::make('Pending / Draft')
                ->badge((clone $base())->whereIn('status', ['draft', 'pending', 'needs_reconciliation'])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['draft', 'pending', 'needs_reconciliation'])),

            'submitted' => Tab::make('Submitted')
                ->badge((clone $base())->where('status', 'submitted')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'submitted')),

            'cancelled' => Tab::make('Cancelled')
                ->badge((clone $base())->where('status', 'cancelled')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}
