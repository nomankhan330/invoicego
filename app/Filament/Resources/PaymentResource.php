<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Payment Management';
    protected static ?string $navigationGroup = 'Billing & Subscriptions';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('business_id')
                            ->label('Business / Payer')
                            ->relationship('business', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Paid By (User)')
                            ->relationship('user', 'name')
                            ->searchable(),

                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('Transaction Reference / Deposit Slip No.')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (PKR)')
                            ->numeric()
                            ->prefix('PKR')
                            ->required(),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Bank Transfer' => 'Bank Transfer (IBFT)',
                                'JazzCash' => 'JazzCash',
                                'EasyPaisa' => 'EasyPaisa',
                                'PayFast' => 'PayFast Online Gateway',
                                'Credit Card' => 'Credit / Debit Card',
                                '1Link' => '1Link 1Bill',
                            ])
                            ->default('Bank Transfer')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'successful' => 'Successful / Verified',
                                'pending' => 'Pending Verification',
                                'failed' => 'Failed / Rejected',
                                'refunded' => 'Refunded',
                            ])
                            ->default('successful')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Payment Notes / Verification Details')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_reference')
                    ->label('Transaction Ref')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (PKR)')
                    ->money('PKR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'successful' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->placeholder('Automatic / Pending'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'successful' => 'Successful',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'Bank Transfer' => 'Bank Transfer',
                        'JazzCash' => 'JazzCash',
                        'EasyPaisa' => 'EasyPaisa',
                        'PayFast' => 'PayFast',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('verify')
                    ->label('Verify & Activate')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Verify Subscription Payment')
                    ->modalDescription('This will mark the payment as successful and renew the business 30-day subscription.')
                    ->action(function (Payment $record) {
                        $record->markAsVerified(auth()->id());
                        Notification::make()
                            ->title('Payment Verified')
                            ->body("Payment Rs {$record->amount} verified. 30-day subscription active.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'failed']);
                        Notification::make()
                            ->title('Payment marked as Failed')
                            ->warning()
                            ->send();
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
