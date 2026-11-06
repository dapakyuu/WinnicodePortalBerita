<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'avatar', 'banner', 'bio'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'bookmarks')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** ID berita yang dibookmark user ini (di-cache per request). */
    public function bookmarkedIds(): array
    {
        return once(fn () => $this->bookmarks()->pluck('posts.id')->all());
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => media_url($this->avatar, 'images/avatar/default.svg'));
    }

    protected function bannerUrl(): Attribute
    {
        return Attribute::get(fn () => media_url($this->banner, 'images/tags/abstract.png'));
    }
}
