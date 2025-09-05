<?php

namespace App\Filament\Resources;

use App\Enums\Permission\RoleConstant;
use App\Filament\Resources\MembershipPlansResource\Pages;
use App\Models\MembershipPlan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;

class MembershipPlansResource extends Resource
{
    protected static ?string $model = MembershipPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(RoleConstant::ADMIN);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Membership Package Name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->live(onBlur: true),

                Forms\Components\TextInput::make('price')
                    ->label('Required Points')
                    ->required()
                    ->numeric()
                    ->minValue(0),

                Forms\Components\TextInput::make('duration')
                    ->label('Time')
                    ->required()
                    ->numeric()
                    ->placeholder('How many months')
                    ->minValue(0),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ])
                    ->default(true)
                    ->required(),
                Forms\Components\TextInput::make('sort')
                    ->label('Sort')
                    ->helperText("The smaller the number, the higher the package will appear in the list")
                    ->integer()
                    ->minValue(0),
                Forms\Components\TextInput::make('badge')
                    ->label('Membership package badge')
                    ->maxLength(255),

                ColorPicker::make('badge_color')
                    ->label('Choose badge color')
                    ->default('#ff5733'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\Section::make('Benefit configuration')
                    ->schema([
                        Forms\Components\Toggle::make('config.free_product_listing')
                            ->label('Free product listing fee')
                            ->helperText('Allow free listings')
                            ->default(false),

                        Forms\Components\Toggle::make('config.free_auction_participation')
                            ->label('Enroll in free auctions')
                            ->helperText('Enroll in free auctions')
                            ->default(false),

                        Forms\Components\Toggle::make('config.priority_support')
                            ->label('Priority support')
                            ->helperText('Get priority support when problems arise')
                            ->default(false),

                        Forms\Components\Toggle::make('config.featured_listing')
                            ->label('Featured products')
                            ->helperText('Products are displayed in a featured position')
                            ->default(false),

                        Forms\Components\TextInput::make('config.discount_percentage')
                            ->label('Discount Percentage (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0)
                            ->helperText('Discount on Purchase of Products'),

                        Forms\Components\TextInput::make('config.max_products_per_month')
                            ->label('Maximum number of products/month')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('0 = unlimited'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Package name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->suffix('₫')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Time')
                    ->suffix('month')
                    ->sortable(),

                Tables\Columns\IconColumn::make('config.free_product_listing')
                    ->label('Post free product')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('config.discount_percentage')
                    ->label('Discount')
                    ->suffix('%')
                    ->default('0%'),

                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Not working',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('View'),
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Delete Bulk'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembershipPlans::route('/'),
            'create' => Pages\CreateMembershipPlans::route('/create'),
            'view' => Pages\ViewMembershipPlans::route('/{record}'),
            'edit' => Pages\EditMembershipPlans::route('/{record}/edit'),
        ];
    }
}
