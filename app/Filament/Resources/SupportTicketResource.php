<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Customer Support';
    protected static ?string $navigationGroup = 'Support & Communication';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Details')
                    ->schema([
                        Forms\Components\TextInput::make('ticket_number')
                            ->label('Ticket #')
                            ->disabled(),

                        Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->disabled(),

                        Forms\Components\Select::make('business_id')
                            ->label('Business')
                            ->relationship('business', 'name')
                            ->disabled(),

                        Forms\Components\TextInput::make('category')
                            ->label('Category')
                            ->disabled(),

                        Forms\Components\TextInput::make('priority')
                            ->label('Priority')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->columnSpanFull()
                            ->disabled(),

                        Forms\Components\Textarea::make('message')
                            ->label('User Message / Complaint')
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Admin Response')
                    ->schema([
                        Forms\Components\Textarea::make('admin_reply')
                            ->label('Official Admin Reply')
                            ->rows(4)
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\DateTimePicker::make('replied_at')
                            ->label('Replied At')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'high' => 'warning',
                        'medium' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'gray',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'Technical Issue' => 'Technical Issue',
                        'FBR Integration' => 'FBR Integration',
                        'Billing / Payment' => 'Billing / Payment',
                        'General Inquiry' => 'General Inquiry',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('quick_reply')
                    ->label('Reply')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('primary')
                    ->form([
                        Forms\Components\Textarea::make('admin_reply')
                            ->label('Your Reply')
                            ->required()
                            ->rows(4),
                        Forms\Components\Select::make('status')
                            ->label('Update Ticket Status')
                            ->options([
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->default('resolved')
                            ->required(),
                    ])
                    ->action(function (SupportTicket $record, array $data) {
                        $record->update([
                            'admin_reply' => $data['admin_reply'],
                            'status' => $data['status'],
                            'replied_by' => auth()->id(),
                            'replied_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Reply Sent')
                            ->body("Replied to Ticket #{$record->ticket_number} and updated status to {$data['status']}.")
                            ->success()
                            ->send();
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
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
