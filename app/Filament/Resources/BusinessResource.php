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
    protected static ?string $navigationLabel = 'Businesses & Approvals';
    protected static ?string $navigationGroup = 'Users & Billing';
    protected static ?int $navigationSort = 2;

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
            'All Other Sectors' => 'All Other Sectors',
            'Steel' => 'Steel',
            'Fast Moving Consumer Goods (FMCG)' => 'Fast Moving Consumer Goods (FMCG)',
            'Textile' => 'Textile',
            'Pharmaceutical' => 'Pharmaceutical',
            'Automotive' => 'Automotive',
            'Electronics' => 'Electronics',
            'Chemicals' => 'Chemicals',
        ];
    }

    public static function businessNaturesOptions(): array
    {
        return [
            'Manufacturer' => 'Manufacturer',
            'Importer' => 'Importer',
            'Distributor' => 'Distributor',
            'Wholesaler' => 'Wholesaler',
            'Exporter' => 'Exporter',
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
                    ->description('Register a new owner login account (same as website registration) or link an existing user to this business.')
                    ->schema([
                        Forms\Components\Radio::make('owner_mode')
                            ->label('Owner Assignment')
                            ->options([
                                'create_new' => 'Create New Owner Account (User + Business together)',
                                'link_existing' => 'Link Existing User Account',
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

                        // Field when linking an existing User on Create
                        Forms\Components\Select::make('existing_owner_id')
                            ->label('Select Existing User (Owner)')
                            ->options(fn () => User::orderBy('name')->get()->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"]))
                            ->searchable()
                            ->preload()
                            ->required(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'link_existing')
                            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && $get('owner_mode') === 'link_existing')
                            ->columnSpanFull(),

                        // Field on Edit: Manage linked Users / Owners
                        Forms\Components\Select::make('users')
                            ->label('Linked Owner / Users')
                            ->relationship('users', 'name')
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->name} ({$record->email})")
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Users linked here can log in and manage this business in the tenant portal.')
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
                            ->required(),

                        Forms\Components\Select::make('sector')
                            ->label('Sector')
                            ->options(self::sectorsOptions())
                            ->default('All Other Sectors')
                            ->helperText('Select one sector (IRIS standard)')
                            ->searchable()
                            ->required(),

                        Forms\Components\CheckboxList::make('business_natures')
                            ->label('Business Nature (select one or more)')
                            ->options(self::businessNaturesOptions())
                            ->columns(4)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_retailer_registered')
                            ->label('Registered as a retailer in sales tax profile')
                            ->helperText('Required for Tier-1 POS scenarios SN026–SN028.')
                            ->columnSpanFull(),

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
                    ->options(self::sectorsOptions()),
                Tables\Filters\SelectFilter::make('province')
                    ->options(self::provincesOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Business $record) => $record->status === 'pending' || $record->status === 'rejected')
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
                    ->visible(fn (Business $record) => $record->status === 'pending')
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

                Tables\Actions\Action::make('toggle_suspend')
                    ->label(fn (Business $record) => $record->status === 'suspended' ? 'Reactivate' : 'Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->visible(fn (Business $record) => in_array($record->status, ['approved', 'suspended']))
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

                Tables\Actions\EditAction::make(),
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
