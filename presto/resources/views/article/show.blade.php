<x-layout>
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div id="articleCarousel" class="carousel slide">
                <div class="carousel-inner rounded-3 shadow-sm">
                    @if ($article->images->isNotEmpty())
                        @foreach ($article->images as $image)
                            <div class="carousel-item @if ($loop->first) active @endif">
                                <img src="{{ $image->getUrl() }}" class="d-block mx-auto article-main-image" alt="Immagine annuncio">
                            </div>
                        @endforeach
                    @else
                        <div class="carousel-item active">
                            <img src="https://picsum.photos/seed/{{ $article->id }}/1000/560" class="d-block mx-auto article-main-image" alt="Immagine annuncio">
                        </div>
                    @endif
                </div>
                @if ($article->images->count() > 1)
                    <button class="carousel-control-prev carousel-nav-btn" type="button" data-bs-target="#articleCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next carousel-nav-btn" type="button" data-bs-target="#articleCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <h1 class="h3 mb-2">{{ $article->title }}</h1>
            <p class="h5 text-primary mb-3">{{ number_format($article->price, 2, ',', '.') }} €</p>
            <p class="mb-1">
                <strong>{{ __('ui.category') }}:</strong>
                <a href="{{ route('article.byCategory', ['category' => $article->category]) }}">{{ $article->category->name }}</a>
            </p>
            <p class="mb-3"><strong>{{ __('ui.published_by') }}:</strong> {{ $article->user->name }}</p>
            <p class="text-secondary">{{ $article->description }}</p>
        </div>
    </div>
</x-layout>
