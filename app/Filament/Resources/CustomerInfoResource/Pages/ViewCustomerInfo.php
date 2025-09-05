<?php

namespace App\Filament\Resources\CustomerInfoResource\Pages;

use App\Filament\Resources\CustomerInfoResource;
use Filament\Resources\Pages\Page;
use Filament\Actions;


class ViewCustomerInfo extends Page
{
    protected static string $resource = CustomerInfoResource::class;

    protected static string $view = 'filament.admin.resources.users.user-info';

    protected ?string $heading = 'Personal Information';

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit Info')
                ->url(route('filament.admin.resources.customer-infos.edit'))
                ->icon('heroicon-o-pencil')
                ->color('primary'),
            Actions\Action::make('payment')
                ->label('Deposit')
                ->url(route('filament.admin.resources.point-packages.buy'))
                ->icon('heroicon-o-credit-card')
                ->color('success'),
        ];
    }
}
