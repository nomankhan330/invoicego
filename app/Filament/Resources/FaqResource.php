<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'Website FAQs';
    protected static ?string $navigationGroup = 'System & Support';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('FAQ Information')
                    ->description('Manage questions and answers displayed in the Frequently Asked Questions section of the website.')
                    ->schema([
                        Forms\Components\TextInput::make('question')
                            ->label('Question')
                            ->placeholder('e.g. Is my business information secure?')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category')
                            ->label('Category')
                            ->options([
                                'General' => 'General',
                                'FBR Integration' => 'FBR Integration',
                                'Account & Billing' => 'Account & Billing',
                                'Invoicing & Features' => 'Invoicing & Features',
                                'Security & Privacy' => 'Security & Privacy',
                                'Support' => 'Support',
                            ])
                            ->default('General')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first on the website.')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active on Website')
                            ->helperText('When enabled, this FAQ is visible in the website accordion.')
                            ->default(true),

                        Forms\Components\Textarea::make('answer')
                            ->label('Answer')
                            ->placeholder('Provide a clear, detailed answer...')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active on Website'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'General' => 'General',
                        'FBR Integration' => 'FBR Integration',
                        'Account & Billing' => 'Account & Billing',
                        'Invoicing & Features' => 'Invoicing & Features',
                        'Security & Privacy' => 'Security & Privacy',
                        'Support' => 'Support',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visibility')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
