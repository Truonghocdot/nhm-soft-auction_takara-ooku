<?php

namespace App\Filament\Resources\BuyMembershipResource\Pages;

use App\Filament\Resources\BuyMembershipResource;
use Filament\Resources\Pages\Page;
use Filament\Actions;

class UpgradeMembership extends Page
{
    protected static string $resource = BuyMembershipResource::class;

    protected static string $view = 'filament.admin.resources.membership.upgrade-membership';

    protected ?string $heading = 'Upgrade membership package';

    public function getBreadcrumbs(): array
    {
        return [
            BuyMembershipResource::getUrl() => "Membership Package",
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make("back_to_view_membership")
                ->icon('heroicon-o-arrow-uturn-left')
                ->label('Come back')
                ->url(fn (): string => BuyMembershipResource::getUrl())
        ];
    }
}
