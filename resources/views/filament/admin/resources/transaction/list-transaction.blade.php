<x-filament-panels::page>
    <x-filament::section collapsible collapsed icon="heroicon-o-user-group">
        <x-slot name="heading">
            Membership Payment Transactions
        </x-slot>
        <x-slot name="description">
            This is a list of customer membership payment transactions.
        </x-slot>
        @livewire('filament.transaction-admin.membership')
    </x-filament::section>

    <x-filament::section collapsible collapsed icon="heroicon-o-user-group">
        <x-slot name="heading">
            Point Payment Transactions
        </x-slot>
        <x-slot name="description">
            This is a list of customer points payment transactions.
        </x-slot>
        @livewire('filament.transaction-admin.point-package')
    </x-filament::section>

</x-filament-panels::page>
