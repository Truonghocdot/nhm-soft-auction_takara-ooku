@extends('layouts.app')
@section('title', 'Prestigious online bidding - HOT products, best prices | Takara Ooku')
@section('meta_description',
    'Discover hundreds of online bidding products online on Takara Ooku. Latest updates every
    day, attractive starting prices, flexible bidding and the opportunity to own your favorite item.')
@section('meta_keywords',
    'online bidding online, online bargaining, cheap products, online bidding online, Takara
    Ooku')
@section('og_title', 'Prestigious online bidding - Update the hottest products')
@section('og_description',
    'Join online bidding sessions online on Takara Ooku now. Low starting prices, diverse
    products, transparent competition. Opportunity to buy quality products at the best prices!')
@section('og_image', asset('images/auctions-og.jpg'))
@section('schema_type', 'CollectionPage')
@section('schema_name', 'Online Auctions List - Takara Ooku')

@section('content')

    <section class="site-banner overflow-hidden" aria-label="Promotional Banner">
        @if ($banner_primary)
            <img src="{{ \App\Utils\HelperFunc::generateURLFilePath($banner_primary['url_image']) }}"
                class="w-full max-h-[585px] object-cover" alt="Takara-ooku promotional banner" loading="lazy">
        @else
            <img src="{{ asset('images/banner_buyeeEnSp.png') }}" class="w-full max-h-[585px] object-cover"
                alt="Takara-ooku promotional banner" loading="lazy">
        @endif
    </section>

    <div id="page-home" class="my-6 max-w-7xl mx-auto px-4">
        <main class="site-main">
            <div class="grid lg:grid-cols-4 grid-cols-1 gap-6">
                <aside class="lg:col-span-1">
                    <div class="flex flex-col gap-6">
                        <div class="bg-white p-4">
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <div class="text-center">
                                    <a href="{{ route('products.list') }}"
                                        class="block hover:opacity-80 transition-opacity">
                                        <x-heroicon-o-shopping-bag
                                            class="w-8 h-8 mx-auto mb-2 rounded-lg"></x-heroicon-o-shopping-bag>
                                        <h3 class="text-xs font-medium text-gray-700 leading-tight">
                                            Products
                                        </h3>
                                    </a>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('products.list', ['product_type' => 'sale']) }}"
                                        class="block hover:opacity-80 transition-opacity">
                                        <x-heroicon-o-sparkles
                                            class="w-8 h-8 mx-auto mb-2 rounded-lg"></x-heroicon-o-sparkles>
                                        <h3 class="text-xs font-medium text-gray-700 leading-tight">
                                            For sale
                                        </h3>
                                    </a>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('products.list', ['product_type' => 'auction']) }}"
                                        class="block hover:opacity-80 transition-opacity">
                                        <img src="{{ asset('images/svg/auction-bid-svg.svg') }}"
                                            class="w-8 h-8 mx-auto mb-2 rounded-lg" alt="Trả giá">
                                        <h3 class="text-xs font-medium text-gray-700 leading-tight">Bid online
                                        </h3>
                                    </a>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('news.list') }}" class="block hover:opacity-80 transition-opacity">
                                        <x-heroicon-o-newspaper
                                            class="w-8 h-8 mx-auto mb-2 rounded-lg"></x-heroicon-o-newspaper>
                                        <h3 class="text-xs font-medium text-gray-700 leading-tight">News
                                        </h3>
                                    </a>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('filament.admin.resources.buy-memberships.index') }}"
                                        class="block hover:opacity-80 transition-opacity">
                                        <x-heroicon-o-user class="w-8 h-8 mx-auto mb-2 rounded-lg"></x-heroicon-o-user>
                                        <h3 class="text-xs font-medium text-gray-700 leading-tight">Member
                                        </h3>
                                    </a>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('page.static', 'gioi-thieu') }}"
                                        class="block hover:opacity-80 transition-opacity">
                                        <img src="{{ asset('images/svg/introduce-svg.svg') }}"
                                            class="w-8 h-8 mx-auto mb-2 rounded-lg" alt="Giới thiệu">
                                        <h3 class="text-xs font-medium text-gray-700 leading-tight">Introduce
                                        </h3>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-4">
                            <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Search by category
                            </h2>

                            <div class="grid grid-cols-3 md:grid-cols-5 lg:hidden gap-3 mb-4">
                                @if (isset($categories) && count($categories) > 0)
                                    @foreach ($categories as $category)
                                        <div class="text-center">
                                            <a href="{{ route('products.list', ['category_id' => $category->id]) }}"
                                                class="block hover:opacity-80 transition-opacity">
                                                @if (!empty($category['image']))
                                                    <img src="{{ \App\Utils\HelperFunc::generateURLFilePath($category['image']) }}"
                                                        class="w-16 h-16 mx-auto mb-2 rounded-lg object-cover"
                                                        alt="{{ $category['name'] }}" loading="lazy" />
                                                @else
                                                    <img src="{{ asset('images/product_default.jpg') }}"
                                                        class="w-16 h-16 mx-auto mb-2 rounded-lg object-cover"
                                                        alt="{{ $category['name'] }}" loading="lazy" />
                                                @endif
                                                <h3 class="text-xs font-medium text-gray-700 leading-tight">
                                                    {{ $category['name'] }}
                                                </h3>
                                            </a>
                                        </div>
                                    @endforeach
                                @else
                                    <h2 class="col-span-3 md:col-span-5 text-center text-gray-500">No categories yet
                                    </h2>
                                @endif
                            </div>


                            <nav class="hidden lg:block  max-h-[392px] overflow-y-auto" aria-label="Category Navigation">
                                <ul class="space-y-2">
                                    @if (isset($categories))
                                        @foreach ($categories as $category)
                                            <li>
                                                <a href="{{ route('products.list', ['category_id' => $category->id]) }}"
                                                    class="block p-2 font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded transition-colors">
                                                    {{ $category['name'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @else<h2>No categories</h2>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                        @if ($list_know->count() > 0)
                            <div class="bg-white p-4">
                                <h2 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Our Offers
                                </h2>

                                <div class="grid grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-3">
                                    @forelse ($list_know as $index => $banner)
                                        <div class="aspect-square">
                                            <a href="{{ $banner->link_page ?? 'javascript:void(0);' }}"
                                                title="{{ $banner->name }}"
                                                class="block hover:opacity-90 transition-opacity duration-300"
                                                @if (!empty($banner->link_page)) rel="nofollow sponsored" @endif>
                                                <img src="{{ \App\Utils\HelperFunc::generateURLFilePath($banner->url_image) }}"
                                                    alt="{{ $banner->name }}" loading="lazy"
                                                    class="w-full h-full object-cover rounded-lg shadow-md hover:scale-105 transition-transform duration-300">
                                            </a>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500 col-span-2 text-center">
                                            There are no offers yet
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                </aside>

                <!-- Main Content -->
                <section class="lg:col-span-3">
                    <x-section-home :section="['title' => 'Featured Products', 'target' => route('products.list')]" :products="$products1" />
                    <x-section-home :section="[
                        'title' => 'Popular Products',
                        'target' => route('products.list', ['orderBy' => 'view_desc']),
                    ]" :products="$products2" />
                    <x-section-home :section="['title' => 'New Products', 'target' => route('products.list')]" :products="$products3" />
                    @if ($advertise->count() > 0)
                        <div class="bg-white p-6 mb-6">
                            <header class="mb-6">
                                <h2 class="text-xl font-bold text-gray-800 pb-3 border-b border-gray-200">
                                    Explore our site i
                                </h2>
                            </header>

                            <div class="slide-banner swiper">
                                <div class="swiper-wrapper mb-6">
                                    @forelse ($advertise as $banner)
                                        <div class="swiper-slide">
                                            <article>
                                                <a href="{{ $banner->link_page ?? 'javascript:void(0);' }}"
                                                    title="{{ $banner->name }}"
                                                    @if (!empty($banner->link_page)) rel="nofollow sponsored" @endif>
                                                    <img src="{{ \App\Utils\HelperFunc::generateURLFilePath($banner->url_image) }}"
                                                        alt="{{ $banner->name }}" loading="lazy"
                                                        class="w-full h-auto object-cover shadow-md transition-transform duration-300 hover:scale-105">
                                                </a>
                                            </article>
                                        </div>
                                    @empty
                                        <div class="swiper-slide text-center py-10">
                                            <p class="text-gray-500 text-sm">There are currently no banners</p>
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Navigation -->
                                <button
                                    class="prev-btn p-2 bg-white rounded-full shadow hover:bg-[#b1b1b1] absolute left-2 top-1/3 transform -translate-y-1/4 z-10">
                                    <x-heroicon-o-arrow-long-left class="h-6 w-6 mx-auto" />
                                </button>
                                <button
                                    class="next-btn p-2 bg-white rounded-full shadow hover:bg-[#b1b1b1] absolute right-2 top-1/3 transform -translate-y-1/4 z-10">
                                    <x-heroicon-o-arrow-long-right class="h-6 w-6 mx-auto" />
                                </button>
                            </div>
                        </div>
                    @endif

                    <x-section-home :section="[
                        'title' => 'Online Bidding Products',
                        'target' => route('products.list', ['product_type' => 'auction']),
                    ]" :products="$products4" />
                </section>
            </div>
        </main>
    </div>
    <div class="my-6 max-w-7xl mx-auto px-4">

        <div class="text-center mb-6">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 my-4">
                News & Events
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Stay up to date with the latest news on technology, business and society
            </p>
        </div>
        @if ($articles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($articles->take(9) as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
        @endif

        @if ($articles->count() > 9)
            <div class="w-full flex justify-center">
                <div class="my-6">
                    <a href="{{ route('news.list') }}"
                        class="bg-slate-600 text-white rounded-lg py-2 px-4 hover:bg-slate-700 transition-colors">
                        See more
                    </a>
                </div>
            </div>
        @endif
    </div>

@endsection
