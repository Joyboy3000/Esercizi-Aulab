<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type as FeatureType;
use Google\Cloud\Vision\V1\Image as VisionImage;
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

            $imageAnnotator = new ImageAnnotatorClient(['credentials' => $credentialsPath]);

            $request = (new AnnotateImageRequest())
                ->setImage((new VisionImage())->setContent($imageFile))
                ->setFeatures([
                    (new Feature())->setType(FeatureType::LABEL_DETECTION),
                ]);

            $response = $imageAnnotator->batchAnnotateImages(
                (new BatchAnnotateImagesRequest())->setRequests([$request])
            );

            $imageAnnotator->close();

            $responses = iterator_to_array($response->getResponses());
            $labelAnnotations = ($responses[0] ?? null)?->getLabelAnnotations() ?? [];

            $labels = collect($labelAnnotations)
                ->map(fn ($label) => $label->getDescription())
                ->filter()
                ->take(10)
                ->values()
                ->all();

            $imageModel->labels = $labels;
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
}
