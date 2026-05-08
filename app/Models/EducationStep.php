<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationStep extends Model
{
    protected $fillable = [
        'education_article_id',
        'sort_order',
        'title',
        'body',
        'image_path',
        'youtube_url',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(EducationArticle::class, 'education_article_id');
    }

    public function youtubeEmbedUrl(): ?string
    {
        return EducationArticle::toYoutubeEmbedUrl($this->youtube_url);
    }
}
