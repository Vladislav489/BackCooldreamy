<?php


namespace App\Models\LimitSystem;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimitSystemGroups extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
    ];
}
