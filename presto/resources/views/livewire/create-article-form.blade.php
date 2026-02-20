<div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit="store">
                <div class="mb-3">
                    <label class="form-label" for="title">Titolo</label>
                    <input wire:model.blur="title" type="text" id="title" class="form-control @error('title') is-invalid @enderror">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="price">{{ __('ui.price') }}</label>
                    <input wire:model.blur="price" type="number" step="0.01" min="0" id="price" class="form-control @error('price') is-invalid @enderror">
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="category">{{ __('ui.category') }}</label>
                    <select wire:model.blur="category" id="category" class="form-select @error('category') is-invalid @enderror">
                        <option value="">{{ __('ui.select_category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">{{ __('ui.description') }}</label>
                    <textarea wire:model.blur="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror"></textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="images">Immagini (max 6)</label>
                    <input wire:model="temporary_images" type="file" id="images" multiple class="form-control @error('temporary_images') is-invalid @enderror @error('temporary_images.*') is-invalid @enderror">
                    @error('temporary_images')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('temporary_images.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @if (!empty($images))
                    <div class="row g-3 mb-3">
                        @foreach ($images as $key => $image)
                            <div class="col-6 col-md-4">
                                <div class="border rounded-3 p-2 h-100">
                                    <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="img-fluid rounded-2 mb-2">
                                    <button class="btn btn-outline-danger btn-sm w-100" type="button" wire:click="removeImage({{ $key }})">
                                        Rimuovi
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <button type="submit" class="btn btn-custom">{{ __('ui.create_article') }}</button>
            </form>
        </div>
    </div>
</div>
