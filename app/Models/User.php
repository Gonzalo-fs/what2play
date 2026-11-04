<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'max_price'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'max_price' => 'decimal:2',
        ];
    }

    /**
     * Games in the user's library.
     *
     * @return BelongsToMany<Game, $this>
     */
    public function library(): BelongsToMany
    {
        return $this->belongsToMany(Game::class)
            ->withPivot('completed', 'saved')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Genre, $this>
     */
    public function preferredGenres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'user_genre_preferences');
    }

    /**
     * @return BelongsToMany<Mode, $this>
     */
    public function preferredModes(): BelongsToMany
    {
        return $this->belongsToMany(Mode::class, 'user_mode_preferences');
    }
}
