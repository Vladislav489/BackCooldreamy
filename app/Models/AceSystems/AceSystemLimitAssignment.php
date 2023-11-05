<?php
namespace App\Models\AceSystems;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AceSystemLimitAssignment extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id',
        'sort',
        'step_from',
        'step_to',
    ];
}

