@assets
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin/upgrade-membership.js'])
@endassets

<x-filament::section>
    @if (!$this->nextStepUpgrade)
        <div class="grid grid-cols-4">
            @if ($currentplan)
                <div class="col-span-4">
                    <div class="!w-[400px] bg-center bg-cover mx-auto ">
                        <div
                            class="block w-full max-w-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                            <div class="p-5">
                                <p class="text-3xl text-center mb-5">Current Plans</p>
                                @if (!empty($currentplan->badge))
                                    @php
                                        $badgeStyle = !empty($currentplan->badge_color)
                                            ? "background-color: {$currentplan->badge_color};"
                                            : "backgroud-color: '#ccc";
                                    @endphp
                                    <x-filament::badge size="sm" style="{{ $badgeStyle }}" class="mb-2">
                                        <p class="text-gray-600 dark:text-white">{{ $currentplan->badge }}</p>
                                    </x-filament::badge>
                                @endif
                                <div class="flex flex-col gap-4 mb-4">
                                    {{-- Tiêu đề --}}
                                    <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                        {{ $currentplan->name }}
                                    </h5>
                                    {{-- Điểm --}}
                                    <h6 class="text-3xl font-bold text-gray-900 dark:text-white">
                                        {{ $currentplan->price }}
                                        POINT
                                        <span class="text-sm text-gray-500 dark:text-white">/
                                            {{ $currentplan->duration }}
                                            tháng</span>
                                    </h6>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $currentplan->description }}
                                    </p>
                                    <div
                                        class="block w-full max-w-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 p-2">
                                        <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">
                                            @if ($currentplan->config['featured_listing'])
                                                <li class="flex currentplans-center">
                                                    <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                    </svg>Products for sale are displayed in a prominent position
                                                </li>
                                            @endif
                                            @if ($currentplan->config['priority_support'])
                                                <li class="flex currentplans-center">
                                                    <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                    </svg>Get priority support when problems arise
                                                </li>
                                            @endif
                                            @if ($currentplan->config['discount_percentage'] > 0)
                                                <li class="flex currentplans-center">
                                                    <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                    </svg>Get {{ $currentplan->config['discount_percentage'] }} % off on
                                                    product
                                                </li>
                                            @endif
                                            @if ($currentplan->config['free_product_listing'])
                                                <li class="flex currentplans-center">
                                                    <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                    </svg>Post your products for free
                                                </li>
                                            @elseif($currentplan->config['max_products_per_month'] > 0)
                                                <li class="flex currentplans-center">
                                                    <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                    </svg>Free listing
                                                    {{ $currentplan->config['max_products_per_month'] }}
                                                    products/month
                                                </li>
                                            @endif
                                            @if ($currentplan->config['free_auction_participation'])
                                                <li class="flex currentplans-center">
                                                    <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                    </svg>Join the free bid
                                                </li>
                                            @endif

                                        </ul>
                                    </div>
                                </div>
                                <!-- Khi nhấn vào nút Mua ngay, giá trị id của membership sẽ được gán cho Alpine -->
                                <x-filament::button wire:key="{{ $currentplan->id }}" class="w-full" color="success"
                                    icon="heroicon-m-check-badge"
                                    wire:click="onNextStep('{{ $currentplan->id }}')">Renew package
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-span-4 flex flex-col gap-4 w-full">
                <h3 class="text-3xl text-center my-2">Upgradeable Plans</h3>
                @if ($this->list->isEmpty())
                    <x-filament::section>
                        <div class="text-center text-gray-500">No membership plans</div>
                    </x-filament::section>
                @else
                    <div id="membership_list" class="w-full">
                        <div class="swiper-wrapper">
                            @foreach ($this->list as $item)
                                <div class="!w-[400px] bg-center bg-cover swiper-slide">
                                    <div
                                        class="block w-full max-w-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700">
                                        <div class="p-5">

                                            @if (!empty($item->badge))
                                                @php
                                                    $badgeStyle = !empty($item->badge_color)
                                                        ? "background-color: {$item->badge_color};"
                                                        : "backgroud-color: '#ccc";
                                                @endphp
                                                <x-filament::badge size="sm" style="{{ $badgeStyle }}"
                                                    class="mb-2">
                                                    <p class="text-gray-600 dark:text-white">{{ $item->badge }}</p>
                                                </x-filament::badge>
                                            @endif
                                            <div class="flex flex-col gap-4 mb-4">
                                                {{-- Tiêu đề --}}
                                                <h5
                                                    class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                                                    {{ $item->name }}
                                                </h5>
                                                {{-- Điểm --}}
                                                <h6 class="text-3xl font-bold text-gray-900 dark:text-white">
                                                    {{ $item->price }}
                                                    POINT
                                                    <span class="text-sm text-gray-500 dark:text-white">/
                                                        {{ $item->duration }}
                                                        tháng</span>
                                                </h6>
                                                <p class="text-gray-900 dark:text-white">
                                                    {{ $item->description }}
                                                </p>
                                                <div
                                                    class="block w-full max-w-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 p-2">
                                                    <ul
                                                        class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">
                                                        @if ($currentplan->config['featured_listing'])
                                                            <li class="flex currentplans-center">
                                                                <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                                    aria-hidden="true"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                                </svg>Products for sale are displayed in a prominent
                                                                position
                                                            </li>
                                                        @endif
                                                        @if ($currentplan->config['priority_support'])
                                                            <li class="flex currentplans-center">
                                                                <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                                    aria-hidden="true"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                                </svg>Get priority support when problems arise
                                                            </li>
                                                        @endif
                                                        @if ($currentplan->config['discount_percentage'] > 0)
                                                            <li class="flex currentplans-center">
                                                                <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                                    aria-hidden="true"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                                </svg>Get
                                                                {{ $currentplan->config['discount_percentage'] }} % off
                                                                on
                                                                product
                                                            </li>
                                                        @endif
                                                        @if ($currentplan->config['free_product_listing'])
                                                            <li class="flex currentplans-center">
                                                                <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                                    aria-hidden="true"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                                </svg>Post your products for free
                                                            </li>
                                                        @elseif($currentplan->config['max_products_per_month'] > 0)
                                                            <li class="flex currentplans-center">
                                                                <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                                    aria-hidden="true"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                                </svg>Free listing
                                                                {{ $currentplan->config['max_products_per_month'] }}
                                                                products/month
                                                            </li>
                                                        @endif
                                                        @if ($currentplan->config['free_auction_participation'])
                                                            <li class="flex currentplans-center">
                                                                <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                                                    aria-hidden="true"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                                </svg>Join the free bid
                                                            </li>
                                                        @endif

                                                    </ul>
                                                </div>
                                            </div>
                                            <!-- Khi nhấn vào nút Mua ngay, giá trị id của membership sẽ được gán cho Alpine -->
                                            <x-filament::button wire:key="{{ $item->id }}" class="w-full"
                                                color="success" icon="heroicon-m-check-badge"
                                                wire:click="onNextStep('{{ $item->id }}')">
                                                Upgrade now
                                            </x-filament::button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <x-slot name="heading">
            Payment for membership upgrade
        </x-slot>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <img src="{{ $dataTransfer['urlBankQrcode'] }}" class="w-full h-auto rounded-lg" alt="QR Code">
            <div
                class="block w-full max-w-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 h-fit">
                <div class="p-5">
                    @if (!empty($membershipPay->badge))
                        @php
                            $badgeStyle = !empty($membershipPay->badge_color)
                                ? "background-color: {$membershipPay->badge_color};"
                                : "backgroud-color: '#ccc";
                        @endphp
                        <x-filament::badge size="sm" style="{{ $badgeStyle }}" class="mb-2">
                            <p class="text-gray-600 dark:text-white">{{ $membershipPay->badge }}</p>
                        </x-filament::badge>
                    @endif
                    <div class="flex flex-col gap-4 mb-4">
                        {{-- Tiêu đề --}}
                        <h5 class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">
                            {{ $membershipPay->name }}
                        </h5>
                        {{-- Điểm --}}
                        <h6 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $membershipPay->price }}
                            POINT
                            <span class="text-sm text-gray-500 dark:text-white">/ {{ $membershipPay->duration }}
                                tháng</span>
                        </h6>
                        <p class="text-gray-900 dark:text-white">
                            {{ $membershipPay->description }}
                        </p>
                        <div
                            class="block w-full max-w-sm bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 p-2">
                            <ul class="max-w-md space-y-1 text-gray-500 list-inside dark:text-gray-400">
                                @if ($currentplan->config['featured_listing'])
                                    <li class="flex currentplans-center">
                                        <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                        </svg>Products for sale are displayed in a prominent
                                        position
                                    </li>
                                @endif
                                @if ($currentplan->config['priority_support'])
                                    <li class="flex currentplans-center">
                                        <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                        </svg>Get priority support when problems arise
                                    </li>
                                @endif
                                @if ($currentplan->config['discount_percentage'] > 0)
                                    <li class="flex currentplans-center">
                                        <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                        </svg>Get
                                        {{ $currentplan->config['discount_percentage'] }} % off
                                        on
                                        product
                                    </li>
                                @endif
                                @if ($currentplan->config['free_product_listing'])
                                    <li class="flex currentplans-center">
                                        <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                        </svg>Post your products for free
                                    </li>
                                @elseif($currentplan->config['max_products_per_month'] > 0)
                                    <li class="flex currentplans-center">
                                        <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                        </svg>Free listing
                                        {{ $currentplan->config['max_products_per_month'] }}
                                        products/month
                                    </li>
                                @endif
                                @if ($currentplan->config['free_auction_participation'])
                                    <li class="flex currentplans-center">
                                        <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 shrink-0"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                        </svg>Join the free bid
                                    </li>
                                @endif

                            </ul>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                        <!-- Primary Button -->
                        <x-filament::button class="w-full" color="success" icon="heroicon-m-check-badge"
                            wire:click="submitUpgrade('{{ \App\Enums\PayTypes::QRCODE->value }}')">
                            Payment confirmation
                        </x-filament::button>

                        <!-- Secondary Button: Outline -->
                        <x-filament::button class="w-full mt-2"
                            wire:click="submitUpgrade('{{ \App\Enums\PayTypes::POINTS->value }}')"
                            color="{{ $user->current_balance >= $membershipPay->price ? 'success' : 'danger' }}"
                            icon="{{ $user->current_balance >= $membershipPay->price ? 'heroicon-m-check-badge' : 'heroicon-m-x-mark' }}">
                            @if ($user->current_balance >= $membershipPay->price)
                                Pay with points
                            @else
                                Not enough points
                            @endif

                            <span class="text-xs">
                                (current balance: {{ number_format($user->current_balance) }})
                            </span>
                        </x-filament::button>
                    </div>

                </div>
            </div>
        </div>
    @endif
</x-filament::section>
