<?php


namespace App\Models\Pay;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayGoogle extends Model{
    use HasFactory;
    protected $table = 'pay_googles';
    protected $fillable = [
        'user_id',
        'data_pay',
    ];
}
