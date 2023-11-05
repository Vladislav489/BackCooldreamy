<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCooperationCron extends Model{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'data_user',
        'type_action',
        'status',
    ];
}
