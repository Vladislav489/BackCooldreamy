<?php

namespace App\Models\Operator;

use App\Models\Letter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorLetterLimit extends Model
{
    use HasFactory;

    protected  $fillable = [
        'man_id',
        'girl_id',
        'limits',
        'letter_id'
    ];

    public function girl()
    {
        return $this->belongsTo(User::class, 'girl_id');
    }

    public function man()
    {
        return $this->belongsTo(User::class, 'man_id');
    }

    public function letter()
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }
}
