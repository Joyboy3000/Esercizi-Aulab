<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Likelihood;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Throwable;

class GoogleVisionSafeSearch implements ShouldQueue
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

            $response = $imageAnnotator->safeSearchDetection($imageFile);
            $safeSearch = $response->getSafeSearchAnnotation();

            if (! $safeSearch) {
                $imageAnnotator->close();

                return;
            }

            $imageModel->adult = $this->toIconClass($safeSearch->getAdult());
            $imageModel->spoof = $this->toIconClass($safeSearch->getSpoof());
            $imageModel->medical = $this->toIconClass($safeSearch->getMedical());
            $imageModel->violence = $this->toIconClass($safeSearch->getViolence());
            $imageModel->racy = $this->toIconClass($safeSearch->getRacy());
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

    private function toIconClass(int $likelihood): string
    {
        return match ($likelihood) {
            Likelihood::VERY_UNLIKELY => 'bi bi-emoji-smile text-success',
            Likelihood::UNLIKELY => 'bi bi-emoji-neutral text-success',
            Likelihood::POSSIBLE => 'bi bi-emoji-expressionless text-warning',
            Likelihood::LIKELY => 'bi bi-emoji-frown text-warning',
            Likelihood::VERY_LIKELY => 'bi bi-emoji-dizzy text-danger',
            default => 'bi bi-question-circle text-secondary',
        };
    }
}
