<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('homepage') }}">{{ __('ui.brand') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('article.index') }}">{{ __('ui.all_articles') }}</a>
                </li>
                <li class="nav-item dropdown">
                    <button class="nav-link dropdown-toggle btn btn-link" data-bs-toggle="dropdown" type="button">
                        {{ __('ui.categories') }}
                    </button>
                    <ul class="dropdown-menu">
                        @forelse ($categories as $category)
                            <li>
                                <a class="dropdown-item" href="{{ route('article.byCategory', compact('category')) }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @empty
                            <li><span class="dropdown-item-text text-muted">{{ __('ui.no_categories') }}</span></li>
                        @endforelse
                    </ul>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('article.search') }}" method="GET" class="d-flex" role="search">
                    <input
                        class="form-control form-control-sm"
                        type="search"
                        name="query"
                        value="{{ request('query') }}"
                        placeholder="{{ __('ui.search_placeholder') }}"
                        aria-label="{{ __('ui.search') }}"
                    >
                    <button class="btn btn-outline-primary btn-sm ms-2" type="submit">{{ __('ui.search') }}</button>
                </form>

                <x-_locale lang="it" country="it" />
                <x-_locale lang="en" country="gb" />
                <x-_locale lang="es" country="es" />

                @auth
                    @if (auth()->user()->is_revisor)
                        <a class="btn btn-outline-dark btn-sm position-relative" href="{{ route('revisor.index') }}">
                            {{ __('ui.revisor_area') }}
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ \App\Models\Article::toBeRevisedCount() }}
                            </span>
                        </a>
                    @endif
                    <a class="btn btn-custom btn-sm" href="{{ route('article.create') }}">{{ __('ui.create_article') }}</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-secondary btn-sm" type="submit">{{ __('ui.logout') }}</button>
                    </form>
                @else
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('login') }}">{{ __('ui.login') }}</a>
                    <a class="btn btn-custom btn-sm" href="{{ route('register') }}">{{ __('ui.register') }}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
