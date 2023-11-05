<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptTarget extends Model
{
    use HasFactory;
    protected $fillable = [
        'text',
        'gender',
    ];
    public function users(){
        return $this->hasMany(User::class);
    }
}
