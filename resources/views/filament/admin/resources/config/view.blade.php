<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="description">
            It is where all the system configurations are stored, each configuration below affects the system so it will have to be edited carefully!
        </x-slot>
        {{-- Content --}}
        @livewire('filament.config-form')


    </x-filament::section>

</x-filament-panels::page>
