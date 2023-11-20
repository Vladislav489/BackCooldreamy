<?php

namespace App\Models;

use App\Models\Operator\Forwarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;




class Chat extends Model implements Forwarded{
    use HasFactory;

    const COUNT_FREE_IMAGES = 3;

    protected $fillable = [
        'first_user_id',
        'second_user_id',
        'is_ignored_by_first_user',
        'is_ignored_by_second_user',
        'disabled',
        'deleted_by_first_user',
        'deleted_by_second_user',
        'uuid'
    ];
    protected $table = 'chats';
    protected $appends = [
        'self_user',
        'other_user',
        'model_type',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function unreadMessages()
    {
        return $this->hasMany(ChatMessage::class)->where('is_read_by_recepient', false);
    }

    public function chat_messages()
    {
        return $this->hasMany(ChatMessage::class)->with('operator_ansver');
    }

    public function chat_messages8hour(){
        return $this->hasMany(ChatMessage::class)->with('chat_messageable');
    }

    /**
     * @return HasOne
     */
    public function limit(): HasOne
    {
        return $this->hasOne(OperatorChatLimit::class, 'chat_id');
    }

    /**
     * @return BelongsTo
     */
    public function firstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'first_user_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->with(['operator_ansver','chat_messageable'])->latest();
    }

    /**
     * @return string
     */
    public function getModelTypeAttribute()
    {
        return 'chat';
    }

    /**
     * @return HasOne
     */
    public function lastChatMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class)->latest();
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



    /**
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * @return BelongsTo
     */
    public function secondUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_user_id', 'id');
    }

    public function another_user()
    {
        $user_id = Auth::user()->id;
        if ($this->first_user_id == $user_id) {
            $another_user_id = 'second_user_id';

        } else {
            $another_user_id = 'first_user_id';
        }

        return $this->belongsTo(User::class, $another_user_id, 'id');
    }

    public function my_self_user()
    {
        $user_id = Auth::user()->id;
        if ($this->first_user_id == $user_id) {
            $another_user_id = 'first_user_id';

        } else {
            $another_user_id = 'second_user_id';
        }
        return $this->belongsTo(User::class, $another_user_id, 'id');
    }

    public function total_unread_messages()
    {

    }
}
