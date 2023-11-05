<?php
namespace App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PasswordReset extends Model{
    use HasFactory;
    protected $table = 'password_resets';
    protected $fillable = [
        'email',
        'token',
    ];
}
