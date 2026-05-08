<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class EducationArticle extends Model
{
    public const CATEGORY_CHART = 'chart';
    public const CATEGORY_EXCHANGE = 'exchange';
    public const CATEGORY_BASIC = 'basic-trading';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'cover_image_path',
        'summary',
        'youtube_url',
        'published_at',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public static function categories(): array
    {
        if (Schema::hasTable('education_categories')) {
            $categories = EducationCategory::where('is_active', true)
                ->ordered()
                ->pluck('name', 'slug')
                ->all();

            if ($categories !== []) {
                return $categories;
            }
        }

        return [
            self::CATEGORY_CHART => 'Cara Baca Chart',
            self::CATEGORY_EXCHANGE => 'Cara Beli di Exchange',
            self::CATEGORY_BASIC => 'Basic Trading',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function categoryRecord(): BelongsTo
    {
        return $this->belongsTo(EducationCategory::class, 'category', 'slug');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(EducationStep::class)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function categoryLabel(): string
    {
        return $this->categoryRecord?->name
            ?? self::categories()[$this->category]
            ?? 'Edukasi';
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function youtubeEmbedUrl(): ?string
    {
        return self::toYoutubeEmbedUrl($this->youtube_url);
    }

    public static function toYoutubeEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);
        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = trim($parts['path'] ?? '', '/');
        $videoId = null;

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        } elseif (str_contains($host, 'youtube.com')) {
            if (str_starts_with($path, 'embed/')) {
                $videoId = substr($path, 6);
            } elseif (str_starts_with($path, 'shorts/')) {
                $videoId = substr($path, 7);
            } else {
                parse_str($parts['query'] ?? '', $query);
                $videoId = $query['v'] ?? null;
            }
        }

        $videoId = $videoId ? preg_replace('/[^A-Za-z0-9_-]/', '', $videoId) : null;

        return $videoId ? 'https://www.youtube.com/embed/'.$videoId : null;
    }
}
