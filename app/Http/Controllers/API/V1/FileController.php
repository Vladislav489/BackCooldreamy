<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Traits\FileStoreTrait;
use App\Traits\VideoStoreTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FileController extends Controller {
    use FileStoreTrait, VideoStoreTrait;

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        if ($request->hasFile('file')) {
            return response(json_encode(self::store_file($request->file('file'))));
        }
    }

    public function storeVide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        if ($request->hasFile('file')) {
            return response(json_encode(self::store_video(Auth::user(), $request->file('file'), 0)));
        }
    }
}
