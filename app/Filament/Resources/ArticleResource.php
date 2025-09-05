<?php

namespace App\Filament\Resources;

use App\Enums\Permission\RoleConstant;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use App\Utils\HelperFunc;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    public static ?string $navigationGroup = 'News';
    protected static ?string $modelLabel = 'Article';
    protected static ?string $navigationLabel = 'Article';
    protected static ?string $pluralModelLabel = 'News';
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(RoleConstant::ADMIN);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: 1000)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) return;
                                $baseSlug = \Illuminate\Support\Str::slug($state);
                                $slug = $baseSlug . '-' . HelperFunc::getTimestampAsId();
                                $set('slug', $slug);
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Path')
                            ->required()
                            ->readOnly()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\FileUpload::make('image')
                            ->label('Avatar Image')
                            ->image()
                            ->directory('articles')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jfif'])
                            ->maxSize(2048),
                        SelectTree::make('category_article_id')
                            ->label('Category')
                            ->relationship('category', 'name', 'parent_id')
                            ->searchable()
                            ->formatStateUsing(fn($state) => (string) $state)
                            ->expandSelected(true)
                            ->enableBranchNode()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        TiptapEditor::make('content')
                            ->label('Article content')
                            ->profile('default')
                            ->required()
                            ->columnSpanFull()
                            ->disk('public')
                            ->directory('uploads/editor')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/gif',
                                'application/pdf'
                            ])
                            ->imageResizeMode('force')
                            ->imageResizeTargetWidth('800')
                            ->imageResizeTargetHeight('600')
                            ->extraInputAttributes([
                                'style' => 'min-height: 400px;'
                            ])
                    ]),

                Forms\Components\Section::make('Installation')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Posted',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\TextInput::make('sort')
                            ->label('Order')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('view')
                            ->label('Views')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make()
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
        return $table->modifyQueryUsing(fn(Builder $query) => $query->with('author', 'category'))
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(fn($record) => HelperFunc::generateURLFilePath($record->image))
                    ->circular()
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->color('primary')
                    ->limit(40)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Path')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->formatStateUsing(fn($state) => $state),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(true)
                    ->colors([
                        'success' => 'published',
                        'gray' => 'draft',
                    ])
                    ->formatStateUsing(fn($state): string => match ($state) {
                        'published' => 'Published',
                        'draft' => 'Draft',
                        default => $state
                    }),

                Tables\Columns\TextColumn::make('view')
                    ->label('Views')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('sort')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->color('danger')
                    ->url(fn($record): string => '/admin/users/' . $record->author->id),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Posted',
                    ]),
                Tables\Filters\Filter::make('view')
                    ->form([
                        Forms\Components\TextInput::make('min_view')
                            ->label('Views from')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_view')
                            ->label('Incoming views')
                            ->numeric()
                    ])->query(function (Builder $query, array $data) {
                        return $query->when($data['min_view'], fn($q, $value) => $q->where('view', '>=', $value))
                            ->when($data['max_view'], fn($q, $value) => $q->where('view', '<=', $value));
                    }),
                Tables\Filters\Filter::make('publish_time')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('Post from'),
                        Forms\Components\DatePicker::make('start_to')
                            ->label('To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['start_from'], fn($q, $date) => $q->whereDate('start_time', '>=', $date))
                            ->when($data['start_to'], fn($q, $date) => $q->whereDate('start_time', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Delete'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete selected'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
