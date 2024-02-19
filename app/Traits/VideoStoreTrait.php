<?php

namespace App\Traits;

use App\Models\User;
use App\Services\NextCloud\NextCloud;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public static function store_video_content_base_64(User $user,$contentBase64, $gender = null){
        try {
            $links = new \stdClass();
            $video_parts = explode(";base64,", $contentBase64);
            $video_type_aux = explode("video/", $video_parts[0]);
            $video_type = $video_type_aux[1];
            $video_base64 = base64_decode($video_parts[1]);
            $uniqid = uniqid();
            $filenametostore = md5($uniqid) . '_' . time() . '.' . $video_type;
            if (!is_null($gender)) {
                $urlGender = $gender . '/';
            } else {
                $urlGender = '';
            }
            $ApiStoreg = new NextCloud();
            $dir =  "/media/". $urlGender . $user->id . '/video';
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id);
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id. '/video');
            $ApiStoreg->upoadFile($dir,$video_base64,$filenametostore,$video_type);
            $links->video_url = env('IMG_URL') . $urlGender . $user->id . '/'.$categoryList[$category_id].'/' . $filenametostore;

            $video = new \App\Models\Video();
            $video->user_id = $user->id;
            $video->video_url = 'https://media.cooldreamy.com/' . $links->video_url;
            $video->thumbnail_url = 'https://media.cooldreamy.com/' . $links->video_url;
            $video->big_thumbnail_url = 'https://media.cooldreamy.com/' . $links->video_url;
            $video->blur_thumbnail_url = 'https://media.cooldreamy.com/' . $links->video_url;
            $video->category_id = $category_id;
            $video->save();

            if ($category_id == 1) {
                $user->avatar_url = 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $video->video_url);
                $user->avatar_url_thumbnail =  'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '',  $links->video_url);
                $user->avatar_url_big_thumbnail =  'https://media.cooldreamy.com/'  . str_replace('https://media.cooldreamy.com/', '',  $links->video_url);
                $user->save();
            }
            return response($video);
        } catch (\Exception $e) {
            var_dump(['message' => $e->getMessage(),'l'=>$e->getLine(),'f'=>$e->getFile()]);
            return response()->json((['message' => $e->getMessage(),'l'=>$e->getLine(),'f'=>$e->getFile()]), 500);
        }
    }

    public static function store_video_content(User $user,$content, $gender = null){
        try {
            $links = new \stdClass();
            $fileStream = fopen($content, 'r');
            $video_file = fread($fileStream, filesize($content));
            $video_type = $content->getClientOriginalExtension();
            $uniqid = uniqid();
            $filenametostore = md5($uniqid) . '_' . time() . '.' . $video_type;
            if (!is_null($gender)) {
                $urlGender = $gender . '/';
            } else {
                $urlGender = '';
            }
            $ApiStoreg = new NextCloud();
            $dir =  "/media/". $urlGender . $user->id . '/video';
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id);
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id. '/video');
            $ApiStoreg->upoadFile($dir,$video_file,$filenametostore,$video_type);
            $links->video_url = $urlGender . $user->id . '/video/' . $filenametostore;

            $video = new \App\Models\Video();
            $video->user_id = $user->id;
            $video->video_url = 'https://media.cooldreamy.com/' . $links->video_url;
            $video->save();

            return response($video);
        } catch (\Exception $e) {
            var_dump(['message' => $e->getMessage(),'l'=>$e->getLine(),'f'=>$e->getFile()]);
            return response()->json((['message' => $e->getMessage(),'l'=>$e->getLine(),'f'=>$e->getFile()]), 500);
        }
    }
}
