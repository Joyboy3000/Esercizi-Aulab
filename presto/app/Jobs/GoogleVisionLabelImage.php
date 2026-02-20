<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Throwable;

class GoogleVisionLabelImage implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $imageId) {}

    public function handle(): void
    {
        $credentialsPath = $this->credentialsPath();

        if (! File::exists($credentialsPath)) {
            return;
        }

        $imageModel = Image::find($this->imageId);

        if (! $imageModel) {
            return;
        }

        $absolutePath = storage_path('app/public/'.$imageModel->path);

        if (! File::exists($absolutePath)) {
            return;
        }

        try {
            $imageFile = file_get_contents($absolutePath);

            if ($imageFile === false) {
                return;
            }

            $imageAnnotator = new ImageAnnotatorClient([
                'credentials' => $credentialsPath,
            ]);

            $response = $imageAnnotator->labelDetection($imageFile);
            $labelAnnotations = $response->getLabelAnnotations();

            $labels = collect($labelAnnotations)
                ->map(fn ($label) => $label->getDescription())
                ->filter()
                ->take(10)
                ->values()
                ->all();

            $imageModel->labels = $labels;
            $imageModel->save();

            $imageAnnotator->close();
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function credentialsPath(): string
    {
        $configuredPath = env('GOOGLE_APPLICATION_CREDENTIALS', 'google_credential.json');

        return File::exists($configuredPath)
            ? $configuredPath
            : base_path($configuredPath);
    }
}
