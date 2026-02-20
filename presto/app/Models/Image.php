<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'path',
        'article_id',
        'adult',
        'spoof',
        'medical',
        'violence',
        'racy',
        'labels',
    ];

    protected function casts(): array
    {
        return [
            'labels' => 'array',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function getUrl(int $width = 0, int $height = 0): string
    {
        if ($width > 0 && $height > 0) {
            $pathInfo = pathinfo($this->path);
            $dirname = $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'].'/' : '';
            $filename = $pathInfo['filename'];
            $extension = $pathInfo['extension'] ?? 'jpg';
            $resizedPath = $dirname.$filename."_{$width}x{$height}.".$extension;

            if (Storage::disk('public')->exists($resizedPath)) {
                return Storage::url($resizedPath);
            }
        }

        if (Storage::disk('public')->exists($this->path)) {
            return Storage::url($this->path);
        }

        return 'https://picsum.photos/seed/presto-placeholder/800/600';
    }
}
