<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enum\Action\ActionEnum;
use App\Models\User\CreditsReals;
use App\Repositories\Auth\CreditLogRepository;

use App\Traits\UserChatTrait;
use App\Traits\UserOperatorTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, UserOperatorTrait, UserChatTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'state',
        'country',
        'birthday',
        'gender',
        'credits',
        'avatar_url',
        'avatar_url_thumbnail',
        'is_new_avatar',
        'is_confirmed_user',
        'about_self',
        'last_online',
        'is_premium',
        'premium_expire',
        'is_in_top',
        'ref_url',
        'top_expire',
        'credits',
        'tags',
        'type',
        'credits',
        'email_verified_at',
        'prompt_target_id',
        'prompt_finance_state_id',
        'prompt_source_id',
        'prompt_want_kids_id',
        'prompt_relationship_id',
        'prompt_career_id',
        'online',
        'language',
        'created_at',
        'updated_at',
        'timezone',
        'token',
        'is_email_verified',
        'search_age_from',
        'search_age_to',
        'search_gender',
        'is_pwa',
        'onesignal_token',
        'from_mobile_app'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
//    protected $visible = [
//        'id', 'name', 'avatar_url', 'avatar_url_thumbnail', 'birthday', 'state', 'country'
//    ];
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = [
      //  'password',
        'remember_token',
//        'is_real',
        //'credits'
    ];
    public $timestamps = true;

    protected $appends = ['age', 'user_avatar_url', 'user_thumbnail_url'];

    public function getUserAvatarUrlAttribute()
    {
        return $this->avatar_url ? 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $this->avatar_url) : config('app.url') . ($this->gender == 'male' ? '/empty-avatar.png' : '/empty-girl-avatar.jpeg');
    }

    public function getUserThumbnailUrlAttribute()
    {
        return $this->avatar_url_thumbnail ?  'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $this->avatar_url_thumbnail) : config('app.url') . ($this->gender == 'male' ? '/empty-avatar.png' : '/empty-girl-avatar.jpeg');
    }

    /**
     * @return HasOne
     */
    public function userGeo(): HasOne
    {
        return $this->hasOne(UserGeo::class, 'user_id');
    }

    public function getPasswordAttribute()
    {
        if (isset($this->attributes['password'])){
            return $this->attributes['password'];
        }else{
            $this->attributes['password'] = null;
            return $this->attributes['password'];
        }
    }

    public function getCreditsAttribute()
    {
        return $this->attributes['credits'];
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = [];

    public function gender_for_search()
    {
        if ($this->gender == 'male') {
            return "female";
        } else {
            return "male";
        }
    }


    public function prompt_targets()
    {
        return $this->belongsToMany(PromptTarget::class);
    }

    public function prompt_interests()
    {
        return $this->belongsToMany(PromptInterest::class);
    }


    public function prompt_finance_states()
    {
        return $this->belongsToMany(PromptFinanceState::class);
    }

    public function prompt_sources()
    {
        return $this->belongsToMany(PromptSource::class);
    }

    public function prompt_want_kids()
    {
        return $this->belongsToMany(PromptWantKids::class, 'prompt_want_kids_user', 'user_id', 'prompt_want_kids_id');
    }

    public function prompt_relationships()
    {
        return $this->belongsToMany(PromptRelationship::class);
    }

    public function prompt_careers()
    {
        return $this->belongsToMany(PromptCareer::class);
    }

    public function favorite_users()
    {
        return $this->belongsToMany(User::class, 'favorite_profiles', 'user_id', 'favorite_user_id')
            ->wherePivot('disabled', '!=', true);
    }

    public function favorite_users_with_disabled()
    {
        return $this->belongsToMany(User::class, 'favorite_profiles', 'user_id', 'favorite_user_id');
    }

    public function loving_users()
    {
        return $this->belongsToMany(User::class, 'favorite_profiles', 'favorite_user_id', 'user_id')
            ->wherePivot('disabled', '!=', true);
    }

    public function mutualFavoriteUsers()
    {
        return User::whereHas('favorite_users', function ($q) {
            $q->where('favorite_user_id', '=', $this->id);
        })->whereHas('loving_users', function ($q) {
            $q->where('user_id', '=', $this->id);
        })->get();
    }

    public function feeds_users()
    {
        return $this->belongsToMany(User::class, 'feeds', 'from_user_id', 'to_user_id')
            ->wherePivot('is_liked', '=', true);
    }

    public function liked_me()
    {
        return $this->belongsToMany(User::class, 'feeds', 'to_user_id', 'from_user_id')
            ->wherePivot('is_liked', '=', true);
    }

    public function MutualLikedUsers()
    {
        $feedsUsers = $this->feeds_users;
        $likedMeUsers = $this->liked_me;

        return $feedsUsers->intersect($likedMeUsers);
    }

    public function myMutualLikedUsers()
    {
        return $this->belongsToMany(User::class, 'feeds', 'from_user_id', 'to_user_id')
            ->wherePivot('is_liked', '=', true)
            ->whereHas('liked_me', function ($query) {
                $query->where('is_liked', '=', true);
            });
    }

    public function all_feeds_users()
    {
        return $this->belongsToMany(User::class, 'feeds', 'from_user_id', 'to_user_id');
    }

    public function skipped_users()
    {
        return $this->belongsToMany(User::class, 'feeds', 'from_user_id', 'to_user_id')
            ->wherePivot('is_skipped', '=', true);
    }

    public function profile_pictures()
    {
        return $this->hasMany(ProfilePicture::class);
    }

    public function prompt_target()
    {
        return $this->belongsTo(PromptTarget::class);
    }

    public function prompt_finance_state()
    {
        return $this->belongsTo(PromptFinanceState::class);
    }

    public function prompt_career()
    {
        return $this->belongsTo(PromptCareer::class);
    }

    public function prompt_relationship()
    {
        return $this->belongsTo(PromptRelationship::class);
    }

    public function prompt_source()
    {
        return $this->belongsTo(PromptSource::class);
    }

    public function getAgeAttribute()
    {
        $birthday = Carbon::parse($this->birthday);
        return $birthday->age;
    }

    public function profile_type()
    {
        return $this->belongsTo(ProfileType::class, 'profile_type_id', 'id');
    }

    public function aces()
    {
        return $this->hasMany(Ace::class, 'email', 'email');
    }

    public function ace_limit()
    {
        return $this->hasOne(AceLimit::class);
    }

    public function myWatchers()
    {
        return $this->belongsToMany(User::class, 'anket_watches', 'target_user_id', 'user_id');
    }

    public function usersWatchedByThisUser()
    {
        return $this->belongsToMany(User::class, 'anket_watches', 'user_id', 'target_user_id');
    }

    public function mutualWatchedUsers()
    {
        return User::whereHas('usersWatchedByThisUser', function ($q) {
            $q->where('target_user_id', '=', $this->id);
        })->whereHas('myWatchers', function ($q) {
            $q->where('user_id', '=', $this->id);
        })->orderByDesc('created_at')->get();
    }

    public function addViewedUser($user)
    {
        $this->usersWatchedByThisUser()->syncWithoutDetaching($user->id);
        AnketWatch::where('target_user_id', $user->id)->where('user_id', $this->id)->update(['updated_at' => now()]);
    }

    public function addFavorite($user)
    {
        $this->favorite_users_with_disabled()->syncWithoutDetaching($user->id);
        FavoriteProfile::where('user_id', $this->id)->where('favorite_user_id', $user->id)->update(['disabled' => false]);
    }

    public function addLike($user)
    {
        $this->all_feeds_users()->syncWithoutDetaching($user->id);
        Feed::where('from_user_id', $this->id)->where('to_user_id', $user->id)->update(['is_liked' => true]);
    }

    /**
     * @return HasOne
     */
    public function rating(): HasOne {
        return $this->hasOne(Rating::class, 'user_id', 'id');
    }
    //списание средст юзера мужчины женщина бесплатно
    public function check_payment_man($cost,$service_id = null,$action = null ,$second_user_id = 0){
        // получаем данные пользоваетля
        if($this->is_real && $this->gender == 'male') {
            return $this->pay($cost,$service_id,$action ,$second_user_id);
        } else {
            return true;
        }
    }
    //списание средст для всех
    public function addCreditsReal($countCreadits){
           $creaditsReals = User\CreditsReals::getUserCreditsById($this->id);
           if(!$creaditsReals) {
              try {
                  CreditsReals::created([
                      'user_id' => $this->id,
                      'credits' => $countCreadits
                  ]);
                  return true;
              }catch (\Throwable $e){
                  return  false;
              }
           }else
                return $creaditsReals->update(["credits" => $creaditsReals->creadits + $countCreadits]);
    }

    public function check_payment($cost,$service_id = null,$action = null ,$second_user_id = 0){
        return ($this->is_real)?$this->pay($cost, $service_id, $action, $second_user_id):true;
    }
    //Оплата в чистом виде
    private function pay($cost,$service_id = null,$action = null ,$second_user_id = 0){
       /*
        * если у юзера есть подписка то собщения бесплатные и есть лимиты на отправку фото видео
        *
        * если подписка закончилась то снимаются только на сообщения бесплатные лимиты
        * а потом платные
        *
        * еслизакончились то все
        *
        */

        $repository = new CreditLogRepository();
//        $resultSubscriptions =  Subscriptions::getValidPeriodAndLimit($this->id,$action);
        // получаем баланс пользователя для проверки (платные кредиты)
        $creaditsReals = User\CreditsReals::getUserCreditsById($this->id);
        // получаем баланс пользователя для проверки (бесплатные кредиты)
//        if($resultSubscriptions) {
//            $repository->logPayment($this, 0, $second_user_id, $service_id,$action);
//            return true;
//        }

        $myCredits = $this->credits;
        // средств на счету пользователя больше чем сумма услуги?
        if ($myCredits >= $cost && $action == ActionEnum::SEND_MESSAGE ) {
            // если да, рассчитываем сколько останется на счету
            $newCredits = $myCredits - $cost;
            $repository->logPayment($this, $cost, $second_user_id, $service_id,$action);
            // запускаем метод на списание указанного количества средств со счёта
             $this->update(["credits" => $newCredits]);
            return true;
        }
        if($creaditsReals->credits >= $cost && in_array($action, (new ActionEnum())->getActions())) { //если из реальных кредитов достаточно средст делаем оплату
            dump($creaditsReals->credits, (new ActionEnum())->getActions());
                    $newCredits = $creaditsReals->credits - $cost;//вычитаем цену за сервис
                    $repository->logPayment($this, $cost, $second_user_id, $service_id,$action);//остаток записывем  на реальные кредиты
                    $creaditsReals->update(["credits" => $newCredits]);//остаток записывем  на реальные кредиты
                    return true;
        }


        // Запимь в лог и отправляем собщение о невозможности оплаты
        $newLogItem = new CreditsRefillLog;
        $newLogItem->user_id = $this->id;
        $newLogItem->second_user_id = $second_user_id;
        $newLogItem->balance = $this->credits;
        $newLogItem->cost = $cost;
        $newLogItem->created_at = date("Y-m-d H:i:s");
        $newLogItem->save();
        return false;
    }

    public static function registrationClient($data){
        $field = (new self())->getFillable();
        foreach ($data as  $key => $value){
           if(!in_array($key,$field))
                unset($data[$key]);
        }
        $data['password'] = Hash::make($data['password']);
        $data['token'] = Str::uuid();
        $data['timezone'] = (isset($data['timezone']))?$data['timezone']:'Europe/Moscow';

        $user = new User();
        $user->setRawAttributes($data);
        $user->save();
        return $user;
    }

}
