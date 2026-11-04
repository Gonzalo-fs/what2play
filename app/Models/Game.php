<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'developer_id',
        'title',
        'slug',
        'description',
        'release_year',
        'disclaimer',
        'video_url',
        'price',
        'buy_url',
        'image_path',
        'difficulty',
        'audience',
        'graphics',
        'requirements',
        'requirements_specs',
        'game_length_minutes',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
            'price' => 'decimal:2',
            'game_length_minutes' => 'integer',
            'score' => 'decimal:1',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Game>  $query
     * @param  array<int>|null  $ids
     * @return Builder<Game>
     */
    public function scopeFilterByGenreIds(Builder $query, ?array $ids): Builder
    {
        if ($ids === null || $ids === []) {
            return $query;
        }

        return $query->whereHas('genres', function (Builder $genresQuery) use ($ids): void {
            $genresQuery->whereIn('genres.id', $ids);
        });
    }

    /**
     * @param  Builder<Game>  $query
     * @param  array<int>|null  $ids
     * @return Builder<Game>
     */
    public function scopeFilterByModeIds(Builder $query, ?array $ids): Builder
    {
        if ($ids === null || $ids === []) {
            return $query;
        }

        return $query->whereHas('modes', function (Builder $modesQuery) use ($ids): void {
            $modesQuery->whereIn('modes.id', $ids);
        });
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeFilterByMaxPrice(Builder $query, mixed $maxPrice): Builder
    {
        if ($maxPrice === null || $maxPrice === '') {
            return $query;
        }

        return $query->where(function (Builder $priceQuery) use ($maxPrice): void {
            $priceQuery->whereNull('price')
                ->orWhere('price', '<=', $maxPrice);
        });
    }

    /**
     * @param  Builder<Game>  $query
     * @return Builder<Game>
     */
    public function scopeOrderByScoreDesc(Builder $query): Builder
    {
        return $query
            ->orderByRaw('score IS NULL')
            ->orderByDesc('score')
            ->orderByDesc('id');
    }

    /**
     * @return BelongsTo<Developer, $this>
     */
    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    /**
     * @return BelongsToMany<Genre, $this>
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    /**
     * @return BelongsToMany<Mode, $this>
     */
    public function modes(): BelongsToMany
    {
        return $this->belongsToMany(Mode::class);
    }

    /**
     * Users that have this game in their library.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('completed', 'saved')
            ->withTimestamps();
    }
}
