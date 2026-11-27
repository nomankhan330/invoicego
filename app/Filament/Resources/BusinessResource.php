<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Businesses & Approval';
    protected static ?string $navigationGroup = 'Users & Billing';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdminManager() ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->isAdminManager() ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->isAdminManager() ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Business::where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function provincesOptions(): array
    {
        return [
            'Punjab' => 'Punjab',
            'Sindh' => 'Sindh',
            'Khyber Pakhtunkhwa' => 'Khyber Pakhtunkhwa',
            'Balochistan' => 'Balochistan',
            'Islamabad Capital Territory' => 'Islamabad Capital Territory',
            'Gilgit-Baltistan' => 'Gilgit-Baltistan',
            'Azad Jammu & Kashmir' => 'Azad Jammu & Kashmir',
        ];
    }

    public static function sectorsOptions(): array
    {
        return [
            'Potassium Chlorate' => 'Potassium Chlorate',
            'Cement or Concrete Blocks' => 'Cement or Concrete Blocks',
            'Mobile' => 'Mobile',
            'Wholesale / Retails' => 'Wholesale / Retails',
            'Pharmaceuticals' => 'Pharmaceuticals',
            'CNG Stations' => 'CNG Stations',
            'Automobile' => 'Automobile',
            'Services' => 'Services',
            'Gas Distribution' => 'Gas Distribution',
            'Electricity Distribution' => 'Electricity Distribution',
            'Petroleum' => 'Petroleum',
            'Telecom' => 'Telecom',
            'Textile' => 'Textile',
            'FMCG' => 'FMCG',
            'Steel' => 'Steel',
            'All Other Sectors' => 'All Other Sectors',
        ];
    }

    public static function businessNaturesOptions(): array
    {
        return [
            'Manufacturer' => 'Manufacturer',
            'Importer' => 'Importer',
            'Exporter' => 'Exporter',
            'Distributor' => 'Distributor',
            'Wholesaler' => 'Wholesaler',
            'Retailer' => 'Retailer',
            'Service Provider' => 'Service Provider',
            'Other' => 'Other',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECTION 1: Owner Account Details (Exact match to Website /register flow)
                Forms\Components\Section::make('Owner Account Details')
                    ->description('Create a new business account with owner login or link an existing registered business user.')
                    ->schema([
                        Forms\Components\Radio::make('owner_mode')
                            ->label('Account Setup')
                            ->options([
                                'create_new' => 'Create New Business Account',
                                'link_existing' => 'Link Existing Business Account',
                            ])
                            ->default('create_new')
                            ->inline()
                            ->live()
                            ->visible(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),

                        // Fields when creating a brand-new Owner User on Create
                        Forms\Components\TextInput::make('owner_name')
                            ->label('Full Name')
                            ->placeholder('e.g. Ahmed Khan')
                            ->maxLength(120)
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new'),

                        Forms\Components\TextInput::make('owner_phone')
                            ->label('Phone Number')
                            ->placeholder('03001234567')
                            ->tel()
                            ->maxLength(30)
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new'),

                        Forms\Components\TextInput::make('owner_email')
                            ->label('Login Email Address')
                            ->placeholder('ahmed.khan@company.com')
                            ->email()
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'email')
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('owner_password')
                            ->label('Login Password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->helperText('Minimum 8 characters. Provide these credentials to the client to sign in.')
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new'),

                        Forms\Components\TextInput::make('owner_password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->same('owner_password')
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'create_new'),

                        // Field when linking an existing registered business user on Create (excludes Super Admin, Admin & Support Staff)
                        Forms\Components\Select::make('existing_owner_id')
                            ->label('Select Existing Registered User')
                            ->options(fn () => User::clients()->orderBy('name')->get()->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"]))
                            ->searchable()
                            ->preload()
                            ->helperText('Only registered business users are listed here (Admin & Staff accounts are excluded).')
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'link_existing')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'link_existing')
                            ->columnSpanFull(),

                        // Field on Edit: Manage linked Business Users / Owners (excludes Admin & Staff)
                        Forms\Components\Select::make('users')
                            ->label('Linked Business Owner / Users')
                            ->relationship(
                                name: 'users',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->clients()->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Only registered business users can be linked to manage this business in the tenant portal.')
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->columnSpanFull(),
                    ])->columns(2),

                // SECTION 2: Business Profile (Identical to Website /register)
                Forms\Components\Section::make('Business Profile')
                    ->description('FBR / IRIS taxpayer registration profile details.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Business Name')
                            ->placeholder('e.g. Al-Madina Trading Co.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('ntn_cnic')
                            ->label('NTN / CNIC')
                            ->placeholder('1234567')
                            ->helperText('7-digit NTN or 13-digit CNIC, digits only')
                            ->regex('/^\d{7}$|^\d{13}$/')
                            ->validationMessages([
                                'regex' => 'The NTN / CNIC must be either a 7-digit NTN or 13-digit CNIC containing digits only.',
                            ])
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('strn')
                            ->label('STRN')
                            ->placeholder('3277876123456')
                            ->helperText('Sales tax registration number, if any')
                            ->maxLength(50),

                        Forms\Components\Select::make('province')
                            ->label('Province')
                            ->options(self::provincesOptions())
                            ->default('Punjab')
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\CheckboxList::make('business_natures')
                                    ->label('Business Nature')
                                    ->options(self::businessNaturesOptions())
                                    ->columns(1)
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\CheckboxList::make('sector')
                                    ->label('Sector (For Sandbox Testing Only)')
                                    ->options(self::sectorsOptions())
                                    ->default(['All Other Sectors'])
                                    ->columns(3)
                                    ->required()
                                    ->formatStateUsing(function ($state): array {
                                        if (is_array($state)) {
                                            return array_values(array_filter($state));
                                        }
                                        if (empty($state)) {
                                            return [];
                                        }
                                        return array_values(array_filter(array_map('trim', explode(',', (string) $state))));
                                    })
                                    ->dehydrateStateUsing(fn ($state): string => is_array($state) ? implode(', ', array_filter($state)) : (string) $state)
                                    ->helperText('* If your business nature & sector is different, please select appropriate business nature and sector.')
                                    ->columnSpan(2),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('is_retailer_registered')
                            ->default(false),

                        Forms\Components\Textarea::make('address')
                            ->label('Business Address')
                            ->placeholder('Shop / Office / Plot address')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                // SECTION 3: SaaS Status & Governance
                Forms\Components\Section::make('Approval Status & FBR Environment')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Approval Status')
                            ->options([
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved (Immediate Portal Access)',
                                'rejected' => 'Rejected',
                                'suspended' => 'Suspended',
                            ])
                            ->default('approved')
                            ->required(),

                        Forms\Components\Select::make('current_environment')
                            ->label('Active FBR Environment')
                            ->options([
                                'sandbox' => 'Sandbox Only',
                                'production' => 'Production Enabled',
                            ])
                            ->default('sandbox')
                            ->required(),

                        Forms\Components\Toggle::make('production_submissions_enabled')
                            ->label('Authorize Live Production Submissions')
                            ->default(false),

                        Forms\Components\TextInput::make('invoice_limit')
                            ->label('Monthly Invoice Quota Limit')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn () => (int) \App\Models\SystemSetting::get('default_invoice_limit', 50))
                            ->helperText('Max invoices per month (default 50/month; auto-renews monthly from approval date). Set 0 for unlimited.')
                            ->required(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection / Suspension Reason')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Business')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('users.name')
                    ->label('Owner / Linked User')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->placeholder('No User Linked'),

                Tables\Columns\TextColumn::make('users.email')
                    ->label('Owner Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ntn_cnic')
                    ->label('NTN / CNIC')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('strn')
                    ->label('STRN')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('province')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sector')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'suspended',
                    ]),

                Tables\Columns\IconColumn::make('production_submissions_enabled')
                    ->label('Prod Live')
                    ->boolean(),

                Tables\Columns\TextColumn::make('invoice_quota')
                    ->label('Monthly Quota')
                    ->badge()
                    ->state(function (Business $record): string {
                        $q = $record->getInvoiceQuotaSummary();
                        return $q['unlimited'] ? "{$q['used']} / Unlimited" : "{$q['used']} / {$q['limit']} /mo";
                    })
                    ->description(function (Business $record): ?string {
                        $q = $record->getInvoiceQuotaSummary();
                        return $q['unlimited'] ? 'No monthly cap' : "Renews {$q['renews_on']}";
                    })
                    ->color(function (Business $record): string {
                        $q = $record->getInvoiceQuotaSummary();
                        if ($q['unlimited']) return 'success';
                        if ($q['reached']) return 'danger';
                        return $q['percent'] >= 80 ? 'warning' : 'primary';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered On')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\SelectFilter::make('sector')
                    ->options(self::sectorsOptions())
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->where('sector', 'like', '%' . $data['value'] . '%');
                    }),
                Tables\Filters\SelectFilter::make('province')
                    ->options(self::provincesOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Business $record) => ($record->status === 'pending' || $record->status === 'rejected') && (auth()->user()?->isAdminManager() ?? false))
                    ->requiresConfirmation()
                    ->action(function (Business $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                            'rejection_reason' => null,
                        ]);

                        ActivityLog::record('business_approved', "Business '{$record->name}' (NTN: {$record->ntn_cnic}) approved by admin.", [
                            'business_id' => $record->id,
                        ], $record->id);

                        Notification::make()
                            ->title("Business '{$record->name}' Approved")
                            ->body('The business owner can now access their tenant portal.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Business $record) => $record->status === 'pending' && (auth()->user()?->isAdminManager() ?? false))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required(),
                    ])
                    ->action(function (Business $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        ActivityLog::record('business_rejected', "Business '{$record->name}' rejected by admin.", [
                            'reason' => $data['rejection_reason'],
                        ], $record->id);

                        Notification::make()
                            ->title("Business '{$record->name}' Rejected")
                            ->body('The business registration was rejected.')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Edit Business & Owner')
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false),

                    Tables\Actions\Action::make('set_invoice_limit')
                        ->label('Set Monthly Invoice Limit')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('warning')
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false)
                        ->form(fn (Business $record) => [
                            Forms\Components\TextInput::make('invoice_limit')
                                ->label('Monthly Invoice Limit')
                                ->numeric()
                                ->minValue(0)
                                ->required()
                                ->default(fn () => $record->getEffectiveInvoiceLimit())
                                ->helperText('Default is 50/month (auto-renews monthly from approval date). Enter 0 for unlimited.'),
                        ])
                        ->action(function (Business $record, array $data) {
                            $newLimit = max(0, (int) $data['invoice_limit']);
                            $record->update(['invoice_limit' => $newLimit]);
                            $label = $newLimit === 0 ? 'Unlimited' : "{$newLimit} invoices/month";
                            ActivityLog::record('invoice_limit_updated', "Admin updated monthly invoice limit for '{$record->name}' to {$label}.", [
                                'business_id' => $record->id,
                                'invoice_limit' => $newLimit,
                            ], $record->id);
                            Notification::make()
                                ->title('Monthly Limit Updated')
                                ->body("{$record->name} is now set to {$label}.")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('reset_owner_password')
                        ->label('Reset Owner Password')
                        ->icon('heroicon-o-key')
                        ->color('gray')
                        ->visible(fn (Business $record) => $record->users()->exists() && (auth()->user()?->isAdminManager() ?? false))
                        ->form([
                            Forms\Components\TextInput::make('new_password')
                                ->label('New Owner Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(8)
                                ->default(fn () => \Illuminate\Support\Str::password(12, true, true, true, false))
                                ->helperText('Click the eye icon to copy the password and share with the owner.'),
                        ])
                        ->action(function (Business $record, array $data) {
                            $owner = $record->users()->first();
                            if (!$owner) {
                                Notification::make()->title('No Owner Linked')->danger()->send();
                                return;
                            }
                            $owner->update(['password' => \Illuminate\Support\Facades\Hash::make($data['new_password'])]);
                            ActivityLog::record('user_password_reset', "Admin reset password for owner {$owner->email} ({$record->name}).", [
                                'target_user_id' => $owner->id,
                                'business_id' => $record->id,
                            ], $record->id);
                            Notification::make()
                                ->title('Owner Password Reset')
                                ->body("Password updated for {$owner->name} ({$owner->email}).")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('view_invoices')
                        ->label('View Invoices')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn (Business $record) => InvoiceResource::getUrl('index', [
                            'tableFilters' => ['business_id' => ['value' => $record->id]],
                        ])),

                    Tables\Actions\Action::make('toggle_suspend')
                        ->label(fn (Business $record) => $record->status === 'suspended' ? 'Reactivate Business' : 'Suspend Business')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->visible(fn (Business $record) => in_array($record->status, ['approved', 'suspended']) && (auth()->user()?->isAdminManager() ?? false))
                        ->requiresConfirmation()
                        ->action(function (Business $record) {
                            $newStatus = $record->status === 'suspended' ? 'approved' : 'suspended';
                            $record->update(['status' => $newStatus]);

                            ActivityLog::record('business_status_changed', "Business '{$record->name}' status changed to {$newStatus}.", [
                                'status' => $newStatus,
                            ], $record->id);

                            Notification::make()
                                ->title("Business status changed to {$newStatus}")
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn () => auth()->user()?->isAdminManager() ?? false)
                        ->requiresConfirmation(),
                ])->label('Manage')->icon('heroicon-m-ellipsis-vertical')->button()->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
