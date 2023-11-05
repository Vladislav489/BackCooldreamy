<?php


namespace App\Models\AceSystems;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AceSystemGroups extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
    ];
}
