<?php

namespace App\Filament\Resources;

use App\Enums\CommonConstant;
use App\Enums\Permission\RoleConstant;
use App\Enums\Product\ProductPaymentMethod;
use App\Enums\Product\ProductState;
use App\Enums\Product\ProductStatus;
use App\Enums\Product\ProductTypeSale;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use App\Models\Category;
use App\Services\Products\ProductServiceInterface;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use FilamentTiptapEditor\Enums\TiptapOutput;
use App\Utils\HelperFunc;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // protected static ?string $modelLabel = 'Sản phẩm';

    // protected static ?string  $pluralModelLabel = 'Sản phẩm';


    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole(RoleConstant::ADMIN) || $record->created_by == auth()->user()->id;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255)
                ->live(debounce: 2000)
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!$state) {
                        $set('slug', '');
                        return;
                    };
                    $baseSlug = Str::slug($state);
                    $slug = $baseSlug . '-' . HelperFunc::getTimestampAsId();
                    $set('slug', $slug);
                }),

            Forms\Components\TextInput::make('slug')
                ->label('Path')
                ->required()
                ->readOnly()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Forms\Components\Select::make('type_sale')
                ->label('Product type')
                ->options(ProductTypeSale::getOptions())
                ->required()
                ->default(ProductTypeSale::SALE->value)
                ->live(),
            Forms\Components\TextInput::make('price')
                ->label('Price')
                ->numeric()
                ->requiredIf('type_sale', ProductTypeSale::SALE->value)
                ->visible(function ($get) {
                    $t = $get('type_sale');
                    $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                    return $v === ProductTypeSale::SALE->value;
                })
                ->dehydrated(function ($get) {
                    $t = $get('type_sale');
                    $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                    return $v === ProductTypeSale::SALE->value;
                }),
            Forms\Components\TextInput::make('stock')
                ->label('Quantity')
                ->numeric()
                ->minValue(1)
                ->required(),
            Forms\Components\TextInput::make('min_bid_amount')
                ->label('Price Below')
                ->numeric()
                ->default(0)
                ->requiredIf('type_sale', ProductTypeSale::AUCTION->value)
                ->visible(function ($get) {
                    $t = $get('type_sale');
                    $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                    return $v === ProductTypeSale::AUCTION->value;
                }),
            Forms\Components\TextInput::make('max_bid_amount')
                ->label('Price Above')
                ->numeric()
                ->default(0)
                ->requiredIf('type_sale', ProductTypeSale::AUCTION->value)
                ->visible(function ($get) {
                    $t = $get('type_sale');
                    $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                    return $v === ProductTypeSale::AUCTION->value;
                })
                ->rules([
                    fn($get) => function ($attribute, $value, $fail) use ($get) {
                        $t = $get('type_sale');
                        $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                        if ($v === ProductTypeSale::AUCTION->value) {
                            $min = $get('min_bid_amount');
                            if ($min !== null && $value <= $min) {
                                $fail('The upper price must be greater than the lower price.');
                            }
                        }
                    }
                ]),
            Forms\Components\TextInput::make('step_price')
                ->label('Price step')
                ->numeric()
                ->requiredIf('type_sale', ProductTypeSale::AUCTION->value)
                ->visible(function ($get) {
                    $t = $get('type_sale');
                    $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                    return $v === ProductTypeSale::AUCTION->value;
                })
                ->afterStateHydrated(function (\Filament\Forms\Set $set, \Filament\Forms\Get $get) {
                    $productId = (int) ($get('id') ?? 0);
                    if ($productId > 0) {
                        $step = app(ProductServiceInterface::class)->getAuctionStepPriceByProductId($productId);
                        if ($step !== null) {
                            $set('step_price', $step);
                            return;
                        }
                    }
                    if ($get('step_price') === null) {
                        $set('step_price', 10000);
                    }
                }),
            Forms\Components\DateTimePicker::make('start_time')
                ->label('Start time')
                ->seconds(true)
                ->required()
                ->visible(function ($get) {
                    $t = $get('type_sale');
                    $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                    return $v === ProductTypeSale::AUCTION->value;
                }),
            Forms\Components\DateTimePicker::make('end_time')
                ->label('End time')
                ->seconds(true)
                ->required()
                ->visible(function ($get) {
                    $t = $get('type_sale');
                    $v = $t instanceof \App\Enums\Product\ProductTypeSale ? $t->value : (int) $t;
                    return $v === ProductTypeSale::AUCTION->value;
                }),
            SelectTree::make('category_id')
                ->label('Category')
                ->formatStateUsing(fn($state) => (string) $state)
                ->relationship('category', 'name', 'parent_id')
                ->searchable()
                ->expandSelected(true)
                ->enableBranchNode()
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(ProductStatus::getOptions())
                ->required(),
            Forms\Components\Select::make('state')
                ->label('Product Status')
                ->required()
                ->options(ProductState::getOptions())
                ->default(ProductState::UNUSED),
            Forms\Components\Select::make('pay_method')
                ->label('Payment method')
                ->required()
                ->options(ProductPaymentMethod::getOptions())
                ->default(ProductPaymentMethod::BOTH),
            Forms\Components\TextInput::make('brand')
                ->label('Brand'),
            Forms\Components\FileUpload::make('images')
                ->required()
                ->label('Figure image')
                ->multiple()
                ->image()
                ->directory('product-images')
                ->preserveFilenames()
                ->reorderable()
                ->columnSpanFull()
                ->hidden(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
            TiptapEditor::make('description')
                ->label('Product description')
                ->output(TiptapOutput::Html)
                ->extraInputAttributes([
                    'style' => 'min-height: 400px;'
                ])
                ->required()
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_hot')
                ->label('Priority products')
                ->reactive()
                ->afterStateUpdated(function (bool $state, callable $set) {
                    if (!auth()->user()->hasRole(RoleConstant::ADMIN)) {
                        $plansUsers = array_filter(auth()->user()['membershipUsers']->all(), fn($item) => $item['status'] == CommonConstant::ACTIVE);
                        $can = ! $plansUsers[array_key_first($plansUsers)]['membershipPlan']['config']['featured_listing'];

                        if ($can) {
                            $set('is_hot', false);

                            Notification::make()
                                ->title('Insufficient permissions')
                                ->warning()
                                ->body('You need to upgrade your membership plan to mark the product as preferred or activate another plan!')
                                ->send();
                        }
                    }
                })
                ->default(false),
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\TextInput::make('seo.title')
                        ->label('SEO Title')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('seo.description')
                        ->label('SEO Description')
                        ->rows(3),
                    Forms\Components\TextInput::make('seo.keywords')
                        ->label('SEO Keywords')
                        ->placeholder('Keywords, separated by commas')
                        ->maxLength(255),
                ])
                ->columns(1)
                ->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(fn(Builder $query) => $query->with('firstImage', 'category')->orderBy('created_at', 'desc'),)
            ->recordUrl(fn($record): string => static::getUrl('edit', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->limit(50)
                    ->label('Name')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('VND')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('view')
                    ->label('Views')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_bid_amount')
                    ->label('Lower price')
                    ->money('VND')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_bid_amount')
                    ->label('Price Above')
                    ->money('VND')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->color(fn($state) => match ($state) {
                        ProductStatus::ACTIVE->value => 'success',
                        ProductStatus::INACTIVE->value => 'warning',
                        default => 'default',
                    })->formatStateUsing(fn($state) => $state ? 'active' : 'inactive'),
                Tables\Columns\TextColumn::make('type_sale')
                    ->label('Product Type')
                    ->formatStateUsing(fn($state): string => $state == 1 ? 'Sell directly' : ($state == 2 ? 'Bid' : 'Unknown'))
                    ->searchable(),
                Tables\Columns\TextColumn::make("start_time")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime()
                    ->label('Start Time')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime()
                    ->label('End time')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('images.image_url')
                    ->label('Image')
                    ->getStateUsing(fn($record) => HelperFunc::generateURLFilePath($record->images->pluck('image_url')->first()))
                    ->disk('public')
                    ->height(100)
                    ->width(100)
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(isSeparate: true),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->limit(50),
                Tables\Columns\TextColumn::make('is_hot')
                    ->label('Priority products')
                    ->color(fn($state) => match ($state) {
                        0 => 'success',
                        1 => 'danger'
                    })
                    ->formatStateUsing(fn($state) => $state ? 'yes' : 'no'),
                Tables\Columns\TextColumn::make('state')
                    ->label('Product Status')
                    ->formatStateUsing(fn($state) => ProductState::getLabel(ProductState::from($state))),
                Tables\Columns\TextColumn::make('pay_method')
                    ->label('Payment method')
                    ->formatStateUsing(fn($state) => ProductPaymentMethod::getLabel(ProductPaymentMethod::from($state))),
                Tables\Columns\TextColumn::make('brand')
                    ->label("Brand"),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn() => Category::pluck('name', 'id')->toArray())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('type_sale')
                    ->label('Product type')
                    ->options([
                        1 => 'Direct sale',
                        2 => 'Bargain',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Disabled',
                    ]),

                Tables\Filters\SelectFilter::make('is_hot')
                    ->label('Preferred product')
                    ->options([
                        1 => 'Yes',
                        0 => 'No',
                    ]),

                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->label('Price from')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_price')
                            ->label('Arrival price')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_price'], fn($q, $value) => $q->where('price', '>=', $value))
                            ->when($data['max_price'], fn($q, $value) => $q->where('price', '<=', $value));
                    }),

                Tables\Filters\Filter::make('auction_time')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('Start from'),
                        Forms\Components\DatePicker::make('start_to')
                            ->label('Start to'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['start_from'], fn($q, $date) => $q->whereDate('start_time', '>=', $date))
                            ->when($data['start_to'], fn($q, $date) => $q->whereDate('start_time', '<=', $date));
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('View')
                    ->label('View'),

                Tables\Actions\EditAction::make('Edit')
                    ->label('Edit')->visible(
                        fn(Product $record): bool =>
                        auth()->user()->hasRole(RoleConstant::ADMIN)
                            || auth()->id() === $record->created_by
                    ),
                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn(Product $record) => auth()->user()->hasRole(RoleConstant::ADMIN) || auth()->id() === $record->created_by)
                    ->requiresConfirmation()
                    ->modalHeading('Confirm delete product')
                    ->modalDescription('Are you sure you want to delete this product? This action cannot be undone.')
                    ->action(function (Product $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Success')
                            ->body('Product deleted successfully!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Permanently delete')
                        ->requiresConfirmation()
                        ->modalHeading('Confirm permanently delete')
                        ->modalDescription('Are you sure you want to permanently delete the selected products? This action cannot be undone?')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (empty($record->auction()->get()) || empty($record->orderDetails()->get())) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body("Cannot delete product '{$record->name}' because it is associated with an order or online bid.")
                                        ->danger()
                                        ->send();
                                    return;
                                }
                            }
                            $records->each->forceDelete();
                            Notification::make()
                                ->title('Success')
                                ->body('The product has been permanently deleted successfully!')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->action(function ($records) {
                            $records->each->restore();
                            Notification::make()
                                ->title('Success')
                                ->body('Products have been successfully restored!')
                                ->success()
                                ->send();
                        })
                ]),
            ])
            ->emptyStateHeading("There are no products yet")
            ->emptyStateDescription('Once you list a product, it will appear here.')
            ->emptyStateIcon("heroicon-o-rectangle-stack")
            ->emptyStateActions([
                TableAction::make('create')
                    ->label('Post product')
                    ->url(route('filament.admin.resources.products.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductImageRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProducts::route('/{record}'),
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'slug';
    }
}
