<?php
namespace App\Models\LimitSystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LimitSystemUser extends Model
{
    use HasFactory;
    protected $fillable = [
            "user_id",
            "group_id",
            'active',
            "last_assignments_id",
            "last_assignments_sort",
            "step_cron_counter",
    ];
}
