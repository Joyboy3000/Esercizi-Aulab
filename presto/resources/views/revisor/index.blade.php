<x-layout>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">{{ __('ui.revisor_dashboard') }}</h1>
        <form action="{{ route('revisor.undo') }}" method="POST">
            @csrf
            @method('PATCH')
            <button class="btn btn-outline-secondary btn-sm" type="submit">{{ __('ui.undo_last_action') }}</button>
        </form>
    </div>

    @if ($article_to_check)
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        @if ($article_to_check->images->isNotEmpty())
                            <div id="reviewCarousel" class="carousel slide">
                                <div class="carousel-inner rounded-3 shadow-sm">
                                    @foreach ($article_to_check->images as $image)
                                        <div class="carousel-item @if ($loop->first) active @endif">
                                            <img src="{{ $image->getUrl() }}" class="d-block mx-auto article-main-image" alt="Immagine da revisionare">
                                        </div>
                                    @endforeach
                                </div>
                                @if ($article_to_check->images->count() > 1)
                                    <button class="carousel-control-prev carousel-nav-btn" type="button" data-bs-target="#reviewCarousel" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next carousel-nav-btn" type="button" data-bs-target="#reviewCarousel" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <img class="img-fluid rounded-3" src="https://picsum.photos/seed/rev{{ $article_to_check->id }}/900/520" alt="Anteprima annuncio">
                        @endif
                    </div>

                    <div class="col-12 col-lg-6">
                        <h2 class="h4">{{ $article_to_check->title }}</h2>
                        <p class="text-primary fw-semibold">{{ number_format($article_to_check->price, 2, ',', '.') }} EUR</p>
                        <p class="mb-1"><strong>{{ __('ui.category') }}:</strong> {{ $article_to_check->category->name }}</p>
                        <p class="mb-3"><strong>{{ __('ui.name') }}:</strong> {{ $article_to_check->user->name }}</p>
                        <p class="text-secondary">{{ $article_to_check->description }}</p>

                        <div class="d-flex gap-2 mt-4">
                            <form action="{{ route('revisor.accept', ['article' => $article_to_check]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-success" type="submit">{{ __('ui.accept') }}</button>
                            </form>

                            <form action="{{ route('revisor.reject', ['article' => $article_to_check]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-danger" type="submit">{{ __('ui.reject') }}</button>
                            </form>
                        </div>
                    </div>
                </div>

                @if ($article_to_check->images->isNotEmpty())
                    <hr class="my-4">
                    <h3 class="h5 mb-3">{{ __('ui.image_analysis') }}</h3>
                    <div class="row g-3">
                        @foreach ($article_to_check->images as $image)
                            <div class="col-12 col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <img src="{{ $image->getUrl(300, 300) }}" class="img-fluid rounded-2 mb-3" alt="Anteprima analisi">
                                    @if ($image->adult || $image->spoof || $image->medical || $image->violence || $image->racy)
                                        <div class="row row-cols-2 g-2 mb-3">
                                            <div class="col d-flex align-items-center gap-2">
                                                <i class="{{ $image->adult }}"></i>
                                                <small>Adult</small>
                                            </div>
                                            <div class="col d-flex align-items-center gap-2">
                                                <i class="{{ $image->spoof }}"></i>
                                                <small>Spoof</small>
                                            </div>
                                            <div class="col d-flex align-items-center gap-2">
                                                <i class="{{ $image->medical }}"></i>
                                                <small>Medical</small>
                                            </div>
                                            <div class="col d-flex align-items-center gap-2">
                                                <i class="{{ $image->violence }}"></i>
                                                <small>Violence</small>
                                            </div>
                                            <div class="col d-flex align-items-center gap-2">
                                                <i class="{{ $image->racy }}"></i>
                                                <small>Racy</small>
                                            </div>
                                        </div>
                                    @else
                                        <p class="small text-muted mb-2">{{ __('ui.analysis_pending') }}</p>
                                    @endif

                                    @if (!empty($image->labels))
                                        <p class="small text-muted mb-2">{{ __('ui.image_labels') }}:</p>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($image->labels as $label)
                                                <span class="badge text-bg-light">{{ $label }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <p class="mb-3">{{ __('ui.no_articles_to_review') }}</p>
                <a class="btn btn-outline-primary" href="{{ route('homepage') }}">{{ __('ui.back_home') }}</a>
            </div>
        </div>
    @endif
</x-layout>
