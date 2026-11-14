<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentInvoicesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Latest Invoices & FBR Fiscalization';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()->latest()->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business / Seller')
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('buyer_name')
                    ->label('Buyer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('total_invoice_value')
                    ->label('Amount (PKR)')
                    ->money('PKR')
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('status')
                    ->label('FBR Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accepted' => 'success',
                        'submitted', 'pending' => 'info',
                        'failed' => 'danger',
                        'needs_reconciliation' => 'warning',
                        'draft' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('fbr_invoice_number')
                    ->label('FBR Invoice No.')
                    ->fontFamily('mono')
                    ->placeholder('Not Fiscalized'),
            ])
            ->paginated(false);
    }
}
