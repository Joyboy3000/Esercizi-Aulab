<footer class="bg-dark text-light py-4 mt-5">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <p class="mb-0">{{ __('ui.marketplace') }}</p>
        <div class="d-flex align-items-center gap-3">
            @auth
                <a href="{{ route('become.revisor') }}" class="btn btn-sm btn-outline-light">{{ __('ui.work_with_us') }}</a>
            @endauth
            <small class="text-secondary">{{ __('ui.built_with_laravel') }}</small>
        </div>
    </div>
</footer>
