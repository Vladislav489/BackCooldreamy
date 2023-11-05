<?php

namespace App\Http\Controllers\API\V1\Activities;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnketWatchController extends Controller
{
    const PER_PAGE = 20;
    public static function getMyWatchers(Request $request)
    {
        $users = Auth::user()->myWatchers()->orderByDesc('created_at')->paginate($request->per_page);
        return response()->json(['data' => $users]);
    }

    public static function getMyWatched()
    {
        $users = Auth::user()->usersWatchedByThisUser()->orderByDesc('created_at')->get();
        return response()->json(['data' => $users]);
    }

    public static function getMutualWatchedUsers()
    {
        $users = Auth::user()->mutualWatchedUsers();
        return response()->json(['data' => $users]);
    }
}
