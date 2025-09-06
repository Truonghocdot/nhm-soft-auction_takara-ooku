<?php

namespace App\Filament\Resources;

use App\Enums\Permission\RoleConstant;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Utils\HelperFunc;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $modelLabel = 'Category';
    protected static ?string $pluralModelLabel = 'Category';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(RoleConstant::ADMIN);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Category name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) return;
                        $baseSlug = \Illuminate\Support\Str::slug($state);
                        $slug = $baseSlug . '-' . HelperFunc::getTimestampAsId();
                        $set('slug', $slug);
                    }),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->directory('categories'),
                SelectTree::make('parent_id')
                    ->label('Parent category')
                    ->relationship('parent', 'name', 'parent_id')
                    ->searchable()
                    ->placeholder('Choose parent category')
                    ->nullable(),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(1000),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(fn($record) => HelperFunc::generateURLFilePath($record->image))
                    ->disk('public'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Category name')
                    ->formatStateUsing(fn($state, $record) => str_repeat('&nbsp;&nbsp;&nbsp;', $record->level) . $state)
                    ->html()
                    ->sortable()
                    ->limit(50)
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('full_path')
                    ->label('Path')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('parent.full_path')
                    ->label('Parent category')
                    ->sortable()
                    ->placeholder('None')
                    ->limit(50),

                Tables\Columns\TextColumn::make('children_count')
                    ->label('Number of subcategories')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->counts('children')
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Product quantity')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn(string $state): string => $state === 'active' ? 'Active' : 'Inactive'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent category')
                    ->options(function () {
                        $categories = Category::all();
                        $options = [];

                        foreach ($categories as $category) {
                            $options[$category->id] = $category->full_path;
                        }

                        return $options;
                    })
                    ->placeholder('All categories'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),

                Tables\Actions\Action::make('softDelete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm delete category')
                    ->modalDescription('Are you sure you want to delete this category? The category will be moved to the trash.')
                    ->action(function (Category $record) {
                        if ($record->children()->exists()) {
                            Notification::make()
                                ->title('Error')
                                ->body('Category cannot be deleted because it has child categories.')
                                ->danger()
                                ->send();
                            return;
                        }

                        if ($record->products()->exists()) {
                            Notification::make()
                                ->title('Error')
                                ->body('Category cannot be deleted because it has related products.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->delete();
                        Notification::make()
                            ->title('Success')
                            ->body('Category has been successfully moved to trash!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn(Category $record): bool => $record->trashed())
                    ->action(function (Category $record) {
                        $record->restore();
                        Notification::make()
                            ->title('Success')
                            ->body('Category has been successfully restored!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Permanently delete')
                        ->requiresConfirmation()
                        ->modalHeading('Confirm permanent delete')
                        ->modalDescription('Are you sure you want to permanently delete the selected categories? This action cannot be performed undo.')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->children()->exists()) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body("Cannot delete category '{$record->name}' because it has child categories.")
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                if ($record->products()->exists()) {
                                    Notification::make()
                                        ->title('Error')
                                        ->body("Could not delete category '{$record->name}' because it has related products.")
                                        ->danger()
                                        ->send();
                                    return;
                                }
                            }

                            $records->each->forceDelete();
                            Notification::make()
                                ->title('Success')
                                ->body('Categories have been permanently deleted successfully!')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->action(function ($records) {
                            $records->each->restore();
                            Notification::make()
                                ->title('Success')
                                ->body('Categories have been permanently restored successfully!')
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $ids = collect(\App\Models\Category::getTreeList())->pluck('id')->toArray();
        return parent::getEloquentQuery()
            ->whereIn('id', $ids)
            ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
