<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Random;

trait VideoStoreTrait
{
    public static function store_video(User $user, $file, $category_id)
    {
        try {
            $links = new \stdClass();

            $filenamewithextension = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);


            //filename to store
            $filenametostore = md5($filename) . '_' . time() . '.' . $extension;

            //Upload File
            $file->storeAs('public/images/' . $user->id . '/', $filenametostore);
            $links->video_url = env('IMG_URL') . $user->id . '/' . $filenametostore;

            $video = new \App\Models\Video();
            $video->user_id = $user->id;
            $video->video_url = $links->video_url;
            $video->category_id = $category_id;
            $video->save();

            return response($video);
        } catch (\Exception $e) {
            return response()->json((['message' => $e->getMessage()]), 500);
        }
    }
}
