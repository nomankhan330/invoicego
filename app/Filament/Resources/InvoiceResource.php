<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoice Management';
    protected static ?string $navigationGroup = 'Invoices & FBR Integration';
    protected static ?int $navigationSort = 1;

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
                            ->label('FBR Status')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_invoice_value')
                            ->label('Grand Total (PKR)')
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
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Seller / Business')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('buyer_name')
                    ->label('Customer Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_invoice_value')
                    ->label('Amount (PKR)')
                    ->money('PKR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

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
                    ->label('FBR Invoice #')
                    ->fontFamily('mono')
                    ->placeholder('Not Fiscalized')
                    ->searchable(),

                Tables\Columns\TextColumn::make('environment')
                    ->label('Env')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'danger',
                        'sandbox' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'accepted' => 'Accepted',
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'needs_reconciliation' => 'Needs Reconciliation',
                    ]),
                Tables\Filters\SelectFilter::make('environment')
                    ->options([
                        'sandbox' => 'Sandbox',
                        'production' => 'Production',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('retry_fbr')
                    ->label('Submit / Retry FBR')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (Invoice $record) => $record->status !== 'accepted')
                    ->requiresConfirmation()
                    ->modalHeading('Submit Invoice to FBR')
                    ->modalDescription('Do you want to submit/resubmit this invoice to FBR DI API?')
                    ->action(function (Invoice $record) {
                        $fbrService = app(FbrIntegrationServiceInterface::class);
                        $res = $fbrService->submitInvoice($record);

                        if ($res['success']) {
                            Notification::make()
                                ->title('FBR Submission Successful')
                                ->body("FBR Invoice Number: {$res['fbr_invoice_number']}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('FBR Submission Failed')
                                ->body(implode('; ', $res['errors'] ?? ['Validation rejected']))
                                ->danger()
                                ->send();
                        }
                    }),
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
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
