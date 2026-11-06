<?php

namespace App\Models;

use App\Support\ContentFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'content', 'image',
        'video_url', 'views', 'is_featured', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---------- Relasi ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(PostRating::class);
    }

    public function viewLogs(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    /* ---------- Scope ---------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        return $term === '' ? $query : $query->where(fn ($q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%")
            ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', "%{$term}%")));
    }

    /** Urutan: new (terbaru), popular (views terbanyak), top (rating tertinggi). */
    public function scopeSorted(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'popular' => $query->orderByDesc('views')->orderByDesc('published_at'),
            'top' => $query->withAvg('ratings', 'rating')->orderByDesc('ratings_avg_rating')->orderByDesc('views'),
            default => $query->latest('published_at'),
        };
    }

    /** Relasi standar yang dibutuhkan kartu berita. */
    public function scopeForCard(Builder $query): Builder
    {
        return $query->with(['user:id,name,username,avatar', 'category:id,name,slug']);
    }

    /* ---------- Atribut turunan ---------- */

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->image) {
                return media_url($this->image);
            }
            if ($id = $this->youtubeId()) {
                return "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
            }

            return asset('images/tags/abstract.png');
        });
    }

    protected function excerpt(): Attribute
    {
        return Attribute::get(fn () => Str::limit(ContentFormatter::toPlainText($this->content), 220));
    }

    protected function dateLabel(): Attribute
    {
        return Attribute::get(fn () => ($this->published_at ?? $this->created_at)?->format('F d , Y'));
    }

    protected function contentHtml(): Attribute
    {
        return Attribute::get(fn () => ContentFormatter::toHtml($this->content));
    }

    public function youtubeId(): ?string
    {
        if (! $this->video_url) {
            return null;
        }
        preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $this->video_url, $m);

        return $m[1] ?? null;
    }

    public function isBookmarkedBy(?User $user): bool
    {
        return $user !== null && in_array($this->id, $user->bookmarkedIds(), true);
    }

    public function canBeManagedBy(?User $user): bool
    {
        return $user !== null && ($user->id === $this->user_id || $user->isAdmin());
    }

    /** Buat slug unik dari judul. */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::limit($title, 150, '')) ?: 'berita';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
