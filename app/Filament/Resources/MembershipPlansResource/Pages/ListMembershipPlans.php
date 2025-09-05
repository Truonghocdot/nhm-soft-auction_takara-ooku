<?php

namespace App\Filament\Resources\MembershipPlansResource\Pages;

use App\Filament\Resources\MembershipPlansResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMembershipPlans extends ListRecords
{
    protected static string $resource = MembershipPlansResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Membership Package',
            '' => 'List',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Create Membership Package'),
        ];
    }
}
