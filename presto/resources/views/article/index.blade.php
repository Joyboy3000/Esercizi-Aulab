<x-layout>
    <h1 class="h3 mb-3">{{ __('ui.article_index_title') }}</h1>

    <div class="row g-3">
        @forelse ($articles as $article)
            <div class="col-12 col-md-6 col-lg-4">
                <x-card :article="$article" />
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted">{{ __('ui.no_articles') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $articles->links() }}
    </div>
</x-layout>
