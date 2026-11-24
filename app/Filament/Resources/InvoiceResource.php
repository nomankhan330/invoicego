<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\Invoice;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoice Management';
    protected static ?string $navigationGroup = 'Invoices & FBR';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes()->with(['business', 'creator', 'customer']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Overview')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->disabled(),

                        Forms\Components\Select::make('business_id')
                            ->label('Seller Business')
                            ->relationship('business', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('created_by')
                            ->label('Created By (User)')
                            ->relationship('creator', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('buyer_name')
                            ->label('Buyer / Customer Name')
                            ->disabled(),

                        Forms\Components\TextInput::make('buyer_ntn_cnic')
                            ->label('Buyer NTN / CNIC')
                            ->disabled(),

                        Forms\Components\TextInput::make('buyer_province')
                            ->label('Buyer Province')
                            ->disabled(),

                        Forms\Components\TextInput::make('buyer_type')
                            ->label('Buyer Registration')
                            ->disabled(),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->disabled(),

                        Forms\Components\TextInput::make('invoice_type')
                            ->label('Invoice Type')
                            ->disabled(),

                        Forms\Components\TextInput::make('environment')
                            ->label('Environment')
                            ->disabled(),

                        Forms\Components\TextInput::make('status')
                            ->label('Invoice / FBR Status')
                            ->disabled(),

                        Forms\Components\TextInput::make('sales_tax_amount')
                            ->label('Sales Tax (PKR)')
                            ->prefix('PKR')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_invoice_value')
                            ->label('Total Amount (PKR)')
                            ->prefix('PKR')
                            ->disabled(),

                        Forms\Components\TextInput::make('fbr_invoice_number')
                            ->label('FBR Invoice Number')
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('FBR Response & Fiscalization Data')
                    ->schema([
                        Forms\Components\DateTimePicker::make('fbr_submitted_at')
                            ->label('Submitted At')
                            ->disabled(),

                        Forms\Components\Textarea::make('fbr_qr_code')
                            ->label('FBR QR Code Data')
                            ->rows(2)
                            ->disabled(),

                        Forms\Components\KeyValue::make('fbr_validation_errors')
                            ->label('FBR Validation Errors')
                            ->columnSpanFull()
                            ->disabled(),

                        Forms\Components\KeyValue::make('fbr_raw_response')
                            ->label('FBR Raw Response Payload')
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Invoice ID')
                    ->formatStateUsing(fn ($state) => 'INV-' . str_pad((string) $state, 5, '0', STR_PAD_LEFT))
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.user_code')
                    ->label('User ID')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray')
                    ->placeholder(fn (Invoice $record) => $record->created_by ? 'USR-' . str_pad((string) $record->created_by, 4, '0', STR_PAD_LEFT) : '—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('business.ntn_cnic')
                    ->label('NTN Number')
                    ->fontFamily('mono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('buyer_name')
                    ->label('Buyer Name')
                    ->formatStateUsing(fn ($state, Invoice $record) => $state ?: ($record->customer?->name ?? 'N/A'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_type')
                    ->label('Invoice Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'credit_note' => 'Credit Note',
                        'debit_note' => 'Debit Note',
                        default => 'Sale Invoice',
                    }),

                Tables\Columns\TextColumn::make('total_invoice_value')
                    ->label('Total Amount')
                    ->money('PKR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('sales_tax_amount')
                    ->label('Sales Tax')
                    ->money('PKR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('fbr_status_badge')
                    ->label('FBR Status')
                    ->badge()
                    ->state(function (Invoice $record): string {
                        if ($record->status === 'accepted') return 'Valid';
                        if ($record->status === 'failed') return 'Invalid';
                        if ($record->status === 'submitted') return 'Submitted';
                        if ($record->status === 'needs_reconciliation') return 'Pending';
                        if ($record->status === 'cancelled') return 'Cancelled';
                        return 'Not Submitted';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Valid' => 'success',
                        'Invalid' => 'danger',
                        'Submitted' => 'info',
                        'Pending' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Invoice Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'accepted' => 'Valid',
                        'needs_reconciliation' => 'Pending',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'accepted' => 'success',
                        'submitted', 'pending' => 'info',
                        'failed' => 'danger',
                        'needs_reconciliation' => 'warning',
                        'draft', 'cancelled' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('fbr_invoice_number')
                    ->label('FBR Invoice #')
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('FBR / Invoice Status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'accepted' => 'Valid / Successful',
                        'failed' => 'Invalid / Failed',
                        'needs_reconciliation' => 'Needs Reconciliation',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('invoice_type')
                    ->label('Invoice Type')
                    ->options([
                        'standard' => 'Sale Invoice',
                        'credit_note' => 'Credit Note',
                        'debit_note' => 'Debit Note',
                    ]),

                Tables\Filters\SelectFilter::make('environment')
                    ->label('Environment')
                    ->options([
                        'sandbox' => 'Sandbox',
                        'production' => 'Production',
                    ]),

                Tables\Filters\Filter::make('invoice_date')
                    ->label('Date Range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From Date'),
                        Forms\Components\DatePicker::make('until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('invoice_date', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('invoice_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View Details'),

                    Tables\Actions\Action::make('business_history')
                        ->label('Business History')
                        ->icon('heroicon-o-building-office-2')
                        ->color('info')
                        ->url(fn (Invoice $record) => InvoiceResource::getUrl('analytics', ['business_id' => $record->business_id])),

                    Tables\Actions\Action::make('resubmit')
                        ->label('Re-Submit to FBR')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (Invoice $record) => in_array($record->status, ['failed', 'draft', 'pending'], true) && (auth()->user()?->isAdminManager() ?? false))
                        ->requiresConfirmation()
                        ->action(function (Invoice $record) {
                            $fbrService = app(FbrIntegrationServiceInterface::class);
                            $result = $fbrService->submitInvoice($record);
                            ActivityLog::record('admin_invoice_resubmit', "Admin re-submitted invoice {$record->invoice_number} to FBR.", ['success' => $result['success']], $record->business_id);
                            if ($result['success']) {
                                Notification::make()->title('Submitted to FBR')->body("FBR Invoice #: {$result['fbr_invoice_number']}")->success()->send();
                            } else {
                                Notification::make()->title('FBR Rejected')->body(implode('; ', $result['errors'] ?? ['Submission failed']))->danger()->send();
                            }
                        }),

                    Tables\Actions\Action::make('cancel_invoice')
                        ->label('Cancel Invoice')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Invoice $record) => !in_array($record->status, ['accepted', 'cancelled'], true) && (auth()->user()?->isAdminManager() ?? false))
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Invoice')
                        ->modalDescription('Are you sure you want to mark this invoice as Cancelled? This action is recorded in the audit log.')
                        ->action(function (Invoice $record) {
                            $record->update(['status' => 'cancelled']);
                            ActivityLog::record('invoice_cancelled', "Admin cancelled invoice {$record->invoice_number}.", ['invoice_id' => $record->id], $record->business_id);
                            Notification::make()->title('Invoice Cancelled')->body("Invoice {$record->invoice_number} has been cancelled.")->warning()->send();
                        }),
                ])->label('Actions')->icon('heroicon-m-ellipsis-vertical')->button()->size('sm'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'analytics' => Pages\BusinessInvoiceAnalytics::route('/business-analytics'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
