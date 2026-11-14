<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FbrSubmissionResource\Pages;
use App\Models\FbrSubmission;
use App\Services\Contracts\FbrIntegrationServiceInterface;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FbrSubmissionResource extends Resource
{
    protected static ?string $model = FbrSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';
    protected static ?string $navigationLabel = 'FBR / PRAL Integration';
    protected static ?string $navigationGroup = 'Invoices & FBR Integration';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('API Execution Details')
                    ->schema([
                        Forms\Components\Select::make('business_id')
                            ->label('Business')
                            ->relationship('business', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('scenario_code')
                            ->label('Scenario Code')
                            ->placeholder('Standard Submission')
                            ->disabled(),

                        Forms\Components\TextInput::make('environment')
                            ->label('Environment')
                            ->disabled(),

                        Forms\Components\TextInput::make('endpoint')
                            ->label('Endpoint URL')
                            ->disabled(),

                        Forms\Components\TextInput::make('http_status')
                            ->label('HTTP Status Code')
                            ->disabled(),

                        Forms\Components\TextInput::make('outcome')
                            ->label('FBR Outcome')
                            ->disabled(),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('Server IP Address')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('executed_at')
                            ->label('Executed At')
                            ->disabled(),

                        Forms\Components\Textarea::make('error_message')
                            ->label('FBR Error / Validation Message')
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(4),

                Forms\Components\Section::make('Request & Response Payload Inspection')
                    ->schema([
                        Forms\Components\KeyValue::make('request_payload')
                            ->label('Request JSON Sent to FBR')
                            ->disabled(),

                        Forms\Components\KeyValue::make('response_payload')
                            ->label('Raw PRAL Response Received')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('executed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('executed_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business / Taxpayer')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('environment')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'production' => 'danger',
                        'sandbox' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->fontFamily('mono')
                    ->limit(35),

                Tables\Columns\TextColumn::make('http_status')
                    ->label('HTTP')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === 200 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('outcome')
                    ->label('Outcome')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'rejected' => 'danger',
                        'network_error' => 'warning',
                        'reconciled' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('Server IP')
                    ->fontFamily('mono')
                    ->placeholder('127.0.0.1'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('outcome')
                    ->options([
                        'success' => 'Success',
                        'rejected' => 'Rejected',
                        'network_error' => 'Network Error',
                        'reconciled' => 'Reconciled',
                    ]),
                Tables\Filters\SelectFilter::make('environment')
                    ->options([
                        'sandbox' => 'Sandbox',
                        'production' => 'Production',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('retry')
                    ->label('Retry Submission')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->visible(fn (FbrSubmission $record) => $record->outcome !== 'success' && !empty($record->invoice_id))
                    ->requiresConfirmation()
                    ->action(function (FbrSubmission $record) {
                        if (!$record->invoice) {
                            Notification::make()->title('Invoice not found')->danger()->send();
                            return;
                        }
                        $fbrService = app(FbrIntegrationServiceInterface::class);
                        $res = $fbrService->submitInvoice($record->invoice);

                        if ($res['success']) {
                            Notification::make()
                                ->title('FBR Submission Retry Succeeded')
                                ->body("Invoice fiscalized with number {$res['fbr_invoice_number']}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('FBR Submission Retry Failed')
                                ->body(implode('; ', $res['errors'] ?? ['Rejected']))
                                ->danger()
                                ->send();
                        }
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
            'index' => Pages\ListFbrSubmissions::route('/'),
            'view' => Pages\ViewFbrSubmission::route('/{record}'),
        ];
    }
}
