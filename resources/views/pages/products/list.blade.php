@extends('layouts.app')
@section('title',
    'Product list' .
    (isset($category) && $category ? ' - ' . $category['name'] : '') .
    ' -
    Takara-ooku')
@section('meta_description', 'View product list' . (isset($category) && $category ? ' in category ' . $category['name']
    : '') . ' with price, product type and sort filters.')
@section('meta_keywords', 'Bid, buy, sell, auctions, products' . (isset($category) && $category ? ', ' .
    $category['name'] : ''))
@section('og_title', 'Product list' . (isset($category) && $category ? ' - ' . $category['name'] : ''))
@section('og_description', 'View product list' . (isset($category) && $category ? ' in category ' . $category['name'] :
    '') . ' with price, product type and sort filters.')
@section('og_image', asset('images/auctions-og.jpg'))
@section('schema_type', 'CollectionPage')
@section('schema_name', 'Product list' . (isset($category) && $category ? ' - ' . $category['name'] : ''))

@section('content')
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-4 ">

        <nav class="bg-white rounded-lg shadow-sm p-3 sm:p-4 mb-4 sm:mb-6" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center space-x-1 sm:space-x-2 text-xs sm:text-sm">
                <li class="flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-slate-600 hover:underline flex items-center">
                        <x-heroicon-o-home class="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
                        <span class="hidden xs:inline">Home</span>
                        <span class="xs:hidden">Home</span>
                    </a>
                </li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-4 grid-cols-1 gap-4 lg:gap-6">
            <div class="lg:hidden mb-4">
                <button id="mobile-filter-toggle"
                    class="w-full bg-white border border-gray-300 rounded-lg px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                    <span class="flex items-center font-medium text-gray-700">
                        <x-heroicon-o-funnel class="h-5 w-5 mr-2" />
                        Filter & Sort
                    </span>
                    <x-heroicon-o-chevron-down class="h-5 w-5 text-gray-400 transform transition-transform"
                        id="filter-chevron" />
                </button>
            </div>
            <aside class="lg:col-span-1">
                <div id="filter-sidebar"
                    class="lg:block hidden bg-white rounded-lg shadow-sm p-4 sm:p-6 lg:sticky lg:top-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 pb-3 border-b border-gray-200 flex items-center">
                        <x-heroicon-o-funnel class="h-5 w-5 mr-2" /><span class="hidden sm:inline">Product Filter</span>
                        <span class="sm:hidden">Filter</span>
                    </h2>

                    <form id="filter-form" method="GET" action="{{ request()->url() }}" class="space-y-4 sm:space-y-6">
                        <div class="filter-group">
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center text-sm sm:text-base">
                                <x-heroicon-o-rectangle-stack class="h-4 w-4 mr-2 flex-shrink-0" /><span
                                    class="hidden sm:inline">Product Category</span>
                                <span class="sm:hidden">Category</span>
                            </h3>
                            <div class="mb-3">
                                <input type="text" id="category-search" placeholder="Search category..."
                                    class="input input-sm input-bordered w-full text-xs">
                            </div>
                            <div
                                class="category-tree-container max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2 bg-gray-50">
                                @if (isset($categories))
                                    <div class="space-y-1">
                                        @foreach ($categories->where('parent_id', null) as $parentCategory)
                                            @include('components.category-tree-item', [
                                                'category' => $parentCategory,
                                                'level' => 0,
                                                'selectedCategoryId' => request('category_id'),
                                            ])
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <input type="hidden" name="category_ids"
                                value="{{ request()->input('category_ids', request('category_id')) ?? '' }}"
                                id="selected-category-id">

                            <div id="selected-category-display"
                                class="mt-2 {{ request()->input('category_ids') || request('category_id') ? '' : 'hidden' }}">
                                <div class="bg-blue-50 border border-blue-200 rounded-md p-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs text-blue-700 font-medium">Selected Category</span>
                                        <button type="button" id="clear-category"
                                            class="text-blue-500 hover:text-blue-700 text-xs">Clear All</button>
                                    </div>
                                    <div id="selected-category-list" class="flex flex-wrap gap-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-group">
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center text-sm sm:text-base">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4 mr-2 flex-shrink-0" /><span
                                    class="hidden sm:inline">Product Name</span>
                                <span class="sm:hidden">Name</span>
                            </h3>
                            <div class="space-y-2">
                                <input type="text" name="product_name" value="{{ request('q') }}"
                                    placeholder="Nhập tên sản phẩm..."
                                    class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm">
                            </div>
                        </div>

                        <div class="filter-group">
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center text-sm sm:text-base">
                                <x-heroicon-o-tag class="h-4 w-4 mr-2 flex-shrink-0" /><span
                                    class="hidden sm:inline">Product Type</span>
                                <span class="sm:hidden">Type</span>
                            </h3>
                            <div class="space-y-2">
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="product_type" value=""
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('product_type', '') === '' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">All products</span>
                                </label>
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="product_type" value="auction"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('product_type') === 'auction' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">Bid item</span>
                                </label>
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="product_type" value="sale"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('product_type') === 'sale' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">Products for sale</span>
                                </label>
                            </div>
                        </div>
                        <div class="filter-group">
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center text-sm sm:text-base">
                                <x-heroicon-o-tag class="h-4 w-4 mr-2 flex-shrink-0" />
                                <span class="hidden sm:inline">Product status</span>
                                <span class="sm:hidden">Status</span>
                            </h3>
                            <div class="space-y-2">
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="state" value=""
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('state', '') === '' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">All products</span>
                                </label>
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="state" value="0"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('state') === '0' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">Unused</span>
                                </label>
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="state" value="1"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('state') === '1' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">Barely used</span>
                                </label>

                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="state" value="2"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('state') === '2' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">No noticeable scratches or dirt
                                        pay attention</span>
                                </label>
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="state" value="3"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('state') === '3' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">There are some scratches and dust
                                        dirty</span>
                                </label>
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="state" value="4"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('state') === '4' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">There are scratches and
                                        stains</span>
                                </label>
                                <label class="flex items-center hover:bg-gray-50 p-2 rounded cursor-pointer">
                                    <input type="radio" name="state" value="5"
                                        class="border-gray-300 text-blue-600 focus:ring-blue-500 flex-shrink-0"
                                        {{ request('state') === '5' ? 'checked' : '' }}>
                                    <span class="ml-2 text-xs sm:text-sm text-gray-600">General condition is poor</span>
                                </label>
                            </div>
                        </div>
                        <div class="filter-group">
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center text-sm sm:text-base">
                                <x-heroicon-o-currency-dollar class="h-4 w-4 mr-2 flex-shrink-0" />
                                <span class="hidden sm:inline">Price range</span>
                                <span class="sm:hidden">Price</span>
                            </h3>
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Word (đ)</label>
                                        <input type="number" name="price_min" value="{{ request('price_min') }}"
                                            placeholder="0" min="0"
                                            class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">To (đ)</label>
                                        <input type="number" name="price_max" value="{{ request('price_max') }}"
                                            placeholder="Unlimited" min="0"
                                            class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm">
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="text-xs text-gray-500 mb-2">Popular price range:</div>
                                    <div class="grid grid-cols-2 sm:grid-cols-1 gap-1">
                                        @php
                                            $priceRanges = [
                                                ['label' => 'Under 100K', 'max' => 100000],
                                                ['label' => '100K-500K', 'min' => 100000, 'max' => 500000],
                                                ['label' => '500K-1tr', 'min' => 500000, 'max' => 1000000],
                                                ['label' => '1tr-5tr', 'min' => 1000000, 'max' => 5000000],
                                                ['label' => 'Over 5tr', 'min' => 5000000],
                                        ]; @endphp
                                        @foreach ($priceRanges as $range)
                                            <button type="button" data-min="{{ $range['min'] ?? '' }}"
                                                data-max="{{ $range['max'] ?? '' }}"
                                                class="price-range-btn text-xs px-2 py-1 border border-gray-300 rounded hover:bg-blue-50 hover:border-blue-300 text-gray-600 text-center transition-colors">
                                                {{ $range['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h3 class="font-semibold text-gray-700 mb-3 flex items-center text-sm sm:text-base">
                                <x-heroicon-o-bars-arrow-down class="h-4 w-4 mr-2 flex-shrink-0" />
                                <span class="hidden sm:inline">Sort by</span>
                                <span class="sm:hidden">Sort</span>
                            </h3>
                            <select name="sort_by"
                                class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-xs sm:text-sm">
                                <option value="created_at_desc"
                                    {{ request('sort_by', 'created_at_desc') === 'created_at_desc' ? 'selected' : '' }}>
                                    Latest
                                </option>
                                <option value="created_at_asc"
                                    {{ request('sort_by') === 'created_at_asc' ? 'selected' : '' }}>
                                    Oldest
                                </option>
                                <option value="price_asc" {{ request('sort_by') === 'price_asc' ? 'selected' : '' }}>
                                    Price low to high
                                </option>
                                <option value="price_desc" {{ request('sort_by') === 'price_desc' ? 'selected' : '' }}>
                                    Price high to low
                                </option>
                                <option value="views_desc" {{ request('sort_by') === 'views_desc' ? 'selected' : '' }}>
                                    Most views
                                </option>
                                <option value="name_asc" {{ request('sort_by') === 'name_asc' ? 'selected' : '' }}>
                                    Name A-Z
                                </option>
                                <option value="name_desc" {{ request('sort_by') === 'name_desc' ? 'selected' : '' }}>
                                    Name Z-A
                                </option>
                            </select>
                        </div>

                        <div class="space-y-3 pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="w-full btn btn-neutral text-white font-medium py-2 px-4 rounded-md transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4 inline-block mr-2" />
                                <span class="hidden sm:inline">Apply filter</span>
                                <span class="sm:hidden">Filter</span>
                            </button>
                            <a href="{{ request()->url() }}"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors text-center block text-sm">
                                <x-heroicon-o-arrow-path class="h-4 w-4 inline-block mr-2" />
                                <span class="hidden sm:inline">Clear filter</span>
                                <span class="sm:hidden">Reset</span>
                            </a>
                        </div>
                    </form>
                </div>
            </aside>
            <main class="lg:col-span-3">
                <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2 truncate">
                                {{ isset($category) && $category && isset($category['name']) ? $category['name'] : 'Product' }}
                            </h1>

                            @if (isset($category) && $category && isset($category['description']) && $category['description'])
                                <p class="text-gray-600 mb-4 text-sm sm:text-base line-clamp-2 sm:line-clamp-none">
                                    {{ $category['description'] }}
                                </p>
                            @endif

                            <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-500">
                                <span class="flex items-center">
                                    <x-heroicon-o-cube class="h-4 w-4 mr-1" />
                                    {{ isset($products) && method_exists($products, 'total') ? number_format($products->total()) : '0' }}
                                    products
                                </span>
                                @if (request()->hasAny(['subcategories', 'product_type', 'price_min', 'price_max', 'sort_by']) &&
                                        request('product_type') !== 'all')
                                    <span class="text-blue-600 flex items-center">
                                        <x-heroicon-o-funnel class="h-4 w-4 mr-1" />Filtering
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if (isset($category) && $category && isset($category['image']) && $category['image'])
                            <div class="flex-shrink-0">
                                <img src="{{ asset('storage/' . $category['image']) }}"
                                    alt="{{ $category['name'] ?? 'Category' }}"
                                    class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded-lg shadow-sm" onerror="this.src='{{ asset('images/product_default.jpg') }}'">
                            </div>
                        @endif
                    </div>
                </div>

                @if (request()->hasAny([
                        'category_ids',
                        'category_id',
                        'product_type',
                        'price_min',
                        'price_max',
                        'state',
                        'product_name',
                        'sort_by',
                    ]))
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-medium text-blue-800 mb-2 text-sm sm:text-base">Filters currently applied:
                                </h3>
                                <div class="flex flex-wrap gap-2">

                                    @if (request('category_ids') || request('category_id'))
                                        @php
                                            $catIds = explode(',', request('category_ids', request('category_id')));
                                            $selectedCats = \App\Models\Category::whereIn('id', $catIds)
                                                ->pluck('name')
                                                ->toArray();
                                        @endphp
                                        @foreach ($selectedCats as $catName)
                                            <span
                                                class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $catName }}
                                            </span>
                                        @endforeach
                                    @endif

                                    @if (request('product_name'))
                                        <span
                                            class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                            Name: "{{ request('product_name') }}"
                                        </span>
                                    @endif

                                    @if (request('product_type') === 'auction')
                                        <span
                                            class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Paid products
                                        </span>
                                    @elseif(request('product_type') === 'sale')
                                        <span
                                            class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Products for sale
                                        </span>
                                    @endif

                                    @if (request()->filled('state'))
                                        @php
                                            $states = [
                                                '0' => 'Unused',
                                                '1' => 'Barely used',
                                                '2' => 'No noticeable scratches or dirt',
                                                '3' => 'Some scratches and dirt',
                                                '4' => 'Scratches and dirt',
                                                '5' => 'Overall poor condition',
                                            ];
                                        @endphp
                                        @if (isset($states[request('state')]))
                                            <span
                                                class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ $states[request('state')] }}
                                            </span>
                                        @endif
                                    @endif

                                    @if (request('price_min') || request('price_max'))
                                        <span
                                            class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Price:
                                            {{ request('price_min') ? number_format(request('price_min')) . 'd' : '0d' }} -
                                            {{ request('price_max') ? number_format(request('price_max')) . 'đ' : '∞' }}
                                        </span>
                                    @endif

                                    @if (request('sort_by'))
                                        @php
                                            $sortOptions = [
                                                'created_at_desc' => 'Newest',
                                                'created_at_asc' => 'Oldest',
                                                'price_asc' => 'Price low to high',
                                                'price_desc' => 'Price high to low',
                                                'views_desc' => 'Most viewed',
                                                'name_asc' => 'Name A-Z',
                                                'name_desc' => 'Name Z-A',
                                            ];
                                        @endphp
                                        @if (isset($sortOptions[request('sort_by')]))
                                            <span
                                                class="inline-flex items-center px-2 sm:px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Sort: {{ $sortOptions[request('sort_by')] }}
                                            </span>
                                        @endif
                                    @endif

                                </div>
                            </div>
                            <a href="{{ request()->url() }}"
                                class="text-blue-700 hover:text-blue-900 text-sm font-medium whitespace-nowrap">
                                Delete all
                            </a>
                        </div>
                    </div>
                @endif


                <div class="bg-white rounded-lg shadow-sm p-3 sm:p-6">
                    @if (isset($products))
                        <div class="sm:hidden mb-4 flex items-center justify-between text-xs text-gray-500">
                            <span>
                                Show {{ method_exists($products, 'count') ? $products->count() : count($products) }}
                                product
                            </span>
                            @if (method_exists($products, 'total'))
                                <span>
                                    Page {{ $products->currentPage() }} / {{ $products->lastPage() }}
                                </span>
                            @endif
                        </div>

                        <div
                            class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 mb-6">
                            @foreach ($products as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>

                        @if (method_exists($products, 'links'))
                            <div class="border-t border-gray-200 pt-4 sm:pt-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="order-1 sm:order-2">
                                        <div class="flex justify-center">
                                            {{ $products->links('pagination::daisyui') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <div class="max-w-md mx-auto">
                                <x-heroicon-o-exclamation-triangle
                                    class="h-12 w-12 sm:h-16 sm:w-16 text-gray-400 mx-auto mb-4" />
                                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No products found
                                </h3>
                                <p class="text-sm text-gray-500 mb-4 px-4">There are no products that match your filter
                                    criteria.</p>
                                <div class="space-y-2 sm:space-y-0 sm:space-x-3 sm:flex sm:justify-center">
                                    <a href="{{ request()->url() }}"></a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
    @vite(['resources/js/partials/filter.js'])
@endsection
