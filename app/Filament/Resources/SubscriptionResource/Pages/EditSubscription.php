<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['plan_name'])) {
            try {
                $plan = \App\Models\PricingPlan::where('name', $data['plan_name'])->first();
                if ($plan) {
                    $data['pricing_plan_id'] = $plan->id;
                }
            } catch (\Throwable) {}
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

