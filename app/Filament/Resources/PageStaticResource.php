<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageStaticResource\Pages;
use App\Models\PageStatic;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use App\Enums\CommonConstant;
use App\Enums\Permission\RoleConstant;
use Filament\Forms\Components\RichEditor;

class PageStaticResource extends Resource
{
    protected static ?string $model = PageStatic::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Website';
    protected static ?string $modelLabel = 'Static page';
    protected static ?string $pluralModelLabel = 'Static page';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Static page';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Main information')
                        ->description('Basic information of the page')
                        ->schema([
                            TextInput::make('title')
                                ->label('Title')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set, Forms\Get $get, $record) {
                                    if ($operation !== 'create' || !$state) {
                                        return;
                                    }

                                    $baseSlug = Str::slug($state);
                                    $uniqueSlug = static::generateUniqueSlug($baseSlug, $record?->id);
                                    $set('slug', $uniqueSlug);
                                })
                                ->maxLength(255),

                            TextInput::make('slug')
                                ->label('URL Path')
                                ->required()
                                ->maxLength(255)
                                ->unique(PageStatic::class, 'slug', ignoreRecord: true)
                                ->helperText('Friendly URL, eg: gioi-thieu')
                                ->rules(['alpha_dash'])
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get, $record) {
                                    if (!$state) return;

                                    $baseSlug = Str::slug($state);
                                    $uniqueSlug = static::generateUniqueSlug($baseSlug, $record?->id);

                                    if ($uniqueSlug !== $state) {
                                        $set('slug', $uniqueSlug);
                                    }
                                }),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    CommonConstant::INACTIVE => 'Hidden',
                                    CommonConstant::ACTIVE => 'Activate'
                                ])
                                ->default(CommonConstant::INACTIVE)
                                ->required()
                                ->native(false)
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    if ($state == CommonConstant::ACTIVE && !$get('published_at')) {
                                        $set('published_at', now());
                                    } elseif ($state == CommonConstant::INACTIVE) {
                                        $set('published_at', null);
                                    }
                                }),

                            DateTimePicker::make('published_at')
                                ->label('Posting time')
                                ->helperText('Automatically update when status changes')
                                ->native(false)
                                ->visible(fn(Forms\Get $get) => $get('status') == CommonConstant::ACTIVE),
                        ])
                        ->columnSpan(2),

                    Section::make('SEO & Media')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Representative image')
                                ->image()
                                ->directory('pages')
                                ->maxSize(2048)
                                ->imagePreviewHeight('120'),

                            TextInput::make('meta_title')
                                ->label('Meta Title')
                                ->maxLength(255),

                            Textarea::make('meta_keywords')
                                ->label('Meta Keywords')
                                ->maxLength(255)
                                ->rows(2),

                            Textarea::make('meta_description')
                                ->label('Meta Description')
                                ->rows(3)
                                ->maxLength(500),
                        ])
                        ->columnSpan(1),
                ]),

                // Content Section 
                Section::make('Content')
                    ->schema([
                        Textarea::make('excerpt')
                            ->label('Short description')
                            ->rows(3)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Main content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'blockquote',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                                'code',
                                'codeBlock',
                                'h2',
                                'h3'
                            ])
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Photo')
                    ->circular()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('URL copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state): string => $state == CommonConstant::ACTIVE ? 'Activate' : 'Hidden')
                    ->badge()
                    ->color(fn($state): string => $state == CommonConstant::ACTIVE ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Posting time')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        CommonConstant::INACTIVE => 'Hidden',
                        CommonConstant::ACTIVE => 'Activate',
                    ]),
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('published_range')
                    ->label('Posting interval')
                    ->form([
                        DateTimePicker::make('published_from')
                            ->label('From date')
                            ->native(false),
                        DateTimePicker::make('published_until')
                            ->label('To date')
                            ->native(false),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['published_from'], fn($query, $date) => $query->where('published_at', '>=', $date))
                            ->when($data['published_until'], fn($query, $date) => $query->where('published_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view')
                    ->label('View page')
                    ->url(fn(PageStatic $record): string => route('page.static', $record->slug))
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->visible(fn(PageStatic $record): bool => $record->status == CommonConstant::ACTIVE),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-m-eye')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => CommonConstant::ACTIVE,
                                    'published_at' => $record->published_at ?? now()
                                ]);
                            });
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Hide')
                        ->icon('heroicon-m-eye-slash')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update([
                                'status' => CommonConstant::INACTIVE,
                                'published_at' => null
                            ]);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected static function generateUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = PageStatic::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
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
            'index' => PageStaticResource\Pages\ListPageStatics::route('/'),
            'create' => PageStaticResource\Pages\CreatePageStatic::route('/create'),
            'edit' => PageStaticResource\Pages\EditPageStatic::route('/{record}/edit'),
        ];
    }
}
