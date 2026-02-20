@props(['article'])

<div class="card h-100 border-0 shadow-sm card-article">
    <img
        src="{{ $article->images->isNotEmpty() ? $article->images->first()->getUrl(300, 300) : 'https://picsum.photos/seed/'.$article->id.'/600/340' }}"
        class="card-img-top thumb-placeholder"
        alt="Anteprima annuncio"
    >
    <div class="card-body">
        <h5 class="card-title">{{ $article->title }}</h5>
        <p class="text-muted mb-1">{{ number_format($article->price, 2, ',', '.') }} €</p>
        <p class="mb-2">
            <span class="badge text-bg-light">{{ $article->category->name }}</span>
        </p>
        <a href="{{ route('article.show', compact('article')) }}" class="btn btn-outline-primary btn-sm">{{ __('ui.detail') }}</a>
    </div>
</div>
