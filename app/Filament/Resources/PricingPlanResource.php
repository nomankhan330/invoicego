<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingPlanResource\Pages;
use App\Models\PricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PricingPlanResource extends Resource
{
    protected static ?string $model = PricingPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Website Pricing Plans';
    protected static ?string $navigationGroup = 'Users & Billing';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plan Information')
                    ->description('Configure the plan pricing, description, and feature list shown on the public website.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Plan Name')
                            ->placeholder('e.g. Starter, Professional, Enterprise')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Plan Slug')
                            ->required()
                            ->unique(ignorable: fn (?PricingPlan $record) => $record),

                        Forms\Components\TextInput::make('tagline')
                            ->label('Short Tagline')
                            ->placeholder('e.g. Ideal for small traders and new businesses.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->label('Price Display')
                            ->placeholder('e.g. PKR 2,500 or Custom')
                            ->required(),

                        Forms\Components\TextInput::make('billing_period')
                            ->label('Billing Period')
                            ->placeholder('e.g. /month, /year, /volume')
                            ->default('/month')
                            ->required(),

                        Forms\Components\TagsInput::make('features')
                            ->label('Plan Features (Bullet Points)')
                            ->placeholder('Type feature and hit enter')
                            ->helperText('Each bullet point will show with a checkmark on the website.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('button_text')
                            ->label('CTA Button Text')
                            ->default('Get Started')
                            ->required(),

                        Forms\Components\Select::make('button_action')
                            ->label('CTA Button Action')
                            ->options([
                                'register' => 'Registration / Sign-In Flow',
                                'contact' => 'Open Contact Sales Modal',
                            ])
                            ->default('register')
                            ->required(),

                        Forms\Components\Toggle::make('is_popular')
                            ->label('Most Popular Badge')
                            ->helperText('Highlights this card with a prominent border and "Most Popular" badge.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active on Website')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Plan Name')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (PricingPlan $record) => "{$record->price} {$record->billing_period}"),

                Tables\Columns\IconColumn::make('is_popular')
                    ->label('Most Popular')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active on Site')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
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
            'index' => Pages\ListPricingPlans::route('/'),
            'create' => Pages\CreatePricingPlan::route('/create'),
            'edit' => Pages\EditPricingPlan::route('/{record}/edit'),
        ];
    }
}
