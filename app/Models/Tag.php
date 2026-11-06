<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /** Ubah teks "sport, travel" menjadi daftar ID tag (dibuat bila belum ada). */
    public static function idsFromString(?string $input): array
    {
        return collect(explode(',', (string) $input))
            ->map(fn ($name) => trim(ltrim(trim($name), '#')))
            ->filter()
            ->unique(fn ($name) => Str::slug($name))
            ->take(10)
            ->map(fn ($name) => static::firstOrCreate(['slug' => Str::slug($name)], ['name' => Str::limit($name, 50, '')])->id)
            ->values()
            ->all();
    }
}
