<?php

namespace App\Livewire\Filament\TransactionAdmin;

use App\Enums\Membership\MembershipTransactionStatus;
use App\Services\Transaction\TransactionServiceInterface;
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

class Membership extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    private TransactionServiceInterface $service;

    public function boot(TransactionServiceInterface $service)
    {
        $this->service = $service;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->service->getQueryTransactionMembershipAdmin())
            ->columns([
                TextColumn::make('transaction_code')
                    ->label('Transaction code')
                    ->copyable()
                    ->tooltip("Click to copy transaction code")
                    ->copyMessage('Copy transaction code successfully')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->description(fn($record) => $record->user->name)
                    ->label('User')
                    ->searchable(),
                TextColumn::make('membershipUser.membershipPlan.name')
                    ->label('Membership Plan registration')
                    ->searchable(),
                TextColumn::make('money')
                    ->label('Amount')
                    ->money('vnd'),
                TextColumn::make('created_at')
                    ->label('Transaction time')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => MembershipTransactionStatus::getLabel((int)$state))
                    ->color(fn(string $state): string => match (MembershipTransactionStatus::from((int)$state)) {
                        MembershipTransactionStatus::WAITING => 'warning',
                        MembershipTransactionStatus::ACTIVE => 'success',
                        MembershipTransactionStatus::FAILED => 'danger',
                        default => 'default',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')
                    ->options([
                        MembershipTransactionStatus::WAITING->value => MembershipTransactionStatus::getLabel(MembershipTransactionStatus::WAITING->value),
                        MembershipTransactionStatus::ACTIVE->value => MembershipTransactionStatus::getLabel(MembershipTransactionStatus::ACTIVE->value),
                        MembershipTransactionStatus::FAILED->value => MembershipTransactionStatus::getLabel(MembershipTransactionStatus::FAILED->value),
                    ]),
            ])
            ->actions([
                Action::make('change_status_success')
                    ->label('Confirm')
                    ->visible(fn($record) => in_array($record->status, [MembershipTransactionStatus::WAITING->value, MembershipTransactionStatus::FAILED->value]))
                    ->action(function ($record) {
                        $result = $this->service->confirmMembershipTransaction($record, MembershipTransactionStatus::ACTIVE);
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
                    ->modalHeading('Confirm transaction')
                    ->modalDescription('Are you sure you want to perform this action?')
                    ->modalSubmitActionLabel('Confirm')
                    ->icon('heroicon-o-check')
                    ->color('success'),
                Action::make('change_status_failed')
                    ->label('Cancel')
                    ->visible(fn($record) => $record->status == MembershipTransactionStatus::WAITING->value)
                    ->action(function ($record) {
                        $result = $this->service->confirmMembershipTransaction($record, MembershipTransactionStatus::FAILED);
                        if ($result) {
                            Notification::make()
                                ->title('Success')
                                ->body('Cancel transaction successfully')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failure')
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
        return view('livewire.filament.transaction-admin.membership');
    }
}
