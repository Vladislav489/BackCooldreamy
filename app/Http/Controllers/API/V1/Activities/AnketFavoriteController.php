<?php

namespace App\Http\Controllers\API\V1\Activities;

use App\Http\Controllers\Controller;
use App\Models\FavoriteProfile;
use App\Models\User;
use App\Services\Probability\AnketProbabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AnketFavoriteController extends Controller
{
    public static function getMyFavorite()
    {
        $users = Auth::user()->favorite_users;
        return response($users);
    }

    public static function getFavoritedMe()
    {
        $users = Auth::user()->loving_users;
        return response($users);
    }

    public static function getMutualFavorite()
    {
        $users = Auth::user()->mutualFavoriteUsers();
        return response($users);
    }

    public function addFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $another_user = User::find($request->user_id);
        Auth::user()->addFavorite($another_user);

        if ($another_user->gender == 'female' && $another_user->is_real == false) {
            $service = new AnketProbabilityService();
            $service->addToFavorite($another_user, Auth::user());
        }

        return (self::getMyFavorite());
    }

    public function disableFromFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        FavoriteProfile::where('user_id', Auth::user()->id)->where('favorite_user_id', $request->user_id)->update(['disabled' => true]);
        return (self::getMyFavorite());
    }

}
