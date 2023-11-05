<?php


namespace App\Models\Auth;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersTokenFireBase extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'users_token_fire_bases';

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'token_fire_base',
    ];
}
