<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Article extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'description',
        'price',
        'category_id',
        'user_id',
        'is_accepted',
    ];

    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('category');

        return [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category?->name,
        ];
    }

    public function setAccepted(?bool $value): void
    {
        $this->is_accepted = $value;
    }

    public static function toBeRevisedCount(): int
    {
        return static::whereNull('is_accepted')->count();
    }
}
