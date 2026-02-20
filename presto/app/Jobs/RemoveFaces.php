<?php

namespace App\Jobs;

use App\Models\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\FaceAnnotation;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type as FeatureType;
use Google\Cloud\Vision\V1\Image as VisionImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Throwable;

class RemoveFaces implements ShouldQueue
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

        try {
            $this->censorFacesOnRelativePath($imageModel->path, $credentialsPath);

            $resizedRelativePath = $this->resizedPath($imageModel->path, 300, 300);

            if (File::exists(storage_path('app/public/'.$resizedRelativePath))) {
                $this->censorFacesOnRelativePath($resizedRelativePath, $credentialsPath);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function censorFacesOnRelativePath(string $relativePath, string $credentialsPath): void
    {
        $absolutePath = storage_path('app/public/'.$relativePath);

        if (! File::exists($absolutePath)) {
            return;
        }

        $imageFile = file_get_contents($absolutePath);

        if ($imageFile === false) {
            return;
        }

        $imageAnnotator = new ImageAnnotatorClient(['credentials' => $credentialsPath]);

        $request = (new AnnotateImageRequest())
            ->setImage((new VisionImage())->setContent($imageFile))
            ->setFeatures([
                (new Feature())->setType(FeatureType::FACE_DETECTION),
            ]);

        $response = $imageAnnotator->batchAnnotateImages(
            (new BatchAnnotateImagesRequest())->setRequests([$request])
        );

        $imageAnnotator->close();

        $responses = iterator_to_array($response->getResponses());
        $faces = ($responses[0] ?? null)?->getFaceAnnotations() ?? [];

        if (empty($faces)) {
            return;
        }

        $imageResource = @imagecreatefromstring($imageFile);

        if (! $imageResource) {
            return;
        }

        $censorPath = public_path('img/censor.png');
        $censorResource = File::exists($censorPath) ? @imagecreatefrompng($censorPath) : null;
        $black = imagecolorallocate($imageResource, 0, 0, 0);

        foreach ($faces as $face) {
            $box = $this->getFaceBoundingBox($face);

            if (! $box) {
                continue;
            }

            if ($censorResource) {
                imagecopyresampled(
                    $imageResource,
                    $censorResource,
                    $box['x'],
                    $box['y'],
                    0,
                    0,
                    $box['width'],
                    $box['height'],
                    imagesx($censorResource),
                    imagesy($censorResource)
                );
            } else {
                imagefilledrectangle(
                    $imageResource,
                    $box['x'],
                    $box['y'],
                    $box['x'] + $box['width'],
                    $box['y'] + $box['height'],
                    $black
                );
            }
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        match ($extension) {
            'png' => imagepng($imageResource, $absolutePath),
            'gif' => imagegif($imageResource, $absolutePath),
            'webp' => imagewebp($imageResource, $absolutePath, 90),
            default => imagejpeg($imageResource, $absolutePath, 90),
        };

        if ($censorResource) {
            imagedestroy($censorResource);
        }

        imagedestroy($imageResource);
    }

    private function getFaceBoundingBox(FaceAnnotation $face): ?array
    {
        $vertices = $face->getBoundingPoly()?->getVertices() ?? [];

        if (empty($vertices)) {
            return null;
        }

        $xCoordinates = [];
        $yCoordinates = [];

        foreach ($vertices as $vertex) {
            $xCoordinates[] = max(0, (int) $vertex->getX());
            $yCoordinates[] = max(0, (int) $vertex->getY());
        }

        $minX = min($xCoordinates);
        $minY = min($yCoordinates);
        $maxX = max($xCoordinates);
        $maxY = max($yCoordinates);

        if ($maxX <= $minX || $maxY <= $minY) {
            return null;
        }

        return [
            'x' => $minX,
            'y' => $minY,
            'width' => $maxX - $minX,
            'height' => $maxY - $minY,
        ];
    }

    private function resizedPath(string $path, int $width, int $height): string
    {
        $pathInfo = pathinfo($path);
        $dirname = $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'].'/' : '';
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';

        return $dirname.$filename."_{$width}x{$height}.".$extension;
    }

    private function credentialsPath(): string
    {
        $configuredPath = env('GOOGLE_APPLICATION_CREDENTIALS', 'google_credential.json');

        return File::exists($configuredPath)
            ? $configuredPath
            : base_path($configuredPath);
    }
}
