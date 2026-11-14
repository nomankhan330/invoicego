<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Businesses & Approvals';
    protected static ?string $navigationGroup = 'User & Business Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Identity & Registration')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Business Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ntn_cnic')
                            ->label('NTN / CNIC')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('strn')
                            ->label('STRN')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('province')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('sector')
                            ->required()
                            ->maxLength(150),
                        Forms\Components\TagsInput::make('business_natures')
                            ->label('Business Nature(s)'),
                        Forms\Components\Toggle::make('is_retailer_registered')
                            ->label('Registered Retailer in Sales Tax Profile'),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('SaaS Status & Governance')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'suspended' => 'Suspended',
                            ])
                            ->required(),
                        Forms\Components\Select::make('current_environment')
                            ->options([
                                'sandbox' => 'Sandbox Only',
                                'production' => 'Production Enabled',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('production_submissions_enabled')
                            ->label('Authorize Live Production Submissions'),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection / Suspension Reason')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Business')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('ntn_cnic')
                    ->label('NTN / CNIC')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('strn')
                    ->label('STRN')
                    ->searchable()
                    ->placeholder('N/A'),
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
                    ->options([
                        'All Other Sectors' => 'All Other Sectors',
                        'Steel' => 'Steel',
                        'Fast Moving Consumer Goods (FMCG)' => 'Fast Moving Consumer Goods (FMCG)',
                        'Textile' => 'Textile',
                        'Pharmaceutical' => 'Pharmaceutical',
                    ]),
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
