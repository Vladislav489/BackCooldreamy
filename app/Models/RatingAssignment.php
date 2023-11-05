<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ryzhakov Alexey 2023-06-20
 *
 * Сущность отвечает за какие рейтинги какой баланс
 */
class RatingAssignment extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'rating_assignments';

    /**
     * @var string[]
     */
    protected $fillable = [
        'slug',
        'limit'
    ];
}
