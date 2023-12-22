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
