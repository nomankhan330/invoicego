<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SystemSettingResource extends Resource
{
    protected static ?string $model = SystemSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'System Settings';
    protected static ?string $navigationGroup = 'System & Support';
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereNotIn('key', [
            'brand_logo_light_b64',
            'brand_logo_light_mime',
            'brand_logo_dark_b64',
            'brand_logo_dark_mime',
            'brand_logo_version',
            'brand_logo_has_custom',
            'brand_logo_transparent',
            'brand_logo_auto_dark',
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuration Parameter')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Setting Key')
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),

                        Forms\Components\Select::make('group')
                            ->label('Configuration Group')
                            ->options([
                                'general' => 'General Platform Settings',
                                'company' => 'Company Legal & Tax Profile',
                                'email' => 'Email & SMTP Gateway',
                                'fbr' => 'FBR / PRAL API Configuration',
                                'payment' => 'Payment Gateway & Banking',
                                'invoice' => 'Invoice & Numbering Rules',
                                'backup' => 'Backup & Storage Policy',
                            ])
                            ->default('general')
                            ->required(),

                        Forms\Components\Textarea::make('value')
                            ->label(fn (?SystemSetting $record) => in_array($record?->key, ['enable_public_registration', 'show_pricing_plans', 'show_faq_section']) ? 'Setting Value (1 = Enabled, 0 = Disabled)' : 'Setting Value')
                            ->helperText(fn (?SystemSetting $record) => in_array($record?->key, ['enable_public_registration', 'show_pricing_plans', 'show_faq_section']) ? 'Enter 1 to enable, or 0 to disable.' : null)
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Setting Key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (SystemSetting $record) => match ($record->key) {
                        'enable_public_registration' => 'Controls whether users can self-register or if admin credentials are required',
                        'show_pricing_plans' => 'Controls whether the Pricing Plans / Subscription section is shown on public website',
                        'show_faq_section' => 'Controls whether the FAQ section is shown on public website',
                        'default_invoice_limit' => 'Monthly max invoices per business (e.g. 50/month; auto-renews monthly from approval date; 0 = unlimited).',
                        default => null,
                    }),

                Tables\Columns\TextColumn::make('value')
                    ->label('Current Value')
                    ->formatStateUsing(function ($state, SystemSetting $record) {
                        if ($record->key === 'enable_public_registration') {
                            return filter_var($state, FILTER_VALIDATE_BOOLEAN) ? 'Enabled (Public)' : 'Disabled (Admin Only)';
                        }
                        if (in_array($record->key, ['show_pricing_plans', 'show_faq_section'])) {
                            return filter_var($state, FILTER_VALIDATE_BOOLEAN) ? 'Visible' : 'Hidden';
                        }
                        if ($record->key === 'default_invoice_limit') {
                            $num = (int) $state;
                            return $num > 0 ? "{$num} Invoices / Month" : 'Unlimited';
                        }
                        return Str::limit($state, 50);
                    })
                    ->badge(fn (SystemSetting $record) => in_array($record->key, ['enable_public_registration', 'show_pricing_plans', 'show_faq_section', 'default_invoice_limit']))
                    ->color(fn (SystemSetting $record, $state) => match (true) {
                        in_array($record->key, ['enable_public_registration', 'show_pricing_plans', 'show_faq_section']) => filter_var($state, FILTER_VALIDATE_BOOLEAN) ? 'success' : 'danger',
                        $record->key === 'default_invoice_limit' => 'primary',
                        default => null,
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('group')
                    ->label('Group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fbr' => 'primary',
                        'payment' => 'success',
                        'email' => 'info',
                        'backup' => 'warning',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'company' => 'Company Information',
                        'email' => 'Email / SMTP',
                        'fbr' => 'FBR / PRAL API',
                        'payment' => 'Payment Gateways',
                        'invoice' => 'Invoice Rules',
                        'backup' => 'Backups',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (SystemSetting $record) => filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? 'Disable' : 'Enable')
                    ->icon(fn (SystemSetting $record) => filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn (SystemSetting $record) => filter_var($record->value, FILTER_VALIDATE_BOOLEAN) ? 'danger' : 'success')
                    ->visible(fn (SystemSetting $record) => in_array($record->key, ['enable_public_registration', 'show_pricing_plans', 'show_faq_section']))
                    ->requiresConfirmation()
                    ->modalHeading(function (SystemSetting $record) {
                        $current = filter_var($record->value, FILTER_VALIDATE_BOOLEAN);
                        return match ($record->key) {
                            'enable_public_registration' => $current ? 'Disable Public Registration' : 'Enable Public Registration',
                            'show_pricing_plans' => $current ? 'Hide Pricing Plans Section' : 'Show Pricing Plans Section',
                            'show_faq_section' => $current ? 'Hide FAQ Section' : 'Show FAQ Section',
                            default => 'Toggle Setting',
                        };
                    })
                    ->modalDescription(function (SystemSetting $record) {
                        $current = filter_var($record->value, FILTER_VALIDATE_BOOLEAN);
                        return match ($record->key) {
                            'enable_public_registration' => $current
                                ? 'Users will not be able to self-register. "Get Started" will redirect to Sign In and the Create Account link will be hidden.'
                                : 'Users will be able to register accounts publicly directly from the website.',
                            'show_pricing_plans' => $current
                                ? 'Are you sure you want to hide the Pricing section from the public website?'
                                : 'Are you sure you want to show the Pricing section on the public website?',
                            'show_faq_section' => $current
                                ? 'Are you sure you want to hide the Frequently Asked Questions section from the public website?'
                                : 'Are you sure you want to show the Frequently Asked Questions section on the public website?',
                            default => 'Are you sure you want to toggle this setting?',
                        };
                    })
                    ->action(function (SystemSetting $record) {
                        $current = filter_var($record->value, FILTER_VALIDATE_BOOLEAN);
                        $record->update(['value' => $current ? '0' : '1']);

                        Notification::make()
                            ->title(Str::headline($record->key) . ($current ? ' Disabled / Hidden' : ' Enabled / Visible'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
