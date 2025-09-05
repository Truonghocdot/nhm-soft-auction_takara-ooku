@extends('layouts.app')

@php
    use Illuminate\Support\Str;
    $siteName = config('app.name', 'Takara Ooku');
    $pageNumber = $articles->currentPage() ?? 1;

    // Lấy input đúng tên: product_name, category_id, sort_by
    $searchTerm = request('q');
    $categoryId = request('category_id');
    $sortBy = request('sort_by');

    // Title logic
    if ($searchTerm) {
        $metaTitle = 'Search results: "' . e($searchTerm) . '" - News & Events | ' . $siteName;
    } elseif ($categoryId) {
        $cat = $categories->where('id', $categoryId)->first();
        $catName = $cat?->name ?? $categoryId;
        $metaTitle = $catName . ' - News & Events | ' . $siteName;
    } else {
        $metaTitle = 'News & Events' . ($pageNumber > 1 ? " — Page {$pageNumber}" : '') . ' | ' . $siteName;
    }

    // Meta description logic
    if ($searchTerm) {
        $metaDescription =
            'Search results for "' .
            e($searchTerm) .
            '" on ' .
            $siteName .
            '. Find articles, guides, events and news related to your topic of interest.';
    } elseif ($categoryId) {
        $metaDescription =
            ($cat?->description ?? 'Articles in category ' . ($catName ?? $categoryId) . ' on ' . $siteName) .
            '. Get the latest news, analysis and guides.';
    } else {
        $metaDescription =
            'Get the latest news & events on technology, business and society on ' .
            $siteName .
            '. In-depth articles, accurate and timely information.';
    }

    // Meta keywords (short)
    $metaKeywords = $categories->pluck('name')->take(10)->map(fn($n) => Str::slug($n, ' '))->join(', ');
    if (empty($metaKeywords)) {
        $metaKeywords = 'news, events, ' . Str::slug($siteName, ' ');
    }

    // OG image: banner nếu có, fallback
    $ogImage = isset($primary) && $primary ? asset('images/banner_buyeeEnSp.png') : asset('images/auctions-og.jpg');

    // Canonical: base URL; include page param only if page > 1
    $currentUrl = request()->url();
    $canonical = $currentUrl . ($pageNumber > 1 ? '?page=' . $pageNumber : '');

    // Prev/Next for pagination
    $prevUrl = $articles->previousPageUrl();
    $nextUrl = $articles->nextPageUrl();

    // Robots: noindex when it's a search (product_name present), index otherwise
$robots = $searchTerm ? 'noindex,follow' : 'index,follow';

    // Prepare items for JSON-LD (limit to 10)
    $ldItems = $articles->take(10);
@endphp

{{-- Basic SEO sections used by partial.head --}}
@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@section('og_image', $ogImage)
@section('schema_type', 'CollectionPage')
@section('schema_name', 'Tin tức & Sự kiện — ' . $siteName)

{{-- Push additional head tags (requires @stack('head') present in head include) --}}
@push('head')
    <link rel="canonical" href="{{ $canonical }}" />
    @if ($prevUrl)
        <link rel="prev" href="{{ $prevUrl }}" />
    @endif
    @if ($nextUrl)
        <link rel="next" href="{{ $nextUrl }}" />
    @endif

    <meta name="robots" content="{{ $robots }}" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $metaTitle }}" />
    <meta name="twitter:description" content="{{ $metaDescription }}" />
    <meta name="twitter:image" content="{{ $ogImage }}" />
@endpush

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-12">
            @if ($primary)
                <div class="overflow-hidden" aria-label="Promotional Banner">
                    <img src="{{ asset('images/banner_buyeeEnSp.png') }}" class="w-full max-h-[585px] object-cover"
                        alt="{{ $siteName }} - Promotion" loading="lazy">
                </div>
            @else
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4 mt-14">
                        News & Events
                    </h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Stay up to date with the latest news on technology, business and society
                    </p>
                </div>
            @endif

            <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
                {{-- Note: change the input name to match the controller: product_name, category_id, sort_by --}}
                <form action="{{ route('news.list') }}" method="GET" class="flex flex-col md:flex-row gap-4"
                    role="search" aria-label="Form search for articles">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                                aria-hidden="true"></i>
                            <input type="text" name="q" value="{{ old('q', $searchTerm) }}"
                                placeholder="Search for articles..." aria-label="Search for articles"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="md:w-48">
                        <select name="category_id" aria-label="Filter by category"
                            class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All categories</option>
                            {{-- Nếu partial.category-node xuất option theo id thì ok; nếu không, bạn có thể render trực tiếp --}}
                            @foreach ($categories as $node)
                                {{-- Giữ partial nếu nó trả option với value = id; nếu partial hiện tree bằng <option>, nó sẽ hoạt động --}}
                                @include('partial.category-node', [
                                    'node' => $node,
                                    'depth' => 0,
                                    'selected' => $categoryId ?? null,
                                ])
                            @endforeach
                        </select>
                    </div>

                    <div class="md:w-48">
                        <select name="sort_by" aria-label="Sort"
                            class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Mọi bài viết</option>
                            <option value="view" {{ $sortBy === 'view' ? 'selected' : '' }}>Lượt xem</option>
                            <option value="sort" {{ $sortBy === 'sort' ? 'selected' : '' }}>Độ ưu tiên</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="btn btn-neutral text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-search mr-2" aria-hidden="true"></i>Search
                    </button>
                </form>
            </div>

            @if ($searchTerm || $categoryId)
                <div class="mt-4 p-4 bg-blue-50 rounded-lg" role="status" aria-live="polite">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-blue-800 font-medium">Search results:</span>
                            @if ($searchTerm)
                                <span class="bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-sm">
                                    <i class="fas fa-search mr-1" aria-hidden="true"></i>
                                    "{{ $searchTerm }}"
                                </span>
                            @endif
                            @if ($categoryId)
                                <span class="bg-green-200 text-green-800 px-3 py-1 rounded-full text-sm">
                                    <i class="fas fa-tag mr-1" aria-hidden="true"></i>
                                    {{ $categories->where('id', $categoryId)->first()?->name ?? $categoryId }}
                                </span>
                            @endif
                            <span class="text-gray-600">
                                ({{ $articles->total() }} articles)
                            </span>
                        </div>
                        <a href="{{ route('news.list') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-times mr-1" aria-hidden="true"></i>Clear filter
                        </a>
                    </div>
                </div>
            @endif

            <section class="my-12" aria-label="List of articles">
                @if ($articles->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($articles as $index => $article)
                            <x-article-card :article="$article" :position="$articles->firstItem() + $index" />
                        @endforeach
                    </div>

                    <div class="mt-12">
                        {{-- Keep other queries when paginating --}}
                        {{ $articles->appends(request()->except('page'))->links('pagination::daisyui') }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="mb-4">
                            <i class="fas fa-search text-gray-400 text-6xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            No articles found
                        </h3>
                        <p class="text-gray-600 mb-6">
                            Try changing the search keyword or category to see more results.
                        </p>
                        <a href="{{ route('news.list') }}"
                            class="btn btn-neutral text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-list mr-2" aria-hidden="true"></i>See all posts
                        </a>
                    </div>
                @endif
            </section>

            <section class="mt-16 bg-gradient-to-r from-[#646068] to-[#777f92] rounded-2xl p-8 text-white text-center">
                <h3 class="text-2xl md:text-3xl font-bold mb-4">
                    Sign up for the latest news
                </h3>
                <p class="text-blue-100 mb-8 max-w-2xl mx-auto">
                    Get the latest articles and exclusive information before others
                </p>
                <div class="max-w-md mx-auto flex gap-4">
                    <input type="email" placeholder="Enter your email..."
                        class="flex-1 px-4 py-3 rounded-lg text-blue-600 focus:outline-none focus:ring-2 focus:ring-white"
                        aria-label="Email to receive news">
                    <button
                        class="bg-white text-blue-600 hover:bg-slate-400 px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                        <i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>Sign up
                    </button>
                </div>
            </section>
        </div>
    </div>

    {{-- JSON-LD for Collection + ItemList + Breadcrumb --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "CollectionPage",
      "name": {!! json_encode(trim(strip_tags($metaTitle))) !!},
      "description": {!! json_encode(trim(strip_tags($metaDescription))) !!},
      "url": {!! json_encode($canonical) !!},
      "mainEntity": {
        "@type": "ItemList",
        "itemListElement": [
            @foreach($ldItems as $i => $a)
            {
              "@type": "ListItem",
              "position": {{ $i + 1 }},
              "url": {!! json_encode(route('news.detail', $a->slug ?? $a->id)) !!},
              "name": {!! json_encode($a->title ?? '') !!},
              @if(!empty($a->image))
              "image": {!! json_encode(asset('storage/' . $a->image)) !!},
              @endif
              "datePublished": {!! json_encode(optional($a->published_at ?? $a->created_at)->toIso8601String()) !!}
            }@if(!$loop->last),@endif
            @endforeach
        ]
      }
    }
    </script>

    {{-- BreadcrumbList --}}
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Trang chủ",
          "item": {!! json_encode(url('/')) !!}
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Tin tức",
          "item": {!! json_encode(route('news.list')) !!}
        }
      ]
    }
    </script>
@endsection
