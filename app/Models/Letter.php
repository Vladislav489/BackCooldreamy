<?php

namespace App\Models;

use App\Models\Operator\Forwarded;
use App\Models\Operator\OperatorLetterLimit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Letter extends Model implements Forwarded
{
    use HasFactory;

    const COUNT_FREE_IMAGES = 8;

    protected $fillable = [
        'first_user_id',
        'second_user_id',
        'is_ignored_by_first_user',
        'is_ignored_by_second_user',
        'disabled',
    ];

    protected $appends = [
        'self_user',
        'other_user',
    ];

    /**
     * @return HasOne
     */
    public function limit(): HasOne
    {
        return $this->hasOne(OperatorLetterLimit::class, 'letter_id');
    }


    public function users() {
        return $this->belongsToMany(User::class);
    }

    public function letter_messages() {
        return $this->hasMany(LetterMessage::class);
    }

    public function unreadMessages()
    {
        return $this->hasMany(LetterMessage::class)->where('is_read_by_recepient', false);
    }

    /**
     * @return BelongsTo
     */
    public function firstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_user_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function secondUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_user_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(LetterMessage::class)->latest();
    }

    /**
     * @return HasOne
     */
    public function lastChatMessage(): HasOne
    {
        return $this->hasOne(LetterMessage::class)->latest();
    }

    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(LetterMessage::class);
    }

    public function another_user() {
        $user_id = Auth::user()->id;
        if ($this->first_user_id === $user_id) {
            $another_user_id = 'second_user_id';
        } else {
            $another_user_id = 'first_user_id';
        }
        return $this->belongsTo(User::class, $another_user_id);
    }

    public function getOtherUserAttribute()
    {
        if ($this->relationLoaded('firstUser') && $this->relationLoaded('secondUser')) {
            return $this->secondUser->is_real ? $this->secondUser : $this->firstUser;
        }

        return null;
    }

    public function getSelfUserAttribute()
    {
        if ($this->relationLoaded('firstUser') && $this->relationLoaded('secondUser')) {
            return $this->secondUser->is_real ? $this->firstUser : $this->secondUser;
        }

        return null;
    }
}
