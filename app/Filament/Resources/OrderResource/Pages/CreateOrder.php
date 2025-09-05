<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderDetail;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use App\Services\Orders\OrderService;
use Filament\Notifications\Notification;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    protected ?OrderService $orderService = null;

    protected function Service(): OrderService
    {
        return $this->orderService ??= app(OrderService::class);
    }

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(null);
    }

    protected function afterCreate(): void
    {
        /** @var OrderDetail $orderDetail */
        $orderDetail = $this->record;
        $formData = $this->form->getState();
        $paymentMethod = $formData['payment_method'] ?? '0';

        $this->Service()->afterCreate($orderDetail, $paymentMethod);

        if ($paymentMethod === '1') {
            Notification::make()
                ->title('Redirected to QR code payment page!')
                ->info()
                ->send();
        } else {
            Notification::make()
                ->title('Order created successfully!')
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        $formData = $this->form->getState();
        $paymentMethod = $formData['payment_method'] ?? '0';

        if ($paymentMethod === '1') {
            return route('filament.admin.resources.orders.qr-code', ['record' => $this->record]);
        }

        return parent::getRedirectUrl();
    }

    /** @return Step[] */
    protected function getSteps(): array
    {
        return [
            Step::make('Order details')
                ->schema([
                    Section::make()->schema(OrderResource::getDetailsFormSchema())->columns(),
                ]),

            Step::make('Products in order')
                ->schema([
                    Section::make()->schema([
                        OrderResource::getItemsRepeater(),
                        Placeholder::make('total_display')
                            ->label('Total order amount')
                            ->content(function (Get $get): string {
                                $items = $get('items') ?? [];
                                return $this->Service()->formatCurrency($this->Service()->calculateSubtotal($items));
                            })
                            ->columnSpan('full')
                            ->extraAttributes(['class' => 'text-lg font-bold text-green-600']),
                    ]),
                ]),
            Step::make('Payment')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Section::make()->schema(OrderResource::getPaymentFormSchema()),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->Service()->calculateOrderTotals($data);
    }
}
