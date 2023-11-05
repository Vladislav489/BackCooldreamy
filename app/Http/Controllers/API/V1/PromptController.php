<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
    public function get_prompt_targets_table()
    {
        $prompt_targets = (DB::table('prompt_targets')->select('id', 'text', 'gender','icon')->get());
        return json_encode($prompt_targets);
    }

    public function get_prompt_finance_states_table()
    {
        $prompt_finance_states = (DB::table('prompt_finance_states')->select('id', 'text', 'gender','icon')->get());
        return json_encode($prompt_finance_states);
    }

    public function get_all_prompts()
    {
        $prompt_targets = (DB::table('prompt_targets')->select('id', 'text', 'gender','icon')->get());
        $prompt_finance_states = (DB::table('prompt_finance_states')->select('id', 'text', 'gender','icon')->get());
        $prompt_interests = (DB::table('prompt_interests')->select('id', 'text', 'gender','icon')->get());
        $prompt_sources = (DB::table('prompt_sources')->select('id', 'text','icon')->get());
        $prompt_want_kids = (DB::table('prompt_want_kids')->select('id', 'text','icon')->get());
        $prompt_relationships = (DB::table('prompt_relationships')->select('id', 'text', 'gender','icon')->where('gender', Auth::user()->gender)->get());
        $prompt_careers = (DB::table('prompt_careers')->select('id', 'text', 'gender','icon')->get());

        $all_prompts = new \stdClass();
        $all_prompts->prompt_targets = $prompt_targets;
        $all_prompts->prompt_finance_states = $prompt_finance_states;
        $all_prompts->prompt_interests = $prompt_interests;
        $all_prompts->prompt_sources = $prompt_sources;
        $all_prompts->prompt_want_kids = $prompt_want_kids;
        $all_prompts->prompt_relationships = $prompt_relationships;
        $all_prompts->prompt_careers = $prompt_careers;

        return json_encode($all_prompts);

    }
}
