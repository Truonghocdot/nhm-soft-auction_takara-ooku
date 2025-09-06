<?php

namespace App\Livewire\Filament\TransactionAdmin;

use App\Enums\Transactions\TransactionPaymentStatus;
use App\Enums\Transactions\TransactionPaymentType;
use App\Services\PointPackages\PointPackageServiceInterface;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;

class PointPackage extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    private PointPackageServiceInterface $service;

    public function boot(PointPackageServiceInterface $service)
    {
        $this->service = $service;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->service->getTransactionPaymentByType(TransactionPaymentType::RECHANGE_POINT))
            ->columns([
                TextColumn::make('description')
                    ->label('Transaction code')
                    ->copyable()
                    ->tooltip("Click to copy transaction code")
                    ->copyMessage('Copy transaction code successfully')
                    ->searchable(),
                TextColumn::make('transactionPoint.point')
                    ->label('Points'),
                TextColumn::make('user.email')
                    ->description(fn($record) => $record->user->name)
                    ->label('User')
                    ->searchable(),
                TextColumn::make('money')
                    ->label('Amount')
                    ->money('vnd'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => TransactionPaymentStatus::getLabel((int)$state))
                    ->color(fn(string $state): string => match (TransactionPaymentStatus::from((int)$state)) {
                        TransactionPaymentStatus::WAITING => 'warning',
                        TransactionPaymentStatus::ACTIVE => 'success',
                        TransactionPaymentStatus::FAILED => 'danger',
                        default => 'default',
                    }),
                TextColumn::make('created_at')
                    ->label('Transaction time')
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        TransactionPaymentStatus::WAITING->value => TransactionPaymentStatus::getLabel(TransactionPaymentStatus::WAITING->value),
                        TransactionPaymentStatus::ACTIVE->value => TransactionPaymentStatus::getLabel(TransactionPaymentStatus::ACTIVE->value),
                        TransactionPaymentStatus::FAILED->value => TransactionPaymentStatus::getLabel(TransactionPaymentStatus::FAILED->value),
                    ]),
            ])
            ->actions([
                Action::make('change_status_success')
                    ->label('Confirm')
                    ->visible(fn($record) => in_array($record->status, [TransactionPaymentStatus::WAITING->value, TransactionPaymentStatus::FAILED->value]))
                    ->action(function ($record) {
                        $result = $this->service->confirmPointTransaction($record, TransactionPaymentStatus::ACTIVE);
                        if ($result) {
                            Notification::make()
                                ->title('Success')
                                ->body('Transaction Confirmation Successful')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failure')
                                ->body('Transaction Confirmation Failed')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Transaction')
                    ->modalDescription('Are you sure you want to perform this action?')
                    ->modalSubmitActionLabel('Confirm')
                    ->icon('heroicon-o-check')
                    ->color('success'),
                Action::make('change_status_failed')
                    ->label('Cancel')
                    ->visible(fn($record) => $record->status == TransactionPaymentStatus::WAITING->value)
                    ->action(function ($record) {
                        $result = $this->service->confirmPointTransaction($record, TransactionPaymentStatus::FAILED);
                        if ($result) {
                            Notification::make()
                                ->title('Success')
                                ->body('Cancel transaction successfully')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed')
                                ->body('Cancel transaction failed')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cancel transaction')
                    ->modalDescription('Are you sure you want to perform this action?')
                    ->modalSubmitActionLabel('Confirm')
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger'),
            ])
            ->emptyStateHeading("No transactions have been made yet")
            ->emptyStateIcon("heroicon-o-rectangle-stack")
            ->emptyStateDescription("No transactions have been made yet. Please come back later.")
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc');
    }

    public function render()
    {
        return view('livewire.filament.transaction-admin.point-package');
    }
}
