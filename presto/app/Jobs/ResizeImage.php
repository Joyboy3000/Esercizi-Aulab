<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Spatie\Image\Enums\AlignPosition;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\Unit;
use Spatie\Image\Image as SpatieImage;

class ResizeImage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $path,
        public int $width,
        public int $height
    ) {}

    public function handle(): void
    {
        $source = storage_path('app/public/'.$this->path);

        if (! File::exists($source)) {
            return;
        }

        $resizedPath = $this->resizedPath($this->path, $this->width, $this->height);
        $destination = storage_path('app/public/'.$resizedPath);
        File::ensureDirectoryExists(dirname($destination));

        $image = SpatieImage::load($source)->fit(Fit::Crop, $this->width, $this->height);

        $watermark = public_path('img/watermark.png');

        if (File::exists($watermark)) {
            $image->watermark(
                $watermark,
                AlignPosition::BottomRight,
                10,
                10,
                Unit::Pixel,
                80,
                Unit::Pixel,
                80,
                Unit::Pixel,
                Fit::Contain,
                70
            );
        }

        $image->save($destination);
    }

    private function resizedPath(string $path, int $width, int $height): string
    {
        $pathInfo = pathinfo($path);
        $dirname = $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'].'/' : '';
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? 'jpg';

        return $dirname.$filename."_{$width}x{$height}.".$extension;
    }
}
