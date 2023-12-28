<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'utm_source' => 'required|string',
            'utm_medium' => 'required|string',
            'utm_campaign' => 'required|string',
            'utm_term' => 'required|string',
            'utm_advertiser' => 'required|string',
        ]);
        $lead = [];
        if (!is_null($data['utm_source']) && !is_null($data['utm_medium']) && !is_null($data['utm_campaign']) && !is_null($data['utm_term']) && !is_null($data['utm_advertiser'])) {
            $lead = Lead::create($data);
        }

        return response()->json(['data' => $lead]);
    }
}
