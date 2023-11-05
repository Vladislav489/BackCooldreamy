<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \App\Models\ProfileType;
class CsvUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'email', 'name', 'state', 'country', 'birthday',
        'prompt_target_id', 'prompt_finance_state_id', 'prompt_source_id',
        'prompt_want_kids_id', 'prompt_relationship_id', 'prompt_career_id',
        'about_self', 'password', 'is_sync', 'profile_type_id'
    ];

    protected $dates = [
        'created_at', 'updated_at', 'birthday'
    ];

    public function profile_type()
    {
        return $this->belongsTo(ProfileType::class, 'profile_type_id', 'id');
    }
}
