<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Admin & Staff';
    protected static ?string $navigationGroup = 'Users & Billing';
    protected static ?int $navigationSort = 1;
    protected static bool $shouldRegisterNavigation = false;

    // RBAC: Only Super Admin & Admin can create, edit, or delete accounts (Support Staff is View-Only)
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Only Super Admins can view/manage Admin & Support Staff accounts
        if (!(auth()->user()?->isSuperAdmin() ?? false)) {
            $query->clients();
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdminManager() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAdminManager() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdminManager() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdminManager() ?? false;
    }

    public static function form(Form $form): Form
    {
        $provinces = [
            'Punjab' => 'Punjab',
            'Sindh' => 'Sindh',
            'Khyber Pakhtunkhwa' => 'Khyber Pakhtunkhwa',
            'Balochistan' => 'Balochistan',
            'Islamabad Capital Territory' => 'Islamabad Capital Territory',
            'Gilgit-Baltistan' => 'Gilgit-Baltistan',
            'Azad Jammu & Kashmir' => 'Azad Jammu & Kashmir',
        ];

        return $form
            ->schema([
                // SECTION 1: Business Information (Shown on Create for 1-step registration)
                Forms\Components\Section::make('Business Information')
                    ->description('Register the user\'s isolated business profile.')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->schema([
                        Forms\Components\TextInput::make('business_name')
                            ->label('Business Name')
                            ->placeholder('e.g. Al-Madina Trading Co.')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Forms\Components\Select::make('business_type')
                            ->label('Business Type')
                            ->options([
                                'Individual' => 'Individual',
                                'Company' => 'Company',
                                'Partnership' => 'Partnership',
                                'AOP' => 'AOP (Association of Persons)',
                            ])
                            ->default('Company')
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\TextInput::make('business_ntn')
                            ->label('NTN Number')
                            ->placeholder('7-digit NTN or 13-digit CNIC')
                            ->helperText('Digits only (7-digit NTN or 13-digit CNIC)')
                            ->regex('/^\d{7}$|^\d{13}$/')
                            ->unique(table: Business::class, column: 'ntn_cnic')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('business_strn')
                            ->label('STRN Number (Optional)')
                            ->placeholder('3277876123456')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('business_city')
                            ->label('City')
                            ->placeholder('e.g. Lahore, Karachi, Islamabad')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(100),

                        Forms\Components\Select::make('business_province')
                            ->label('Province')
                            ->options($provinces)
                            ->default('Punjab')
                            ->searchable()
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\Textarea::make('business_address')
                            ->label('Business Address')
                            ->placeholder('Plot / Shop / Office full address')
                            ->rows(2)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),
                    ])->columns(2),

                // SECTION 2: Owner Information
                Forms\Components\Section::make('Owner Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Owner Full Name')
                            ->placeholder('e.g. Ahmed Khan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('cnic')
                            ->label('CNIC Number')
                            ->placeholder('35201-1234567-1')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Mobile Number')
                            ->placeholder('03001234567')
                            ->tel()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(30),
                    ])->columns(2),

                // SECTION 3: Account Information
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->placeholder('e.g. ahmed.traders')
                            ->unique(ignoreRecord: true)
                            ->maxLength(80),

                        Forms\Components\Select::make('status')
                            ->label('Account Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended / Blocked',
                            ])
                            ->default('active')
                            ->required(),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generatePassword')
                                    ->icon('heroicon-o-sparkles')
                                    ->tooltip('Generate secure temporary password')
                                    ->action(function (Forms\Set $set) {
                                        $temp = Str::password(12, true, true, true, false);
                                        $set('password', $temp);
                                        $set('password_confirmation', $temp);
                                        Notification::make()
                                            ->title('Temporary Password Generated')
                                            ->body("Password: {$temp} (click eye icon to view/copy)")
                                            ->info()
                                            ->send();
                                    })
                            )
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('invoice_limit')
                            ->label('Monthly Invoice Quota Limit')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn () => (int) SystemSetting::get('default_invoice_limit', 50))
                            ->helperText('Max invoices per month (default 50/month; auto-renews monthly from approval date). Enter 0 for unlimited.')
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\Select::make('subscription_plan_id')
                            ->label('Subscription Plan (Optional)')
                            ->options(function () {
                                $plans = PricingPlan::where('is_active', true)->orderBy('sort_order')->get();
                                if ($plans->isNotEmpty()) {
                                    return $plans->mapWithKeys(fn ($p) => [$p->id => "{$p->name} ({$p->price}{$p->billing_period})"])->toArray();
                                }
                                return [];
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Leave blank — Subscriptions paused during free rollout')
                            ->helperText('Optional while free invoice-limit mode is active.')
                            ->visible(fn (string $operation): bool => $operation === 'create' && PricingPlan::where('is_active', true)->exists()),

                        Forms\Components\Select::make('subscription_plan_name')
                            ->label('Subscription Tier (Optional)')
                            ->options([
                                'Free' => 'Free',
                                'Basic' => 'Basic',
                                'Premium' => 'Premium',
                                'Enterprise' => 'Enterprise',
                            ])
                            ->placeholder('Optional — Leave blank for Free Quota Mode')
                            ->visible(fn (string $operation): bool => $operation === 'create' && !PricingPlan::where('is_active', true)->exists()),

                        Forms\Components\Select::make('admin_role')
                            ->label('Access Control Role')
                            ->options([
                                'user' => 'Business User (Tenant Portal Only)',
                                'support_staff' => 'Support Staff (Admin View-Only)',
                                'admin' => 'Admin (Manage Users & Businesses)',
                                'super_admin' => 'Super Admin (Full System Access)',
                            ])
                            ->default('user')
                            ->visible(fn () => auth()->user()?->isSuperAdmin() ?? false)
                            ->helperText('Only Super Admins can assign administrative roles.'),
                    ])->columns(2),

                // SECTION 4: FBR Configuration (Shown on Create)
                Forms\Components\Section::make('FBR Configuration')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->schema([
                        Forms\Components\Select::make('fbr_registration_status')
                            ->label('FBR Registration Status')
                            ->options([
                                'Registered' => 'Registered',
                                'Pending IRIS Approval' => 'Pending IRIS Approval',
                                'Unregistered' => 'Unregistered',
                            ])
                            ->default('Registered')
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\Select::make('fbr_environment')
                            ->label('FBR Environment')
                            ->options([
                                'sandbox' => 'Sandbox (Testing)',
                                'production' => 'Production (Live)',
                            ])
                            ->default('sandbox')
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\TextInput::make('fbr_pos_id')
                            ->label('FBR POS ID (Optional)')
                            ->placeholder('Optional Tier-1 POS ID')
                            ->maxLength(60),

                        Forms\Components\Select::make('fbr_integration_status')
                            ->label('FBR Integration Status')
                            ->options([
                                'Not Connected' => 'Not Connected',
                                'Sandbox Testing' => 'Sandbox Testing',
                                'Connected' => 'Connected',
                                'Live Production' => 'Live Production',
                            ])
                            ->default('Sandbox Testing'),
                    ])->columns(2),

                // Assigned Businesses (Shown on Edit)
                Forms\Components\Section::make('Assigned Businesses')
                    ->description('Link this user to one or more registered businesses.')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->schema([
                        Forms\Components\Select::make('businesses')
                            ->label('Linked Businesses')
                            ->relationship('businesses', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Business $record) => "{$record->name} (NTN: {$record->ntn_cnic} — " . ucfirst($record->status) . ')')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('user_code')
                    ->label('User ID')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn (User $record) => 'USR-' . str_pad((string) $record->id, 4, '0', STR_PAD_LEFT)),

                Tables\Columns\TextColumn::make('businesses.name')
                    ->label('Business Name')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-building-office-2')
                    ->searchable()
                    ->placeholder('No Business Linked'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Owner Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (User $record): string => match ($record->effectiveAdminRole()) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'support_staff' => 'Support Staff',
                        default => $record->username ? '@' . $record->username : 'Business Owner',
                    }),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->icon('heroicon-m-envelope')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Mobile Number')
                    ->icon('heroicon-m-phone')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('businesses.ntn_cnic')
                    ->label('NTN / CNIC')
                    ->fontFamily('mono')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('invoice_quota_badge')
                    ->label('Monthly Quota')
                    ->badge()
                    ->state(function (User $record): string {
                        $business = $record->businesses()->first();
                        if (!$business) {
                            return '—';
                        }
                        $quota = $business->getInvoiceQuotaSummary();
                        return $quota['unlimited']
                            ? "{$quota['used']} / Unlimited"
                            : "{$quota['used']} / {$quota['limit']} /mo";
                    })
                    ->description(function (User $record): ?string {
                        $business = $record->businesses()->first();
                        if (!$business) {
                            return null;
                        }
                        $quota = $business->getInvoiceQuotaSummary();
                        return $quota['unlimited'] ? 'No monthly cap' : "Renews {$quota['renews_on']}";
                    })
                    ->color(function (User $record): string {
                        $business = $record->businesses()->first();
                        if (!$business) {
                            return 'gray';
                        }
                        $quota = $business->getInvoiceQuotaSummary();
                        if ($quota['unlimited']) {
                            return 'success';
                        }
                        if ($quota['reached']) {
                            return 'danger';
                        }
                        return $quota['percent'] >= 80 ? 'warning' : 'primary';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Account Status')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-m-check-circle',
                        'suspended' => 'heroicon-m-no-symbol',
                        'inactive' => 'heroicon-m-clock',
                        default => 'heroicon-m-minus-circle',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        'inactive' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registration Date')
                    ->dateTime('d M Y')
                    ->description(fn (User $record) => $record->last_login_at ? 'Last login: ' . $record->last_login_at->diffForHumans() : 'Never logged in')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Account Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),

                Tables\Filters\SelectFilter::make('subscription_plan')
                    ->label('Subscription Plan')
                    ->options(fn () => Subscription::query()->distinct()->pluck('plan_name', 'plan_name')->filter()->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas('businesses.subscriptions', fn (Builder $sq) => $sq->where('plan_name', $data['value']));
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->label('Registration Date')
                    ->form([
                        Forms\Components\DatePicker::make('registered_from')->label('Registered From'),
                        Forms\Components\DatePicker::make('registered_until')->label('Registered Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['registered_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['registered_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),

                Tables\Filters\SelectFilter::make('admin_role')
                    ->label('Role')
                    ->options([
                        'user' => 'Business User',
                        'support_staff' => 'Support Staff',
                        'admin' => 'Admin',
                        'super_admin' => 'Super Admin',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View Details')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit User')
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false),

                    Tables\Actions\Action::make('activate')
                        ->label('Activate Account')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (User $record) => $record->status !== 'active' && (auth()->user()?->isAdminManager() ?? false))
                        ->requiresConfirmation()
                        ->action(function (User $record) {
                            $record->update(['status' => 'active']);
                            ActivityLog::record('user_activated', "Admin activated account {$record->email}.", ['target_user_id' => $record->id]);
                            Notification::make()->title('Account Activated')->body("{$record->name} is now active.")->success()->send();
                        }),

                    Tables\Actions\Action::make('deactivate')
                        ->label('Deactivate Account')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->visible(fn (User $record) => $record->status === 'active' && (auth()->user()?->isAdminManager() ?? false))
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate User Account')
                        ->modalDescription('Are you sure you want to deactivate this account? The user will not be able to log in until reactivated.')
                        ->action(function (User $record) {
                            $record->update(['status' => 'inactive']);
                            ActivityLog::record('user_deactivated', "Admin deactivated account {$record->email}.", ['target_user_id' => $record->id]);
                            Notification::make()->title('Account Deactivated')->body("{$record->name} has been marked inactive.")->warning()->send();
                        }),

                    Tables\Actions\Action::make('suspend')
                        ->label('Suspend Account')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->visible(fn (User $record) => $record->status !== 'suspended' && (auth()->user()?->isAdminManager() ?? false))
                        ->requiresConfirmation()
                        ->modalHeading('Suspend User Account')
                        ->modalDescription('Are you sure you want to suspend this user? They will be immediately blocked from logging in or issuing invoices.')
                        ->action(function (User $record) {
                            $record->update(['status' => 'suspended']);
                            ActivityLog::record('user_suspended', "Admin suspended account {$record->email}.", ['target_user_id' => $record->id]);
                            Notification::make()->title('Account Suspended')->body("{$record->name} has been suspended.")->danger()->send();
                        }),

                    Tables\Actions\Action::make('reset_password')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('gray')
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false)
                        ->form([
                            Forms\Components\TextInput::make('new_password')
                                ->label('New Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(8)
                                ->default(fn () => Str::password(12, true, true, true, false))
                                ->helperText('A secure temporary password has been pre-generated. Click eye icon to copy.'),
                        ])
                        ->action(function (User $record, array $data) {
                            $record->update(['password' => Hash::make($data['new_password'])]);
                            ActivityLog::record('user_password_reset', "Admin reset password for {$record->email}.", ['target_user_id' => $record->id]);
                            Notification::make()->title('Password Reset Successfully')->body("Password for {$record->name} ({$record->email}) has been updated.")->success()->send();
                        }),

                    Tables\Actions\Action::make('set_invoice_limit')
                        ->label('Set Invoice Limit')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('warning')
                        ->visible(fn (User $record) => $record->businesses()->exists() && (auth()->user()?->isAdminManager() ?? false))
                        ->form(fn (User $record) => [
                            Forms\Components\TextInput::make('invoice_limit')
                                ->label('Monthly Invoice Limit')
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->default(fn () => $record->businesses()->first()?->getEffectiveInvoiceLimit() ?? 50)
                                ->helperText('Default is 50/month (auto-renews monthly from approval date; blocks 51st in same month). Set 0 for unlimited.'),
                        ])
                        ->action(function (User $record, array $data) {
                            $business = $record->businesses()->first();
                            if (!$business) {
                                Notification::make()->title('No Business Linked')->body('Link a business first before updating invoice limits.')->danger()->send();
                                return;
                            }
                            $newLimit = max(0, (int) $data['invoice_limit']);
                            $business->update(['invoice_limit' => $newLimit]);
                            $label = $newLimit === 0 ? 'Unlimited' : "{$newLimit} invoices";
                            ActivityLog::record('invoice_limit_updated', "Admin updated invoice limit for {$business->name} to {$label}.", [
                                'business_id' => $business->id,
                                'invoice_limit' => $newLimit,
                            ], $business->id);
                            Notification::make()
                                ->title('Invoice Limit Updated')
                                ->body("{$business->name} invoice limit is now set to {$label}.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('change_subscription')
                        ->label('Change Subscription')
                        ->icon('heroicon-o-credit-card')
                        ->color('primary')
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false)
                        ->form([
                            Forms\Components\Select::make('pricing_plan_id')
                                ->label('Select Plan')
                                ->options(fn () => PricingPlan::orderBy('sort_order')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->name} ({$p->price}{$p->billing_period})"])->toArray())
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('status')
                                ->label('Subscription Status')
                                ->options([
                                    'active' => 'Active',
                                    'trial' => 'Trial',
                                    'expired' => 'Expired',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->default('active')
                                ->required(),
                            Forms\Components\DatePicker::make('expires_at')
                                ->label('Expiry Date')
                                ->default(now()->addDays(30))
                                ->required(),
                        ])
                        ->action(function (User $record, array $data) {
                            $business = $record->businesses()->first();
                            if (!$business) {
                                Notification::make()->title('No Business Linked')->body('Link a business to this user first before changing subscription.')->danger()->send();
                                return;
                            }
                            $plan = PricingPlan::find($data['pricing_plan_id']);
                            $fee = 0.00;
                            if ($plan && preg_match('/[\d,]+/', (string) $plan->price, $m)) {
                                $fee = (float) str_replace(',', '', $m[0]);
                            }
                            Subscription::updateOrCreate(
                                ['business_id' => $business->id],
                                [
                                    'pricing_plan_id' => $plan?->id,
                                    'plan_name' => $plan?->name ?? 'Custom Plan',
                                    'starts_at' => now(),
                                    'expires_at' => $data['expires_at'],
                                    'status' => $data['status'],
                                    'monthly_fee' => $fee,
                                    'auto_renew' => true,
                                ]
                            );
                            ActivityLog::record('subscription_changed', "Admin changed subscription for {$business->name} to {$plan?->name}.", ['business_id' => $business->id], $business->id);
                            Notification::make()->title('Subscription Updated')->body("Plan changed to {$plan?->name} for {$business->name}.")->success()->send();
                        }),

                    Tables\Actions\Action::make('view_invoices')
                        ->label('View Invoices')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(function (User $record) {
                            $businessId = $record->businesses()->value('businesses.id');
                            return $businessId
                                ? InvoiceResource::getUrl('index', ['tableFilters' => ['business_id' => ['value' => $businessId]]])
                                : InvoiceResource::getUrl('index');
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Delete Account')
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false)
                        ->modalHeading('Delete User Account')
                        ->modalDescription('Warning: This will permanently delete the user account and detach associated businesses.')
                        ->requiresConfirmation(),
                ])->label('Actions')->icon('heroicon-m-ellipsis-vertical')->button()->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
