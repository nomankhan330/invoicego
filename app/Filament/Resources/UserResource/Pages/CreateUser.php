<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected bool $saveAsDraft = false;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Create Account')
            ->icon('heroicon-o-check-circle');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            Action::make('saveAsDraft')
                ->label('Save as Draft')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(function () {
                    $this->saveAsDraft = true;
                    $this->create();
                }),
            $this->getCancelFormAction()
                ->label('Cancel'),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $isDraft = $this->saveAsDraft;
        $this->saveAsDraft = false;

        // Extract Business, Subscription & FBR fields
        $businessName = trim((string) ($data['business_name'] ?? ''));
        $businessType = $data['business_type'] ?? 'Company';
        $ntnNumber = preg_replace('/\D/', '', (string) ($data['business_ntn'] ?? ''));
        $strnNumber = $data['business_strn'] ?? null;
        $businessAddress = $data['business_address'] ?? '';
        $businessCity = $data['business_city'] ?? null;
        $businessProvince = $data['business_province'] ?? 'Punjab';

        $subscriptionPlanId = $data['subscription_plan_id'] ?? null;
        $subscriptionPlanFallback = $data['subscription_plan_name'] ?? null;
        $invoiceLimit = (isset($data['invoice_limit']) && $data['invoice_limit'] !== '' && $data['invoice_limit'] !== null)
            ? max(0, (int) $data['invoice_limit'])
            : (int) SystemSetting::get('default_invoice_limit', 50);

        $fbrRegStatus = $data['fbr_registration_status'] ?? 'Registered';
        $fbrEnv = $data['fbr_environment'] ?? 'sandbox';
        $fbrPosId = $data['fbr_pos_id'] ?? null;
        $fbrIntegrationStatus = $data['fbr_integration_status'] ?? 'Not Connected';

        $adminRole = $data['admin_role'] ?? 'user';
        $accountStatus = $isDraft ? 'inactive' : ($data['status'] ?? 'active');

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => !empty($data['username']) ? trim($data['username']) : null,
            'phone' => $data['phone'] ?? null,
            'cnic' => $data['cnic'] ?? null,
            'password' => $data['password'], // already hashed by dehydrateStateUsing or User cast
            'status' => $accountStatus,
            'admin_role' => $adminRole,
            'is_super_admin' => in_array($adminRole, ['super_admin'], true),
        ];

        return DB::transaction(function () use (
            $userData,
            $isDraft,
            $businessName,
            $businessType,
            $ntnNumber,
            $strnNumber,
            $businessAddress,
            $businessCity,
            $businessProvince,
            $subscriptionPlanId,
            $subscriptionPlanFallback,
            $invoiceLimit,
            $fbrRegStatus,
            $fbrEnv,
            $fbrPosId,
            $fbrIntegrationStatus
        ) {
            /** @var User $user */
            $user = User::create($userData);

            $business = null;
            if ($businessName !== '') {
                $business = Business::create([
                    'uuid' => (string) Str::uuid(),
                    'name' => $businessName,
                    'business_type' => $businessType,
                    'ntn_cnic' => $ntnNumber !== '' ? $ntnNumber : '0000000',
                    'strn' => $strnNumber,
                    'address' => $businessAddress ?: ($businessCity ?: 'Pakistan'),
                    'city' => $businessCity,
                    'province' => $businessProvince,
                    'sector' => 'All Other Sectors',
                    'business_natures' => [$businessType === 'Individual' ? 'Retailer' : 'Distributor'],
                    'is_retailer_registered' => false,
                    'status' => $isDraft ? 'pending' : 'approved',
                    'approved_at' => $isDraft ? null : now(),
                    'approved_by' => $isDraft ? null : auth()->id(),
                    'current_environment' => $fbrEnv,
                    'production_submissions_enabled' => $fbrEnv === 'production',
                    'invoice_limit' => $invoiceLimit,
                    'fbr_pos_id' => $fbrPosId,
                    'fbr_registration_status' => $fbrRegStatus,
                    'fbr_integration_status' => $fbrIntegrationStatus,
                ]);

                $user->businesses()->syncWithoutDetaching([
                    $business->id => [
                        'role' => 'owner',
                        'is_default' => true,
                    ],
                ]);

                // Provision initial subscription plan if chosen
                $plan = $subscriptionPlanId ? PricingPlan::find($subscriptionPlanId) : null;
                $planName = $plan?->name ?? $subscriptionPlanFallback;
                if (!empty($planName)) {
                    $fee = 0.00;
                    if ($plan && preg_match('/[\d,]+/', (string) $plan->price, $m)) {
                        $fee = (float) str_replace(',', '', $m[0]);
                    }
                    Subscription::create([
                        'business_id' => $business->id,
                        'pricing_plan_id' => $plan?->id,
                        'plan_name' => $planName,
                        'starts_at' => now(),
                        'expires_at' => now()->addDays(30),
                        'status' => $isDraft ? 'pending' : 'active',
                        'monthly_fee' => $fee,
                        'auto_renew' => true,
                        'notes' => 'Provisioned by admin during account creation.',
                    ]);
                }
            }

            ActivityLog::record(
                $isDraft ? 'admin_user_draft_created' : 'admin_user_created',
                "Admin created user {$user->name} ({$user->email}, {$user->user_code})" . ($business ? " with business '{$business->name}'" : '') . ($isDraft ? ' [Saved as Draft].' : '.'),
                [
                    'created_user_id' => $user->id,
                    'user_code' => $user->user_code,
                    'business_id' => $business?->id,
                    'status' => $user->status,
                    'is_draft' => $isDraft,
                ],
                $business?->id
            );

            return $user;
        });
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User Account Created')
            ->body('User account, linked business profile, and configuration have been saved successfully.');
    }
}
