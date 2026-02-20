<?php

namespace App\Livewire;

use App\Jobs\GoogleVisionLabelImage;
use App\Jobs\GoogleVisionSafeSearch;
use App\Jobs\RemoveFaces;
use App\Jobs\ResizeImage;
use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateArticleForm extends Component
{
    use WithFileUploads;

    #[Validate('required|min:5|max:80')]
    public string $title = '';

    #[Validate('required|min:10|max:1200')]
    public string $description = '';

    #[Validate('required|numeric|min:0.01')]
    public string $price = '';

    #[Validate('required|exists:categories,id')]
    public string $category = '';

    public array $images = [];

    public array $temporary_images = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|min:5|max:80',
            'description' => 'required|min:10|max:1200',
            'price' => 'required|numeric|min:0.01',
            'category' => 'required|exists:categories,id',
            'images.*' => 'image|max:2048',
            'temporary_images.*' => 'image|max:2048',
        ];
    }

    public function updatedTemporaryImages(): void
    {
        if (count($this->images) + count($this->temporary_images) > 6) {
            $this->addError('temporary_images', 'Puoi caricare massimo 6 immagini.');
            $this->temporary_images = [];

            return;
        }

        $this->validate([
            'temporary_images.*' => 'image|max:2048',
        ]);

        foreach ($this->temporary_images as $image) {
            $this->images[] = $image;
        }

        $this->temporary_images = [];
    }

    public function removeImage(int $key): void
    {
        if (array_key_exists($key, $this->images)) {
            unset($this->images[$key]);
            $this->images = array_values($this->images);
        }
    }

    public function store(): void
    {
        $validated = $this->validate();

        $article = Article::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category_id' => $validated['category'],
            'user_id' => Auth::id(),
        ]);

        foreach ($this->images as $image) {
            $newFileName = "articles/{$article->id}";
            $storedPath = $image->store($newFileName, 'public');

            $newImage = $article->images()->create([
                'path' => $storedPath,
            ]);

            ResizeImage::withChain([
                new GoogleVisionSafeSearch($newImage->id),
                new GoogleVisionLabelImage($newImage->id),
                new RemoveFaces($newImage->id),
            ])->dispatch($newImage->path, 300, 300);
        }

        if (File::exists(storage_path('app/livewire-tmp'))) {
            File::deleteDirectory(storage_path('app/livewire-tmp'));
        }

        $this->reset(['title', 'description', 'price', 'category', 'temporary_images', 'images']);

        session()->flash('success', __('ui.inserted_success'));
    }

    public function render()
    {
        return view('livewire.create-article-form');
    }
}
