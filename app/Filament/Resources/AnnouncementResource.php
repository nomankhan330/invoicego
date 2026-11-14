<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Announcements & Alerts';
    protected static ?string $navigationGroup = 'Support & Communication';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Announcement Broadcast')
                    ->description('Broadcast messages, system updates, and alerts to all registered business users.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Announcement Title / Header')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Notification Type')
                            ->options([
                                'info' => 'Information / Feature Update',
                                'warning' => 'Important Warning',
                                'maintenance' => 'Scheduled Maintenance Notice',
                                'fbr_alert' => 'FBR DI API Alert',
                            ])
                            ->default('info')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Publish to All Users Immediately')
                            ->default(true),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Publish From')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expire At (Optional)'),

                        Forms\Components\Textarea::make('message')
                            ->label('Announcement Message Content')
                            ->rows(5)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'warning' => 'warning',
                        'maintenance' => 'danger',
                        'fbr_alert' => 'primary',
                        default => 'secondary',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('From')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Until')
                    ->dateTime('d M Y')
                    ->placeholder('Indefinite'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'info' => 'Information',
                        'warning' => 'Warning',
                        'maintenance' => 'Maintenance',
                        'fbr_alert' => 'FBR Alert',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
