<x-layout>
    <h1 class="h3 mb-1">{{ __('ui.search_results') }}</h1>
    <p class="text-muted mb-4">{{ __('ui.search_results_for') }}: <strong>{{ $query }}</strong></p>

    <div class="row g-3">
        @forelse ($articles as $article)
            <div class="col-12 col-md-6 col-lg-4">
                <x-card :article="$article" />
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted">{{ __('ui.search_no_results') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $articles->links() }}
    </div>
</x-layout>
