<?php


namespace App\Services\NextCloud;
use App\Models\User;
use Image;

class SaveImage {
    public  function  saveImgUsers(User $user, $nameFile,$category_id, $gender = null){
        $fileOInfo = pathinfo(urldecode($nameFile));
        $extension = (isset($fileOInfo['extension']))?$fileOInfo['extension']:"";
        if( in_array(strtolower($extension), array('png', 'jpg', 'gif'))) {
            $links = new \stdClass();
            $links->image_url = str_replace('/media/', env('IMG_URL'),$nameFile);
            $links->big_thumbnail_url = $links->image_url; // env('IMG_URL') .$urlGender . $user->id .'/'.$subFolder."/".$filenametostore;
            $links->thumbnail_url = $links->image_url; //env('IMG_URL') .$urlGender . $user->id .'/'.$subFolder."/".$filenametostore;
            $links->blur_thumbnail_url = $links->image_url;  ///env('IMG_URL') .$urlGender . $user->id .'/'.$subFolder."/".$filenametostore;
                $image = \App\Models\Image::query()->where('image_url', $links->image_url)->get()->first();
                if (is_null($image)) {
                    $image = new \App\Models\Image();
                    $image->user_id = $user->id;
                    $image->image_url = $links->image_url;
                    $image->thumbnail_url = $links->thumbnail_url;
                    $image->big_thumbnail_url = $links->big_thumbnail_url;
                    $image->blur_thumbnail_url = $links->blur_thumbnail_url;
                    $image->category_id = $category_id;
                    $image->save();
                }else{
                    $image->user_id = $user->id;
                    $image->image_url = $links->image_url;
                    $image->thumbnail_url = $links->thumbnail_url;
                    $image->big_thumbnail_url = $links->big_thumbnail_url;
                    $image->blur_thumbnail_url = $links->blur_thumbnail_url;
                    $image->category_id = $category_id;
                    $image->save();
                }

                if ($category_id == 1) {
                    $user->avatar_url = $image->image_url;
                    $user->avatar_url_thumbnail = $image->thumbnail_url;
                    $user->avatar_url_big_thumbnail = $image->big_thumbnail_url;
                    $user->save();
                }
            return $image;
        }
        return false;
    }
}
