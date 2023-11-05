<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilePicture extends Model {
    use HasFactory;

    protected $fillable = [
        'picture_url',
        'order_item',
        'disabled',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
