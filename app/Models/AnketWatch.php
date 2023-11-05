<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnketWatch extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at'; // имя столбца, содержащего время создания записи
    const UPDATED_AT = 'updated_at'; // имя столбца, содержащего время последнего обновления записи

    public $timestamps = true;
    protected $fillable = ['user_id', 'target_user_id', 'created_at', 'updated_at'];
    protected $dates = ['created_at', 'updated_at'];

}
