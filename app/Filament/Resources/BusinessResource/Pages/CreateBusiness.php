<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use App\Models\ActivityLog;
use App\Models\Business;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateBusiness extends CreateRecord
{
    protected static string $resource = BusinessResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $ownerMode = $data['owner_mode'] ?? 'create_new';
        $ownerName = $data['owner_name'] ?? null;
        $ownerPhone = $data['owner_phone'] ?? null;
        $ownerEmail = $data['owner_email'] ?? null;
        $ownerPassword = $data['owner_password'] ?? null;
        $existingOwnerId = $data['existing_owner_id'] ?? null;

        // Strip virtual owner fields before creating Business
        unset(
            $data['owner_mode'],
            $data['owner_name'],
            $data['owner_phone'],
            $data['owner_email'],
            $data['owner_password'],
            $data['owner_password_confirmation'],
            $data['existing_owner_id']
        );

        return DB::transaction(function () use ($data, $ownerMode, $ownerName, $ownerPhone, $ownerEmail, $ownerPassword, $existingOwnerId) {
            $data['uuid'] = (string) Str::uuid();
            $data['ntn_cnic'] = preg_replace('/\D/', '', (string) ($data['ntn_cnic'] ?? ''));

            if (($data['status'] ?? 'approved') === 'approved') {
                $data['approved_at'] = now();
                $data['approved_by'] = auth()->id();
            }

            /** @var Business $business */
            $business = Business::create($data);

            $linkedUser = null;
            if ($ownerMode === 'create_new' && !empty($ownerEmail)) {
                $linkedUser = User::create([
                    'name' => $ownerName,
                    'email' => $ownerEmail,
                    'phone' => $ownerPhone,
                    'password' => Hash::make($ownerPassword),
                    'status' => 'active',
                    'is_super_admin' => false,
                ]);
            } elseif ($ownerMode === 'link_existing' && !empty($existingOwnerId)) {
                $linkedUser = User::find($existingOwnerId);
            }

            if ($linkedUser) {
                $hasDefault = $linkedUser->businesses()->wherePivot('is_default', true)->exists();
                $business->users()->syncWithoutDetaching([
                    $linkedUser->id => [
                        'role' => 'owner',
                        'is_default' => !$hasDefault,
                    ],
                ]);
            }

            ActivityLog::record(
                'admin_business_registered',
                "Admin registered business '{$business->name}' (NTN: {$business->ntn_cnic})" . ($linkedUser ? " linked to {$linkedUser->email}." : '.'),
                [
                    'business_id' => $business->id,
                    'owner_email' => $linkedUser?->email,
                    'owner_mode' => $ownerMode,
                    'status' => $business->status,
                ],
                $business->id
            );

            return $business;
        });
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Business Registered & Linked')
            ->body('The business profile and owner login account have been created and linked successfully.');
    }
}
