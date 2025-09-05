<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PointPackageResource\Pages;
use App\Models\PointPackage;
use Filament\Forms\Components\TextInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Enums\Permission\RoleConstant;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\Enums\TiptapOutput;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;

class PointPackageResource extends Resource
{
    protected static ?string $model = PointPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getNavigationUrl(): string
    {
        if (auth()->check() && auth()->user()->hasRole(RoleConstant::CUSTOMER)) {
            return static::getUrl('buy');
        }

        return static::getUrl('index');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Package name')
                            ->required()
                            ->maxLength(255),

                        TiptapEditor::make('description')
                            ->label('Description')
                            ->output(TiptapOutput::Html)
                            ->extraInputAttributes(['style' => 'min-height: 250px;'])
                            ->columnSpanFull()
                            ->nullable(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('points')
                                    ->label('Points')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required()
                                    ->helperText('The number of points the user receives when purchasing this package.'),

                                TextInput::make('discount')
                                    ->label('Discount (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0)
                                    ->required()
                                    ->helperText('Promotion Percentage, 0–100.'),
                            ]),

                        Forms\Components\Toggle::make('status')
                            ->label('Enable')
                            ->default(true)
                            ->helperText('Enable to make the package visible to users.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Tên')->searchable()->sortable()->limit(30),
                Tables\Columns\TextColumn::make('points')->label('Điểm')->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->label('Reduction (%)')
                    ->formatStateUsing(fn($state) => "{$state}%")
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created at')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
                Tables\Filters\Filter::make('min_points')
                    ->form([
                        TextInput::make('points')->label('Minimum points')->numeric(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['points'] ?? null, fn($q, $v) => $q->where('points', '>=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPointPackages::route('/'),
            'create' => Pages\CreatePointPackage::route('/create'),
            'edit' => Pages\EditPointPackage::route('/{record}/edit'),
            'buy' => Pages\BuyPointPackage::route('/buy')
        ];
    }
}
