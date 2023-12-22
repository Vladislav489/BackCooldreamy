<?php

namespace App\Http\Controllers;

use App\Enum\User\ProfileTypeEnum;
use App\Enum\User\SearchSortTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class UserController extends Controller
{
    public function fix()
    {
//        $users = DB::table('users')->whereNotIn('id', [124518, 124519, 124487])->where([['gender', '=', 'female'], ['is_real', '=', 1]])->pluck('id')->toArray();
        $users = [126763,126906,126912,126914,126916,126917,126919,126922,126926,126927,126938,126940,126941,126943,126944,126946,126949,126953,126955,126968,126969,126971,126974,126977,126978,126981,126983,126985,126988,126990,126991,126994,126995,126997,126999,127002,127005,127007,127016,127019,127021,127022,127024,127028,127032,127034,127035,127037,127040,127047,127053,127060,127062,127070,127075,127080,127085,127093,127096,127098,127102,127103,127107,127108,127111,127112,127116,127117,127120,127125,127129,127132,127133,127134,127145,127147,127150,127151,127156,127158,127173,127176,127177,127179,127183,127184,127187,127191,127192,127196,127203,127211,127214,127229,127237,127238,127241,127246,127250,127254,127261,127265,127267,127271,127276,127285,127287,127291,127294,127295,127297,127298,127302,127305,127317,127327,127328,127338,127339,127352,127356,127357,127367,127373,127377,127383,127390,127394,127400,127402,127411,127413,127415,127419,127422,127423,127426,127428,127430,127434,127436,127437,127438,127440,127444,127448,127449,127458,127460,127464,127465,127467,127473,127475,127476,127477,127479,127483,127492,127497,127513,127522,127533,127534,127535,127537,127549,127559,127585,127595,127596,127597,127598,127610,127616,127632,127653,127654,127655,127656,127659,127671,127676,127689,127691,127692,127694,127697,127698,127699,127702,127708,127713,127714,127715,127720,127722,127726,127737,127738,127739,127741,127753,127756,127759,127760,127761,127764,127766,127767,127768,127769,127777,127778,127779,127780,127781,127784,127785,127788,127789,127790,127791,127796,127798,127800,127803,127805,127809,127811,127812,127813,127815,127818,127822,127826,127828,127829,127831,127833,127835,127838,127851,127853,127854,127857,127860,127865,127866,127867,127868,127870,127873,127875,127877,127880,127884,127885,127886,127887,127889,127891,127896,127899,127901,127902,127905,127912,127913,127914,127915,127916,127918,127922,127926,127927,127928,127929,127931,127934,127936,127938,127942,127944,127945,127946,127955,127956,127958,127960,127961,127963,127972,127975,127977,127979,127982,127985,127986,127990,127992,127993,128001,128003,128004,128007,128012,128015,128017,128025,128028,128038,128040,128041,128042,128043,128044,128046,128047,128048,128049,128050,128055,128067,128072,128077,128078,128080,128084,128086,128095,128097,128098,128102,128106,128107,128108,128113,128114,128120,128121,128124,128126,128128,128135,128137,128141,128144,128146,128147,128148,128150,128152,128155,128159,128162,128163,128171,128177,128181,128183,128185,128186,128189,128190,128193,128197,128199,128205,128206,128208,128210,128211,128213,128214,128216,128224,128229,128239,128257,128260,128262,128263,128267,128269,128275,128278,128279,128281,128282,128284,128286,128295,128296,128297,128298,128300,128302,128303,128305,128306,128307,128314,128316,128318,128319,128323,128325,128328,128335,128336,128337,128339,128341,128345,128346,128347,128348,128349,128350,128351,128353,128356,128359,128363,128368,128373,128374,128376,128382,128384,128385,128386,128388,128391,128392,128394,128396,128400,128403,128405,128408,128411,128427,128430,128438,128439,128441,128443,128445,128446,128447,128451,128452,128453,128463,128465,128468,128471,128486,128489,128493,128496,128497,128500,128501,128502,128505,128507,128512,128514,128519,128520,128523,128524,128526,128528,128534,128535,128536,128545,128555,128557];
        $chatIds = DB::table('chats')->whereIn('first_user_id', $users)->orWhereIn('second_user_id', $users)->pluck('id')->toArray();
        $chat_messages = DB::table('chat_messages')->whereIn('chat_id', $chatIds)->delete();
        $winks = DB::table('chat_wink_messages')->whereIn('from_user_id', $users)->orWhereIn('to_user_id', $users)->delete();
        $images = DB::table('images')->whereIn('user_id', $users)->delete();
        $chats = DB::table('chats')->whereIn('id', $chatIds)->delete();
        $log = Log::build(['driver' => 'daily', 'path' => storage_path('logs/users/deleted_users.log')]);
        $log->info('Chats deleted: ' . $chats);
        $log->info('Messages deleted: ' . $chat_messages);
        $log->info('Images deleted: ' . $images);
        return ['users' => count($users), 'chats' => $chats, 'winks' => $winks, 'images' => $images];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = 8;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }
        $currentUserId = Auth::user()->id;
        $ageRangeStart = -10; // начало диапазона возраста
        $ageRangeEnd = 5; // конец диапазона возраста
        $appUrl = env('APP_URL');
        $currentAge = DB::table('users')
            ->where('id', $currentUserId)
            ->selectRaw('TIMESTAMPDIFF(YEAR, birthday, CURDATE()) AS age')
            ->value('age');

        $users = DB::table('users')
            ->select('id', 'name', 'avatar_url', 'avatar_url_thumbnail', 'birthday', 'state', 'country', 'created_at', 'credits',
                DB::raw('TIMESTAMPDIFF(YEAR, birthday, CURDATE()) AS age'),
                DB::raw('RAND() < 0.5 AS "online"'))
            ->whereBetween(DB::raw('TIMESTAMPDIFF(YEAR, birthday, CURDATE())'), [$currentAge + $ageRangeStart, $currentAge + $ageRangeEnd])
            ->where('id', '<>', $currentUserId)
            ->where('gender', Auth::user()->gender_for_search())
            ->paginate($perPage);
        $users->map(function ($user) use ($appUrl) {
            if (!Str::startsWith($user->avatar_url, 'http') and $user->avatar_url) {
                $user->avatar_url = $appUrl . '/' . $user->avatar_url;
            }
            if (!Str::startsWith($user->avatar_url_thumbnail, 'http') and $user->avatar_url) {
                $user->avatar_url_thumbnail = $appUrl . '/' . $user->avatar_url_thumbnail;
            }
            return $user;
        });
        return $users;
    }

    public function search(Request $request)
    {
        $perPage = 8;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }
        $currentUserId = Auth::user()->id;
        $users = User::select()
            ->addSelect(DB::raw('(CASE WHEN EXISTS (SELECT * FROM chat_wink_messages WHERE from_user_id = ' . $currentUserId . ' AND to_user_id = users.id) THEN false ELSE true END) as winkable'))
            ->where('gender', Auth::user()->gender_for_search())
//            ->whereIn('profile_type_id', [ProfileTypeEnum::STANDARD])
            ->when($request->input('age_range_start'), function ($query) use ($request) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= ?', [$request->age_range_start]);
            })
            ->when($request->input('age_range_end'), function ($query) use ($request) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, birthday, CURDATE()) <= ?', [$request->age_range_end]);
            })
            ->when($request->input('country'), function ($query) use ($request) {
                if ($request->country) {
                    $query->where('country', $request->country);
                }
//                $query->where('country', $request->country);
            })
            ->when($request->input('state'), function ($query) use ($request) {
                $query->where('state', $request->state);
            })
            ->when($request->input('prompt_targets'), function ($query) use ($request) {
                $prompt_targets = json_decode($request->input('prompt_targets'));
                if (count($prompt_targets) > 0) {
                    $query->whereHas('prompt_targets', function ($q) use ($prompt_targets) {
                        $q->whereIn('prompt_targets.id', $prompt_targets);
                    });
                }
            })
            ->when($request->input('prompt_finance_states'), function ($query) use ($request) {
                $prompt_finance_states = json_decode($request->input('prompt_finance_states'));
                if (count($prompt_finance_states) > 0) {
                    $query->whereHas('prompt_finance_states', function ($q) use ($prompt_finance_states) {
                        $q->whereIn('prompt_finance_states.id', $prompt_finance_states);
                    });
                }
            });

        if ($request->filter_type) {
            if ($request->filter_type == SearchSortTypeEnum::NEW) {
                $users->where('created_at', '>', Carbon::now()->subDays(30)->format('Y-m-d H:i'))->orderBy('created_at', 'desc');
            } else if ($request->filter_type == SearchSortTypeEnum::NEARBY) {
                $users->where(function (Builder $builder) {
                    $builder->where('state', '=', Auth::user()->state)
                        ->orWhere('country', '=', Auth::user()->country);
                });
            } else if ($request->filter_type == SearchSortTypeEnum::ONLINE) {
                $users->where('online', true);
            }
        }

        if (!$request->isNew || $request->isNew) {
            $users->inRandomOrder();
        }

        return $users->paginate($perPage);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $currentUserId = Auth::user()->id;
        return $currentUserId;
        return DB::table('users')
            ->where('id', '=', $currentUserId)
            ->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public static function changeOnline(){
        User::query()->whereRaw("is_real = 1 AND updated_at < (NOW() - INTERVAL 190 MINUTE)") // 3 часа разницы  в поясе
                     ->whereRaw("is_real = 0 AND updated_at < (NOW() - INTERVAL 195 MINUTE)",[],'OR')// 3 часа разницы  в поясе
                     ->update(['online' => 0]);
    }
}
