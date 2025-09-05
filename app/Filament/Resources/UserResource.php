<?php

namespace App\Filament\Resources;

use App\Enums\Permission\RoleConstant;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'User';
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'User';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(RoleConstant::ADMIN);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),

                Forms\Components\Fieldset::make('Password')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Present Password')
                            ->readOnly()
                            ->placeholder('●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●')
                            ->disabled(fn($get,  $context) => $get('showChangePassword') !== true || $context === 'create')
                            ->default(fn($record) => $record?->password ?? '')
                            ->visible(fn($get, $record) => $record !== null && $get('showChangePassword') !== true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('changePassword')
                                    ->label('Change password')
                                    ->icon('heroicon-o-pencil')
                                    ->action(function ($get, $set) {
                                        $set('showChangePassword', true);
                                    })
                            ),

                        Forms\Components\TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                            ->required(fn($record) => $record === null)
                            ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->maxLength(255),

                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Identity Password')
                            ->password()
                            ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                            ->same('new_password')
                            ->required(fn($record) => $record === null),

                        Forms\Components\Hidden::make('showChangePassword')->default(false),
                    ]),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Select::make('role')
                    ->required()
                    ->label('RoleConstant')
                    ->options(function () {
                        if (auth()->user()->role === 'admin') {
                            return [
                                'user' => 'User',
                                'member' => 'Member',
                            ];
                        } else {
                            return [
                                'user' => 'User',
                            ];
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Name')
                ->sortable()
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->sortable()
                ->searchable(),
            Tables\Columns\ImageColumn::make('profile_photo_path')
                ->label('Photo')
                ->circular()
                ->defaultImageUrl(fn($record) => $record->profile_photo_url),
            Tables\Columns\TextColumn::make('phone')
                ->label('Phone number')
                ->searchable()
                ->default('no phone'),
            Tables\Columns\TextColumn::make('address')
                ->label('Address')
                ->sortable(),
            Tables\Columns\TextColumn::make('current_balance')
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.') . '₫')
                ->label('Balance')
                ->searchable()
                ->default(0),
            Tables\Columns\TextColumn::make('membership')
                ->searchable()
                ->formatStateUsing(fn($record): string => $record->activeMemberships->count() > 0 ? 'Membership' : 'Not registered')
                ->badge()
                ->color(fn($record): string => $record->activeMemberships->count() > 0 ? 'success' : 'danger'),
            Tables\Columns\TextColumn::make('reputation')
                ->label('Reputation')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\Action::make('manager')
                    ->label('Management')
                    ->icon('heroicon-o-user')
                    ->url(fn(User $record) => UserResource::getUrl('view', ['record' => $record])),
                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn() => auth()->user()?->role === 'admin')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Course Deletion')
                    ->modalDescription('Are you sure you want to delete this course? This action cannot be undone.')
                    ->action(function (User $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Success')
                            ->body('Course deleted successfully!')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('name')->label('Username'),
                                        Components\TextEntry::make('email')->label('Email'),
                                        Components\TextEntry::make('phone')->label('Phone number'),
                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('role')->label('Role'),
                                        Components\TextEntry::make('membership')->label('Membership')
                                            ->formatStateUsing(fn(bool $state): string => $state ? 'Membership' : 'Not registered')
                                            ->badge()
                                            ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                                    ]),
                                ]),
                            Components\ImageEntry::make('profile_photo_url')
                                ->label('Ảnh')
                                ->hiddenLabel()
                                ->grow(false),
                        ])->from('lg'),
                    ]),
                Components\Section::make('Cash flow history')
                    ->schema([
                        Components\ViewEntry::make('transaction_stats')
                            ->view('filament.admin.resources.users.user-transaction-stats')
                            ->columnSpanFull(),
                    ]),

                Components\Section::make('Transaction history')
                    ->schema([
                        Components\RepeatableEntry::make('transactions')
                            ->hiddenLabel()
                            ->schema([
                                Components\Grid::make(5)
                                    ->schema([
                                        Components\TextEntry::make('type_transaction')
                                            ->label('Transaction type')
                                            ->badge()
                                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                                'recharge_point' => 'Recharge',
                                                'bid' => 'Bid',
                                                'buy_product' => 'Buy product',
                                                default => 'Other',
                                            })
                                            ->color(fn(string $state): string => match ($state) {
                                                'recharge_point' => 'success',
                                                'bid' => 'warning',
                                                'buy_product' => 'danger',
                                                default => 'gray',
                                            }),
                                        Components\TextEntry::make('point_change')
                                            ->label('Balance after')
                                            ->formatStateUsing(
                                                fn($state) => ($state > 0 ? '+' : '') . number_format($state, 0, ',', '.') . '₫'
                                            )
                                            ->color(fn($state): string => $state > 0 ? 'success' : 'danger'),
                                        Components\TextEntry::make('point')
                                            ->label('Current balance')
                                            ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.') . ' $'),
                                        Components\TextEntry::make('created_at')
                                            ->label('Transaction date')
                                            ->dateTime(),
                                        Components\TextEntry::make('id')
                                            ->label('Transaction code')
                                            ->prefix('#'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Components\Section::make('More info')
                    ->schema([
                        Components\TextEntry::make('created_at')->label('Created date')->dateTime(),
                        Components\TextEntry::make('updated_at')->label('Updated date')->dateTime(),
                    ])
                    ->collapsible(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}')
        ];
    }
}
