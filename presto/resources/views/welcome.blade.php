<x-layout>
    <section class="hero p-4 p-md-5 mb-4">
        <h1 class="display-6 fw-bold mb-2">{{ __('ui.hero_title') }}</h1>
        <p class="mb-4">{{ __('ui.hero_subtitle') }}</p>
        <a href="{{ route('article.create') }}" class="btn btn-light fw-semibold">{{ __('ui.create_article') }}</a>
    </section>

    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">{{ __('ui.latest_articles') }}</h2>
            <a href="{{ route('article.index') }}" class="btn btn-outline-primary btn-sm">{{ __('ui.see_all') }}</a>
        </div>

        <div class="row g-3">
            @forelse ($articles as $article)
                <div class="col-12 col-md-6 col-lg-4">
                    <x-card :article="$article" />
                </div>
            @empty
                <div class="col-12">
                    <p class="text-muted">{{ __('ui.no_articles_available') }}</p>
                </div>
            @endforelse
        </div>
    </section>
</x-layout>
