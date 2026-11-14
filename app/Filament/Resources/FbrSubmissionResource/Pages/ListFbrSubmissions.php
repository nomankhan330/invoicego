<?php

namespace App\Filament\Resources\FbrSubmissionResource\Pages;

use App\Filament\Resources\FbrSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListFbrSubmissions extends ListRecords
{
    protected static string $resource = FbrSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
