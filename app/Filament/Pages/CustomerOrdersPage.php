<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Models\OrderDetail;
use App\Services\Payments\PaymentServiceInterface;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use App\Filament\Resources\OrderResource;
use App\Enums\Permission\RoleConstant;

class CustomerOrdersPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.admin.pages.customer-orders';
    protected static ?string $title = 'Customer Orders';
    protected static ?string $navigationLabel = 'Customer Orders';
    protected static ?string $navigationGroup = 'Order';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole(RoleConstant::CUSTOMER);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderDetail::query()
                    ->with(['payments', 'user'])
                    ->whereHas('items.product', function ($q) {
                        $q->where('created_by', auth()->id());
                    })
            )
            ->columns([
                Tabilities\Columns\TextColumn::make('code_orders')->label('Code order')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer'),
                Tables\Columns\TextColumn::make('total')->label('Total')->formatStateUsing(fn($state) => number_format((float) $state, 0, ',', '.') . ' $'),
                Tables\Columns\TextColumn::make('status')->label('Single status')->badge(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->state(function (OrderDetail $record) {
                        $payment = $record->payments->first();
                        return $payment?->status ?? 'pending';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'success' => 'Paid',
                        'pending' => 'Waiting for confirmation',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('seller_confirmed')
                    ->label('Seller confirmed')
                    ->state(function (OrderDetail $record) {
                        $payment = $record->payments->first();
                        return !empty($payment?->confirmation_at) ? 'confirmed' : 'not_confirmed';
                    })
                    ->badge()
                    ->color(fn(string $state): string => $state === 'confirmed' ? 'success' : 'gray')
                    ->formatStateUsing(fn(string $state): string => $state === 'confirmed' ? 'Confirmed' : 'Not confirmed'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created date'),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm_payment')
                    ->label('Payment Confirmation')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(function (OrderDetail $record) {
                        $payment = $record->payments->first();
                        return $payment && empty($payment->confirmation_at);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Payment Confirmation')
                    ->modalDescription('Confirm that the customer has transferred the money. The system will save the confirmation time on this order.')
                    ->action(function (OrderDetail $record) {
                        $paymentService = app(PaymentServiceInterface::class);
                        $ok = $paymentService->confirmPaymentBySeller($record->id, auth()->id());
                        if ($ok) {
                            Notification::make()->title('Success')->body('Payment confirmed for order row.')->success()->send();
                        } else {
                            Notification::make()->title('Failure')->body('You do not have permission or the order has not been paid yet.')->danger()->send();
                        }
                    })
                    ->after(function () {
                        if (method_exists($this, 'refreshTable')) {
                            $this->refreshTable();
                        }
                    }),
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->modalHeading('Payment Status')
                    ->infolist(fn() => OrderResource::getInfolistSchema())
                    ->modalWidth(MaxWidth::SixExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->recordUrl(null);
    }
}
