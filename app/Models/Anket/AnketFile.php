<?php

namespace App\Models\Anket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnketFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'anket_id',
        'type',
        'path',
        'url'
    ];
}
