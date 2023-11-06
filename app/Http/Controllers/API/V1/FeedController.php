<?php

namespace App\Http\Controllers\API\V1;

use App\Events\SympathyEvent;
use App\Http\Controllers\API\V1\Activities\AnketLikeController;
use App\Http\Controllers\Controller;
use App\Jobs\ResponsibleLikeJob;
use App\Models\Feed;
use App\Models\ResponsibleLikeProbability;
use App\Services\FireBase\FireBaseService;
use App\Services\Probability\AnketProbabilityService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;

class FeedController extends Controller
{
    public function index(Request $request)
    {

        $userId = Auth::user()->id;
        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }
        $appUrl = env('APP_URL');
        $users = User::select('id', 'name', 'avatar_url', 'avatar_url_thumbnail', 'birthday', 'state', 'country',
            DB::raw('YEAR(NOW()) - YEAR(birthday) - (DATE_FORMAT(NOW(), "%m%d") < DATE_FORMAT(birthday, "%m%d")) AS age'))
            ->where('gender', '=', Auth::user()->gender_for_search())
            ->where('id', '<>', $userId)
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('to_user_id')
                    ->from('feeds')
                    ->where('from_user_id', '=', $userId)
                    ->where('is_liked', true);
            })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('to_user_id')
                    ->from('feeds')
                    ->where('from_user_id', '=', $userId)
                    ->where('is_skipped', true);;
            })
            ->inRandomOrder()
            ->paginate($perPage);
        $users->map(function ($user) use ($appUrl) {
//            if (!Str::startsWith($user->avatar_url, 'http')) {
//                $user->avatar_url = $appUrl . '/' . $user->avatar_url;
//            }
//            if (!Str::startsWith($user->avatar_url_thumbnail, 'http')) {
//                $user->avatar_url_thumbnail = $appUrl . '/' . $user->avatar_url_thumbnail;
//            }
            return $user;
        });
        return $users;
    }

    public function set_feed_liked(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        if (Feed::where('from_user_id', Auth::user()->id)
                ->where('to_user_id', $request->id)->count() > 0) {
            return response()->json(['error' => 'запись уже есть'], 500);
        };
        $sender  = Auth::user();
        $another_user = User::findorfail($request->id);
        $feed = new Feed();
        $feed->from_user_id = $sender->id;
        $feed->to_user_id = $request->id;
        $feed->is_liked = true;
        $feed->save();
        // Отправляем евент человеку которому ставим лайк
        FireBaseService::sendPushFireBase($another_user,"СoolDreamy","You have a new like",$sender->avatar_url);
        //SympathyEvent::dispatch($another_user->id, AnketProbabilityService::LIKE, Auth::user());
        if ($another_user->gender == 'female' && $another_user->is_real == false) {
            $service = new AnketProbabilityService();
            $service->like($another_user,$sender);
        }

        if (!$another_user->is_real) {
            OperatorLimitController::addChatLimits($another_user->id, 4);
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function set_feed_skipped(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        if (Feed::where('from_user_id', Auth::user()->id)
                ->where('to_user_id', $request->id)->count() > 0) {
            return response()->json(['error' => 'запись уже есть'], 500);
        };
        User::findorfail($request->id);
        $feed = new Feed();
        $feed->from_user_id = Auth::user()->id;
        $feed->to_user_id = $request->id;
        $feed->is_skipped = true;
        $feed->save();
        return response()->json(['message' => 'success'], 200);
    }

    public function statistic()
    {
        $user = Auth::user();

        $countWatches = $user->myWatchers()->where('is_read', false)->count();
        $countLikes = $user->liked_me()->where('is_read', false)->count();
        $countMyLikes = $user->feeds_users()->where('is_read', false)->count();
        $countMutual = count($user->MutualLikedUsers());

        return response()->json([
            'count_watches' => $countWatches,
            'count_likes' => $countLikes,
            'count_my_likes' => $countMyLikes,
            'count_mutual' => $countMutual
        ]);
    }

    public function read(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type');
        $id = $request->get('id');

        if ($type == 'WATCH') {
            $user->myWatchers()->where('is_read', false)->update(['is_read' => true]);
        } else {
            $user->liked_me()->where('is_read', false)->update(['is_read' => true]);
        }

        return response()->json(['message' => 'success']);
    }
}
