<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\User;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_invoices')
                ->label('View Invoices')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(function (User $record) {
                    $businessId = $record->businesses()->value('businesses.id');
                    return $businessId
                        ? InvoiceResource::getUrl('index', ['tableFilters' => ['business_id' => ['value' => $businessId]]])
                        : InvoiceResource::getUrl('index');
                }),
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->isAdminManager() ?? false),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // 1. Invoice Performance Summary
                Infolists\Components\Section::make('Invoice & FBR Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_invoices_created')
                            ->label('Total Invoices Created')
                            ->badge()
                            ->color('primary')
                            ->state(function (User $record): string {
                                $bizIds = $record->businesses()->pluck('businesses.id');
                                $count = Invoice::withoutGlobalScopes()->whereIn('business_id', $bizIds)->count();
                                return number_format($count);
                            }),

                        Infolists\Components\TextEntry::make('successful_fbr_invoices')
                            ->label('Successful FBR Invoices')
                            ->badge()
                            ->color('success')
                            ->state(function (User $record): string {
                                $bizIds = $record->businesses()->pluck('businesses.id');
                                $count = Invoice::withoutGlobalScopes()->whereIn('business_id', $bizIds)->where('status', 'accepted')->count();
                                return number_format($count);
                            }),

                        Infolists\Components\TextEntry::make('failed_fbr_invoices')
                            ->label('Failed FBR Invoices')
                            ->badge()
                            ->color('danger')
                            ->state(function (User $record): string {
                                $bizIds = $record->businesses()->pluck('businesses.id');
                                $count = Invoice::withoutGlobalScopes()->whereIn('business_id', $bizIds)->where('status', 'failed')->count();
                                return number_format($count);
                            }),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Account Creation Date')
                            ->dateTime('d M Y, h:i A'),

                        Infolists\Components\TextEntry::make('last_login_at')
                            ->label('Last Login')
                            ->dateTime('d M Y, h:i A')
                            ->placeholder('Never logged in yet'),
                    ])->columns(5),

                // 2. Owner & Account Information
                Infolists\Components\Section::make('Owner & Account Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('user_code')
                            ->label('User ID')
                            ->badge()
                            ->color('info')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('name')
                            ->label('Owner Full Name')
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email Address')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('username')
                            ->label('Username')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Mobile Number')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('cnic')
                            ->label('Owner CNIC')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Account Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'suspended' => 'danger',
                                'inactive' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('role_display')
                            ->label('Access Role')
                            ->badge()
                            ->state(fn (User $record) => match ($record->effectiveAdminRole()) {
                                'super_admin' => 'Super Admin',
                                'admin' => 'Admin',
                                'support_staff' => 'Support Staff',
                                default => 'Business Owner / User',
                            }),
                    ])->columns(4),

                // 3. Business Information
                Infolists\Components\Section::make('Business Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('biz_name')
                            ->label('Business Name')
                            ->weight('bold')
                            ->state(fn (User $record) => $record->businesses()->first()?->name ?? 'No Business Linked'),
                        Infolists\Components\TextEntry::make('biz_type')
                            ->label('Business Type')
                            ->badge()
                            ->state(fn (User $record) => $record->businesses()->first()?->business_type ?? '—'),
                        Infolists\Components\TextEntry::make('biz_ntn')
                            ->label('NTN / CNIC')
                            ->copyable()
                            ->state(fn (User $record) => $record->businesses()->first()?->ntn_cnic ?? '—'),
                        Infolists\Components\TextEntry::make('biz_strn')
                            ->label('STRN')
                            ->state(fn (User $record) => $record->businesses()->first()?->strn ?: '—'),
                        Infolists\Components\TextEntry::make('biz_city')
                            ->label('City')
                            ->state(fn (User $record) => $record->businesses()->first()?->city ?: '—'),
                        Infolists\Components\TextEntry::make('biz_province')
                            ->label('Province')
                            ->state(fn (User $record) => $record->businesses()->first()?->province ?? '—'),
                        Infolists\Components\TextEntry::make('biz_status')
                            ->label('Business Approval Status')
                            ->badge()
                            ->state(fn (User $record) => ucfirst($record->businesses()->first()?->status ?? 'N/A')),
                        Infolists\Components\TextEntry::make('biz_address')
                            ->label('Business Address')
                            ->state(fn (User $record) => $record->businesses()->first()?->address ?? '—')
                            ->columnSpanFull(),
                    ])->columns(4),

                // 4. Subscription & FBR Integration Details
                Infolists\Components\Section::make('Subscription & FBR Integration Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('sub_plan')
                            ->label('Subscription Plan')
                            ->badge()
                            ->color('primary')
                            ->state(fn (User $record) => $record->businesses()->first()?->subscriptions()->latest('id')->first()?->plan_name ?? 'No Active Plan'),
                        Infolists\Components\TextEntry::make('sub_status')
                            ->label('Subscription Status')
                            ->badge()
                            ->state(fn (User $record) => ucfirst($record->businesses()->first()?->subscriptions()->latest('id')->first()?->status ?? 'None')),
                        Infolists\Components\TextEntry::make('sub_expires')
                            ->label('Subscription Expiry')
                            ->state(fn (User $record) => $record->businesses()->first()?->subscriptions()->latest('id')->first()?->expires_at?->format('d M Y') ?? '—'),
                        Infolists\Components\TextEntry::make('fbr_reg')
                            ->label('FBR Registration Status')
                            ->state(fn (User $record) => $record->businesses()->first()?->fbr_registration_status ?? 'Registered'),
                        Infolists\Components\TextEntry::make('fbr_env')
                            ->label('FBR Environment')
                            ->badge()
                            ->color(fn (User $record) => ($record->businesses()->first()?->current_environment === 'production') ? 'success' : 'warning')
                            ->state(fn (User $record) => ucfirst($record->businesses()->first()?->current_environment ?? 'sandbox')),
                        Infolists\Components\TextEntry::make('fbr_pos')
                            ->label('FBR POS ID')
                            ->state(fn (User $record) => $record->businesses()->first()?->fbr_pos_id ?: '—'),
                        Infolists\Components\TextEntry::make('fbr_int')
                            ->label('FBR Integration Status')
                            ->badge()
                            ->state(function (User $record): string {
                                $biz = $record->businesses()->first();
                                if (!$biz) return '—';
                                if (in_array($biz->sandbox_connection_status, ['connected', 'verified'], true)) return 'Connected (Sandbox Verified)';
                                return $biz->fbr_integration_status ?? 'Not Connected';
                            }),
                        Infolists\Components\TextEntry::make('fbr_last_verified')
                            ->label('Last FBR Check')
                            ->state(fn (User $record) => $record->businesses()->first()?->sandbox_verified_at?->format('d M Y, H:i') ?? '—'),
                    ])->columns(4),

                // 5. Account Activity History
                Infolists\Components\Section::make('Account Activity History')
                    ->schema([
                        Infolists\Components\TextEntry::make('activity_history')
                            ->label('Recent Audit Logs')
                            ->html()
                            ->state(function (User $record): string {
                                $bizIds = $record->businesses()->pluck('businesses.id');
                                $logs = ActivityLog::where('user_id', $record->id)
                                    ->orWhereIn('business_id', $bizIds)
                                    ->latest('id')
                                    ->limit(10)
                                    ->get();

                                if ($logs->isEmpty()) {
                                    return '<span class="text-gray-500 text-sm">No activity logs recorded yet for this account.</span>';
                                }

                                $rows = $logs->map(function ($log) {
                                    $when = e($log->created_at?->format('d M Y, H:i') ?? '');
                                    $action = e(strtoupper(str_replace('_', ' ', $log->action)));
                                    $desc = e($log->description);
                                    return "<div style='padding:6px 0;border-bottom:1px solid rgba(148,163,184,0.2);font-size:13px;'><strong>[{$when}]</strong> <span style='color:#1E50FF;font-weight:600;'>{$action}</span> — {$desc}</div>";
                                })->implode('');

                                return "<div>{$rows}</div>";
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
