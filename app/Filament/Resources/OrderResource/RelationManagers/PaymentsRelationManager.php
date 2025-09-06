<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_detail_id')
                    ->label('Order code')
                    ->disabled()
                    ->columnSpan('full')
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->label('Payment amount')
                    ->numeric()
                    ->required(),

                Forms\Components\Select::make('payment_method')
                    ->label('Payment method')
                    ->options([
                        '0' => 'Direct transaction',
                        '1' => 'Bank transfer',
                    ])
                    ->required()
                    ->default('0'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Payment')
            ->columns([
                Tables\Columns\TextColumn::make('orderDetail.code_orders')
                    ->label('Order code')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Payment amount')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.') . ' ₫'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment method')
                    ->formatStateUsing(function ($state) {
                        if ($state == '1') {
                            return 'Bank transfer';
                        } else {
                            return 'Direct transaction';
                        }
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->payment_method == '0') {
                            return $state == 'success' ? 'Direct transaction' : 'Not received money yet';
                        }
                        if ($record->payment_method == '1') {
                            return $state == 'success' ? 'Paid' : 'Not paid yet';
                        }
                        return 'Unknown';
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ]);
    }
}
