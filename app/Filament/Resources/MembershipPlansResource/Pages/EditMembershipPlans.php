<?php

namespace App\Filament\Resources\MembershipPlansResource\Pages;

use App\Filament\Resources\MembershipPlansResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMembershipPlans extends EditRecord
{
    protected static string $resource = MembershipPlansResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Membership Package',
            '' => 'Edit Membership Package',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
