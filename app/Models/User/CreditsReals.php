<?php
namespace App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditsReals extends Model{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'credits',
    ];
   public static function getUserCreditsById($user_id) {
        $creditsReals =  CreditsReals::query()->where('user_id',$user_id)->get()->first();
        if(is_null($creditsReals)){
            try {
                $creditsReals = CreditsReals::create([
                    'user_id' => $user_id,
                    'credits' => 0
                ]);
                return $creditsReals;
            }catch (\Throwable $e){
               logger($e->getMessage());
            }
        }
        return $creditsReals;
   }
}
