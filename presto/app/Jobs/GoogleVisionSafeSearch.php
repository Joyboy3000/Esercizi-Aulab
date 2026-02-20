<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type as FeatureType;
use Google\Cloud\Vision\V1\Image as VisionImage;
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

            $imageAnnotator = new ImageAnnotatorClient(['credentials' => $credentialsPath]);

            $request = (new AnnotateImageRequest())
                ->setImage((new VisionImage())->setContent($imageFile))
                ->setFeatures([
                    (new Feature())->setType(FeatureType::SAFE_SEARCH_DETECTION),
                ]);

            $response = $imageAnnotator->batchAnnotateImages(
                (new BatchAnnotateImagesRequest())->setRequests([$request])
            );

            $imageAnnotator->close();

            $responses = iterator_to_array($response->getResponses());
            $safeSearch = ($responses[0] ?? null)?->getSafeSearchAnnotation();

            if (! $safeSearch) {
                return;
            }

            $imageModel->adult = $this->toIconClass($safeSearch->getAdult());
            $imageModel->spoof = $this->toIconClass($safeSearch->getSpoof());
            $imageModel->medical = $this->toIconClass($safeSearch->getMedical());
            $imageModel->violence = $this->toIconClass($safeSearch->getViolence());
            $imageModel->racy = $this->toIconClass($safeSearch->getRacy());
            $imageModel->save();
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
