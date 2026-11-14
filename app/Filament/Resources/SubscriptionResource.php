<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Business;
use App\Models\Subscription;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Subscription Management';
    protected static ?string $navigationGroup = 'Billing & Subscriptions';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subscription Details')
                    ->schema([
                        Forms\Components\Select::make('business_id')
                            ->label('Business / Subscriber')
                            ->relationship('business', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('plan_name')
                            ->label('Plan Name')
                            ->default('Monthly Professional')
                            ->required(),

                        Forms\Components\TextInput::make('monthly_fee')
                            ->label('Monthly Fee (PKR)')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(5000.00)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'suspended' => 'Suspended',
                                'trial' => 'Trial (14 Days)',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date')
                            ->default(now())
                            ->required(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiry Date')
                            ->default(now()->addDays(30))
                            ->required(),

                        Forms\Components\Toggle::make('auto_renew')
                            ->label('Auto Renewal Enabled')
                            ->default(true),

                        Forms\Components\Textarea::make('notes')
                            ->label('Internal Notes')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business / User')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('plan_name')
                    ->label('Plan')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('monthly_fee')
                    ->label('Fee')
                    ->money('PKR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Started')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expiry Date')
                    ->date('d M Y')
                    ->sortable(),

                // 30-Day Countdown Column
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('30-Day Countdown')
                    ->badge()
                    ->getStateUsing(function (Subscription $record): string {
                        if ($record->status === 'expired' || ($record->expires_at && $record->expires_at->isPast())) {
                            return 'Expired';
                        }
                        $days = $record->days_remaining;
                        return $days . ' ' . ($days === 1 ? 'day left' : 'days left');
                    })
                    ->color(function (Subscription $record): string {
                        if ($record->status === 'expired' || ($record->expires_at && $record->expires_at->isPast())) {
                            return 'danger';
                        }
                        $days = $record->days_remaining;
                        if ($days > 10) {
                            return 'success';
                        } elseif ($days > 3) {
                            return 'warning';
                        }
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'suspended' => 'warning',
                        'trial' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('renew')
                    ->label('Renew (+30 Days)')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Renew Monthly Subscription')
                    ->modalDescription('This will extend the subscription by 30 days and mark the status as Active.')
                    ->action(function (Subscription $record) {
                        $record->renew(30);
                        Notification::make()
                            ->title('Subscription Renewed')
                            ->body("Business '{$record->business?->name}' subscription extended by 30 days.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (Subscription $record) => $record->status === 'active' ? 'Deactivate' : 'Activate')
                    ->icon(fn (Subscription $record) => $record->status === 'active' ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (Subscription $record) => $record->status === 'active' ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        $newStatus = $record->status === 'active' ? 'suspended' : 'active';
                        $record->update(['status' => $newStatus]);
                        Notification::make()
                            ->title("Subscription status changed to {$newStatus}")
                            ->success()
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
