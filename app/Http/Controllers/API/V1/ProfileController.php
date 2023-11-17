<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\User\ProfileTypeEnum;
use App\Events\SympathyEvent;
use App\Events\UpdateNotificationEvent;
use App\Http\Controllers\Controller;
use App\Mail\VerificationMail;
use App\Models\Chat;
use App\Models\Image;
use App\Models\StatisticSite\UserWatch;
use App\Services\FireBase\FireBaseService;
use App\Services\Probability\AnketProbabilityService;
use App\Traits\UserSubscriptionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class ProfileController extends Controller
{
    /** @var UserSubscriptionTrait */
    private UserSubscriptionTrait $userSubscriptionTrait;

    public function __construct(UserSubscriptionTrait $userSubscriptionTrait)
    {
        $this->userSubscriptionTrait = $userSubscriptionTrait;
    }

    public function get_my_profile()
    {
        $user = Auth::user();
        $user->load(['prompt_targets', 'prompt_interests', 'prompt_finance_states', 'prompt_sources', 'prompt_want_kids', 'prompt_relationships', 'prompt_careers']);
        $profilePhotos = Image::where('user_id', $user->id)
            ->where('category_id', 2)
            ->get();

        $user->profile_photo = $profilePhotos;
        $user->is_active_subscription = $this->userSubscriptionTrait->checkUserExistsSubscription($user);

        return response($user);
    }

    public function get_profile(Request $request)
    {
        $authUser = Auth::user();

        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required', 'integer'
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = User::findOrFail($request->user_id);
        $user->load(['prompt_targets', 'prompt_interests', 'prompt_finance_states', 'prompt_sources', 'prompt_want_kids', 'prompt_relationships', 'prompt_careers']);

        $user->is_liked = $user->liked_me()->where('users.id', $authUser->id)->exists();
        $user->is_favorite = $authUser->favorite_users_with_disabled()->where('users.id', $user->id)->exists();
        //SympathyEvent::dispatch($user->id, AnketProbabilityService::WATCH, $authUser);

        UserWatch::create([
            'user_id' => $authUser->id,
            'viewed' => $user->id,
        ]);

        $user->profile_photo = Image::where('user_id', $user->id)
            ->where('category_id', 2)
            ->get();
        Auth::user()->addViewedUser($user);


        FireBaseService::sendPushFireBase($user,"СoolDreamy","Someone visited your page", Auth::user()->avatar_url);
//        UpdateNotificationEvent::dispatch($user->id);

        if (!$user->is_real && $user->gender == 'female') {
            $service = new AnketProbabilityService();

            $service->watch($user, $authUser);
        }

        if (!$user->is_real && ($user->profile_type_id != ProfileTypeEnum::STANDARD)) {
//            if (!$request->uuid) {
//                return response(['message' => 'not found'], 404);
//            }

//            if (!Chat::query()->where(function($query) use ($user, $authUser) {
//                $query->where(function ($query) use ($user, $authUser) {
//                    $query->where('first_user_id', $user->id);
//                    $query->where('second_user_id', $authUser->id);
//                })->orWhere(function ($query) use($user, $authUser) {
//                    $query->where('second_user_id', $user->id);
//                    $query->where('first_user_id', $authUser->id);
//                });
//            })->where('uuid', $request->uuid)->exists()) {
//                return response(['message' => 'not found'], 404);
//            }

            OperatorLimitController::addChatLimits($user->id, 5);
        }
        return response($user);
    }

    public function update_my_profile(Request $request)
    {
        //todo сделать валидацию и политики записи
        $user = Auth::user();
        if (isset($request->prompt_targets)) {
            $prompt_targets = json_decode($request->input('prompt_targets'));
            $user->prompt_targets()->sync($prompt_targets);
        }

        if (isset($request->prompt_interests)) {
            $prompt_interests = json_decode($request->input('prompt_interests'));
            $user->prompt_interests()->sync($prompt_interests);
        }

        if (isset($request->prompt_finance_states)) {
            $prompt_finance_states = json_decode($request->input('prompt_finance_states'));
            $user->prompt_finance_states()->sync($prompt_finance_states);
        }

        if (isset($request->prompt_sources)) {
            $prompt_sources = json_decode($request->input('prompt_sources'));
            $user->prompt_sources()->sync($prompt_sources);
        }

        if (isset($request->prompt_want_kids)) {
            $prompt_want_kids = json_decode($request->input('prompt_want_kids'));
            $user->prompt_want_kids()->sync($prompt_want_kids);
        }

        if (isset($request->prompt_relationships)) {
            $prompt_relationships = json_decode($request->input('prompt_relationships'));
            $user->prompt_relationships()->sync($prompt_relationships);
        }

        if (isset($request->prompt_careers)) {
            $prompt_careers = json_decode($request->input('prompt_careers'));
            $user->prompt_careers()->sync($prompt_careers);
        }


        if($request->get('email')){
           if($request->get('email') != $user->email) {
               if (User::query()->where("email", "=", $request->get('email'))->exists()) {
                   return response()->json(['error' => "User with mail " . $request->get('email') . " exists"], 401);
               }
               $user->is_email_verified = false;
               $user->save();
                try {
                    Mail::to($request->get('email'))->send(new VerificationMail($user->token, $user));
                }catch (\Throwable $e){

                }
           }
        }
        $user->update($request->all());
        $no_cache_user = User::find(Auth::user()->id);
        $no_cache_user->load(['prompt_targets', 'prompt_interests', 'prompt_finance_states', 'prompt_sources', 'prompt_want_kids', 'prompt_relationships', 'prompt_careers']);

        return response($no_cache_user);
    }
}
