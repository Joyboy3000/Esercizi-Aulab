<x-layout>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 mb-3">{{ __('ui.become_revisor_title') }}</h1>
                    <p class="text-muted mb-4">{{ __('ui.become_revisor_description') }}</p>

                    <form action="{{ route('become.revisor.submit') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="name">{{ __('ui.name') }}</label>
                            <input id="name" type="text" class="form-control" value="{{ auth()->user()->name }}" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="email">{{ __('ui.email') }}</label>
                            <input id="email" type="email" class="form-control" value="{{ auth()->user()->email }}" readonly>
                        </div>

                        <button class="btn btn-custom" type="submit">{{ __('ui.send_request') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
