<?php

namespace App\Filament\Resources\MembershipPlansResource\Pages;

use App\Filament\Resources\MembershipPlansResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMembershipPlans extends ViewRecord
{
    protected static string $resource = MembershipPlansResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Membership Package',
            '' => 'View Membership Package',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
