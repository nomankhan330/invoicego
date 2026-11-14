<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Security & Audit Logs';
    protected static ?string $navigationGroup = 'System & Security';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Audit Trail Record')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Timestamp')
                            ->disabled(),

                        Forms\Components\Select::make('user_id')
                            ->label('User / Operator')
                            ->relationship('user', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('business_id')
                            ->label('Associated Business')
                            ->relationship('business', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('action')
                            ->label('Action Type')
                            ->disabled(),

                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),

                        Forms\Components\Textarea::make('description')
                            ->label('Event Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(),

                        Forms\Components\KeyValue::make('properties')
                            ->label('Context Properties & Request Metadata')
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Actor')
                    ->searchable()
                    ->placeholder('System / Guest')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('business.name')
                    ->label('Business')
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('action')
                    ->label('Event Action')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'login', 'auth' => 'info',
                        'fbr_submission' => 'success',
                        'user_created', 'invoice_created' => 'primary',
                        'warning' => 'warning',
                        'error', 'suspension', 'failed' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->placeholder('127.0.0.1'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'login' => 'User Login',
                        'logout' => 'User Logout',
                        'invoice_created' => 'Invoice Created',
                        'fbr_submission' => 'FBR Submission',
                        'user_activated' => 'User Activated',
                        'user_suspended' => 'User Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }
}
