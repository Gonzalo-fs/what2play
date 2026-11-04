<?php

namespace App\Models;

use Database\Factories\GenreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Genre extends Model
{
    /** @use HasFactory<GenreFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * @return BelongsToMany<Game, $this>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
    }

    /**
     * Users who prefer this item.
     *
     * @return BelongsToMany<User, $this>
     */
    public function preferredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_genre_preferences');
    }
}
