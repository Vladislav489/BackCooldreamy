<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteProfile extends Model {
    use HasFactory;

    protected $fillable = ['user_id', 'favorite_user_id', 'disabled'];
}
