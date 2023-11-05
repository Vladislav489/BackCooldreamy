<?php


namespace App\Models\StatisticSite;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInputs extends Model {
    use HasFactory;
    protected $fillable = [
        'user_id',
    ];
}
