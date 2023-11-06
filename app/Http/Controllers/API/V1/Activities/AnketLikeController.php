<?php

namespace App\Http\Controllers\API\V1\Activities;

use App\Events\OpenUserProfileEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PaginateHelper;
use App\Models\FavoriteProfile;
use App\Models\Feed;
use App\Models\ResponsibleLikeProbability;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AnketLikeController extends Controller
{
    public static function getMyLikes(Request $request)
    {
        $users = Auth::user()->feeds_users()->orderBy('created_at', 'desc')->paginate($request->per_page);
        return response()->json(['data' => $users]);
    }

    public static function getLikedMe(Request $request)
    {
        $users = Auth::user()->liked_me()->orderBy('created_at', 'desc')->paginate($request->per_page);
        return response()->json(['data' => $users]);
    }

    public function getMutualLikedUsers(Request $request)
    {
        $users = PaginateHelper::paginate(Auth::user()->MutualLikedUsers(), $request->per_page ?? 10);
        return response()->json(['data' => (array)$users]);
    }

    public static function getResponsibleLikeProbability(User $user)
    {
        $user_like_count = Feed::where('from_user_id', $user->id)->where('is_liked', true)->count();
        $responsibleLikeProb = ResponsibleLikeProbability::where('like_count', '>=', $user_like_count)
            ->orderBy('like_count')
            ->first();
        return ($responsibleLikeProb->probability);
    }

    public function openUserProfile($id)
    {
       // OpenUserProfileEvent::dispatch($id, Auth::id());

        return response()->json(['message' => 'success']);
    }

}
