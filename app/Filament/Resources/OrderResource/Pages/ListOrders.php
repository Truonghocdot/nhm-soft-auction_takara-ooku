<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\Permission\RoleConstant;
use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Order',
            '' => 'List',
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create new order'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        $user = auth()->user();
        return [
            null => Tab::make('All'),
            'Pending' => Tab::make()->query(fn($query) => $query->where('status', '1')),
            'Confirmed' => Tab::make()->query(fn($query) => $query->where('status', '2')),
            'Delivering' => Tab::make()->query(fn($query) => $query->where('status', '3')),
            'Delivered' => Tab::make()->query(fn($query) => $query->where('status', '4')),
            'Cancelled' => Tab::make()->query(fn($query) => $query->where('status', '5')),
        ];
    }
}
