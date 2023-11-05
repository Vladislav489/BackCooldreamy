<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function get_countries_validate_user(Request $request)
    {
        $select = ["countries.id" ,"countries.title","countries.created_at","countries.updated_at"];
        $query = Country::query()->select($select);
        if(isset($request->gender))
            $query->whereRaw("users.gender = '{$request->gender}'");
        $query->leftJoin("users",'users.country','=','countries.title')
        ->groupBy($select)->orderBy("countries.id");
        return response($query->get());
    }

    public function get_states_validate_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => ['required', 'integer'],
            'gender' =>['string']
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $select = ["states.id" ,"states.title","states.country_id","states.created_at","states.updated_at"];
        $query = State::query()->select($select);
        $query->where('country_id', $request->country_id);
        if(isset($request->gender))
            $query->whereRaw("users.gender = '{$request->gender}'");
        $query->leftJoin("users",'users.state','=','states.title')
            ->groupBy($select)->orderBy("states.id");
        return response($query->get());
    }

    public function get_countries()
    {
        return response(Country::all());
    }

    public function get_states(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => [
                'required', 'integer'
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        return response(State::where('country_id', $request->country_id)->get());
    }


}
