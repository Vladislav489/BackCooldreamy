<?php

namespace App\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Premuim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'period_start',
        'period_end',
        'status'
    ];

    public static function addNewPremuim($user_id,$list_id){
        $modelType = PremiumList::findOrFail($list_id);
        if(is_null($modelType)) {
            return ['error' => 'type error'];
        }

        if ($modelType->one_time) {
                if (Premuim::where('user_id', $user_id)
                    ->where('service_id', $modelType->id)
                    ->exists()) {
                    return['error' => 'type error'];
                }
        }
        $timeNow = Carbon::now();


        if (Premuim::where('user_id', $user_id)
            ->where('service_id', $modelType->id)
            ->where('period_start', '<=', $timeNow) // дата и время начала оплаченного периода
            ->where('period_end', '>=', $timeNow) // дата и время окончания оплаченного периода
            ->exists()) {
            return ['error' => 'already exists'];
        }
        self::insert([
            'user_id' => $user_id,
            'service_id' => $modelType->id,
            'period_start' => \Illuminate\Support\Carbon::now(),
            'period_end' => Carbon::now()->addWeeks( $modelType->duration),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'status' => 'success'
        ]);
        return response()->json(['message' => 'success']);

    }
}
