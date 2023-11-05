<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Ryzhakov Alexey 2023-06-20
 *
 * Сущность отвечает за рейтинг пользователя
 */
class Rating extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'ratings';

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'rating'
    ];

    /**
     * @return mixed
     */
    public function getLastActivityValue(): mixed
    {
        return $this->history->where('created_at', '>=', Carbon::now()->subHours(72))->sum('limit');
    }

    /**
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(RatingHistory::class, 'rating_id', 'id');
    }
}
