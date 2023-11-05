<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Array_;
use Yajra\DataTables\DataTables;

class StatisticsController extends Controller
{
    public function messageCount()
    {
        $total = ChatMessage::all()->count();

        $female = User::where("gender",'=','female')->count();
        $male = User::where("gender",'=','male')->count();
        $totalGender = $female + $male;
        $percent_female = round(100/($totalGender/$female),2);
        $percent_male = round(100/($totalGender/$male),2);

        $female_ava = User::where("gender",'=','female')->where("avatar_url",'!=','')->count();
        $male_ava = User::where("gender",'=','male')->where("avatar_url",'!=','')->count();

        $percent_female_ava = round(100/($female/$female_ava),2);
        $percent_male_ava = round(100/($male/$male_ava),2);

        return view('admin.statistics.message-count', compact('total','female','percent_female','male','percent_male','percent_female_ava','percent_male_ava'));
    }

    public function messageCountByUsers(Request $request)
    {
        $items = DB::table("users")
            ->select("users.name as user")
            ->addSelect(DB::raw('count(chat_messages.id) as count'))
            ->leftjoin('chat_messages', 'users.id', '=', 'chat_messages.sender_user_id')
            ->orderBy('user')
            ->groupBy('user');
        if($request->date_min) $items->where('chat_messages.created_at','>=',date("Y-m-d 00:00:00",strtotime($request->date_min)));
        if($request->date_max) $items->where('chat_messages.created_at','<=',date("Y-m-d 00:00:00",strtotime($request->date_max)));
        $items->get();

        /*$users_list = User::all();
        $items = [];
        foreach ($users_list as $user) {

            $countResult = ChatMessage::where('sender_user_id', $user->id);
            if($request->date_min) $countResult->where('created_at','>=',date("Y-m-d 00:00:00",strtotime($request->date_min)));
            if($request->date_max) $countResult->where('created_at','<=',date("Y-m-d 00:00:00",strtotime($request->date_max)));
            $countMessages = $countResult->count();

            if($countMessages)
            {
                $items[$user->id]['user'] = $user->name . "(".$request->date_min." - ".$request->date_max.")";
                $items[$user->id]['count'] = $countMessages;
            }
        }*/
        return DataTables::of($items)->make();
    }

    public function ageCountByUsers(Request $request)
    {
        $users = User::where("gender","male")
            ->get();

        $ages = [];
        foreach($users as $user)
        {
            $birthday_timestamp = strtotime($user->birthday . " 12:00:00");
            $age = date('Y') - date('Y', $birthday_timestamp);
            if (date('md', $birthday_timestamp) > date('md')) {
                $age--;
            }

            if(array_key_exists($age,$ages))
            {
                $ages[$age]["count"]++;
            }
            else
            {
                $ages[$age] = [
                    "age" => $age,
                    "count" => 1
                ];
            }
        }

        return DataTables::of($ages)->make();
    }

    public function getUsersList(Request $request)
    {
        $users = DB::table("users")
            ->select("name","email","birthday","gender","country","state","created_at")
            ->orderBy('name');
        if($request->date_min) $users->where('created_at','>=',date("Y-m-d 00:00:00",strtotime($request->date_min)));
        if($request->date_max) $users->where('created_at','<=',date("Y-m-d 00:00:00",strtotime($request->date_max)));
        if($request->city) $users->where('state','=',$request->city);
        $users->get();

        return DataTables::of($users)->make();
    }

    public function getCityList(Request $request)
    {
        $cities = DB::table("users")
            ->select("state")
            ->distinct()
            ->where("state","!=","")
            ->orderBy('state')
            ->get();

        return $cities;
    }

    public function getCountriesList(Request $request)
    {
        $cities = DB::table("users")
            ->select("country")
            ->distinct()
            ->where("country","!=","")
            ->orderBy('country')
            ->get();

        return $cities;
    }
}
