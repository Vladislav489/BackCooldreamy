<?php
namespace App\Models\Chat;

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
}

