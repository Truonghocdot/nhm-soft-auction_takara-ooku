<x-filament::section heading="Orders">
    <div class="flex flex-col gap-2">
        <x-filament::link :href="route('filament.admin.resources.orders.mine')">
            My Orders
        </x-filament::link>
        <x-filament::link :href="route('filament.admin.resources.orders.customers')">
            Customer Orders
        </x-filament::link>
    </div>
</x-filament::section>
