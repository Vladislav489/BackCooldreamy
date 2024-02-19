<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Traits\FileStoreTrait;
use App\Traits\VideoStoreTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class FileController extends Controller {
    use FileStoreTrait, VideoStoreTrait;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => [
                'required',
                File::types(['mp4', 'wmv', 'avi', 'webm'])->max(20 * 1024)
            ]
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        try {
            $user = Auth::user();
            return self::store_video_content($user, $request->video, $user->gender);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
