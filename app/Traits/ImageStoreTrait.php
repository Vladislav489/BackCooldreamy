<?php

namespace App\Traits;

use App\Models\User;
use App\Services\NextCloud\NextCloud;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Image;
use Random;

trait ImageStoreTrait
{
    public static function store_image(User $user, $file, $category_id, $gender = null)
    {
        try {
            $links = new \stdClass();
            $filenamewithextension = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);


            //get file extension


            //filename to store
            $filenametostore = md5($filename) . '_' . time() . '.' . $extension;

            //medium thumbnail name
           // $BigThumbnail_filename = md5($filename) . '_big_' . time() . '.' . $extension;
           // $Thumbnail_filename = md5($filename . rand(1000, 9999)) . time() . '.' . $extension;
           // $BlurThumbnail_filename = md5($filename) . '_blur_' . time() . '.' . $extension;

            //Upload File
            if (!is_null($gender)) {
                $urlGender = $gender . '/';
            } else {
                $urlGender = '';
            }

            $categoryList = [1=>'ava',4=>'18+',3=>'content',2=>'profile'];
            $ApiStoreg = new NextCloud();
            $dir =  "/media/". $urlGender . $user->id . '/'.$categoryList[$category_id];
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id);
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id. '/'.$categoryList[$category_id]);

            $ApiStoreg->upoadFile($dir,$file->getContent(),$filenametostore,$extension);
            $links->image_url = env('IMG_URL') . $urlGender . $user->id . '/'.$categoryList[$category_id].'/' . $filenametostore;


            /*
            $file->storeAs('public/images/' . $urlGender . $user->id . '/', $filenametostore);
            $links->image_url = env('IMG_URL') . $urlGender . $user->id . '/' . $filenametostore;

            $file->storeAs('public/images/' . $urlGender . $user->id . '/thumbnail', $BigThumbnail_filename);
            $BigThumbnailPath = public_path('images/' . $urlGender . $user->id . '/thumbnail/' . $BigThumbnail_filename);
            $file->storeAs('public/images/' . $urlGender . $user->id . '/thumbnail', $Thumbnail_filename);
            $ThumbnailPath = public_path('images/'. $urlGender . $user->id . '/thumbnail/' . $Thumbnail_filename);
            $file->storeAs('public/images/' . $urlGender . $user->id . '/thumbnail', $BlurThumbnail_filename);
            $BlurThumbnailPath = public_path('storage/images/'.$urlGender . $user->id . '/thumbnail/' . $BlurThumbnail_filename);

            $BigThumbnail = Image::make($BigThumbnailPath);
            $BigThumbnail->resize(null, 700, function ($constraint) {
                $constraint->aspectRatio();
            });
            $BigThumbnail->save($BigThumbnailPath);

            $Thumbnail = Image::make($ThumbnailPath);
            $Thumbnail->resize(null, 350, function ($constraint) {
                $constraint->aspectRatio();
            });
            $Thumbnail->save($ThumbnailPath);

            $BlurThumbnail = Image::make($BlurThumbnailPath);
            $BlurThumbnail->resize(null, 350, function ($constraint) {
                $constraint->aspectRatio();
            });

            $width = $BlurThumbnail->getWidth();
            $height = $BlurThumbnail->getHeight();
            $BlurThumbnail->resize(round($width / 8), round($height / 8));
            $BlurThumbnail->blur(10);
            $BlurThumbnail->resize($width, $height);
            $BlurThumbnail->blur(70);
            $BlurThumbnail->brightness(15);
            $BlurThumbnail->save($BlurThumbnailPath);

            $links->big_thumbnail_url = env('IMG_URL') . $urlGender. $user->id . "/thumbnail/" . $BigThumbnail_filename;
            $links->thumbnail_url = env('IMG_URL') . $urlGender. $user->id . "/thumbnail/" . $Thumbnail_filename;
            $links->blur_thumbnail_url = env('IMG_URL') . $urlGender. $user->id . "/thumbnail/" . $BlurThumbnail_filename;
            */
            $image = new \App\Models\Image();
            $image->user_id = $user->id;
            $image->image_url = $links->image_url;
            $image->thumbnail_url = $links->image_url;
            $image->big_thumbnail_url =  $links->image_url;
            $image->blur_thumbnail_url =  $links->image_url;
            $image->category_id = $category_id;
            $image->save();

            if ($category_id == 1) {
                $user->avatar_url = 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $image->image_url);
                $user->avatar_url_thumbnail =  'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '',  $links->image_url);
                $user->avatar_url_big_thumbnail =  'https://media.cooldreamy.com/'  . str_replace('https://media.cooldreamy.com/', '',  $links->image_url);
                $user->save();
            }
            return response($image);
        } catch (\Exception $e) {
            return response()->json((['message' => $e->getMessage(),'l'=>$e->getLine(),'f'=>$e->getFile()]), 500);
        }
    }


    public static function store_image_content_base_64(User $user,$contentBase64, $category_id, $gender = null){
        try {
            $links = new \stdClass();
            $image_parts = explode(";base64,", $contentBase64);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $uniqid = uniqid();
            $filenametostore = md5($uniqid) . '_' . time() . '.' . $image_type;
            if (!is_null($gender)) {
                $urlGender = $gender . '/';
            } else {
                $urlGender = '';
            }
            $categoryList = [1=>'ava',4=>'18+',3=>'content',2=>'profile'];
            $ApiStoreg = new NextCloud();
            $dir =  "/media/". $urlGender . $user->id . '/'.$categoryList[$category_id];
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id);
            $ApiStoreg->createFolder("/media/". $urlGender . $user->id. '/'.$categoryList[$category_id]);
            $ApiStoreg->upoadFile($dir,$image_base64,$filenametostore,$image_type);
            $links->image_url = env('IMG_URL') . $urlGender . $user->id . '/'.$categoryList[$category_id].'/' . $filenametostore;

            $image = new \App\Models\Image();
            $image->user_id = $user->id;
            $image->image_url = 'https://media.cooldreamy.com/' . $links->image_url;
            $image->thumbnail_url = 'https://media.cooldreamy.com/' . $links->image_url;
            $image->big_thumbnail_url = 'https://media.cooldreamy.com/' . $links->image_url;
            $image->blur_thumbnail_url = 'https://media.cooldreamy.com/' . $links->image_url;
            $image->category_id = $category_id;
            $image->save();

            if ($category_id == 1) {
                $user->avatar_url = 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $image->image_url);
                $user->avatar_url_thumbnail =  'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '',  $links->image_url);
                $user->avatar_url_big_thumbnail =  'https://media.cooldreamy.com/'  . str_replace('https://media.cooldreamy.com/', '',  $links->image_url);
                $user->save();
            }
            return response($image);
        } catch (\Exception $e) {
            var_dump(['message' => $e->getMessage(),'l'=>$e->getLine(),'f'=>$e->getFile()]);
            return response()->json((['message' => $e->getMessage(),'l'=>$e->getLine(),'f'=>$e->getFile()]), 500);
        }
    }

    public static function store_image_content(User $user, $name,$subFolder,$content, $category_id, $gender = null) {
        try {

            $name =  pathinfo(urldecode($name));
            $links = new \stdClass();

            $extension = $name['extension'];
            //get filename without extension
            $filename = $name['filename'];

            //filename to store
            $filenametostore = md5($filename).'_'.$filename. '_' . time() . '.' . $extension;

            //medium thumbnail name
            $BigThumbnail_filename = md5($filename) . '_big_' . time() . '.' . $extension;
            $Thumbnail_filename = md5($filename . rand(1000, 9999)) . time() . '.' . $extension;
            $BlurThumbnail_filename = md5($filename) . '_blur_' . time() . '.' . $extension;

            //Upload File
            if (!is_null($gender)) {
                $urlGender = $gender . '/';
                $gender = '/' . $gender;
            } else {
                $urlGender = '';
            }

            $pathFile['original'] ='images/' . $urlGender . $user->id . '/'.$subFolder."/";
            $pathFile['Thumbnail'] = 'images/' . $urlGender . $user->id ."/".$subFolder.'/thumbnail/';



            if(!\Illuminate\Support\Facades\File::isDirectory( storage_path("app/public/".$pathFile['original']))){
                \Illuminate\Support\Facades\File::makeDirectory( storage_path("app/public/".$pathFile['original']),0755,true,true);
            }
            if(!\Illuminate\Support\Facades\File::isDirectory(storage_path("app/public/".$pathFile['Thumbnail']))){
                \Illuminate\Support\Facades\File::makeDirectory(storage_path("app/public/".$pathFile['Thumbnail']),0755,true,true);
            }





            //$file = new File($content);
            //$file->

            $img = Image::make($content);
            $img->resize(null, 700, function ($constraint) {$constraint->aspectRatio();});
            $img->save(storage_path("app/public/".$pathFile['Thumbnail'].$BigThumbnail_filename));
            $img->destroy();

            $img->resize(null, 350, function ($constraint) {$constraint->aspectRatio();});
            $img->save(storage_path("app/public/".$pathFile['Thumbnail'].$Thumbnail_filename));
            $img->destroy();

            if($category_id ==4) {
                $img->resize(null, 350, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $width = $img->getWidth();
                $height = $img->getHeight();
                $img->resize(round($width / 8), round($height / 8));
                $img->blur(10);
                $img->resize($width, $height);
                $img->blur(70);
                $img->brightness(15);
                $img->save(storage_path("app/public/" . $pathFile['Thumbnail'] . $BlurThumbnail_filename));
                $img->destroy();
            }

            $origfile = fopen(storage_path("app/public/" .$pathFile['original']."111".$filenametostore),"w");
            fwrite($origfile, $content);
            fclose($origfile);

           // Storage::put( "public/".$pathFile['original'].$filenametostore,$content);
            $links->image_url = env('IMG_URL') .$urlGender . $user->id .'/'.$subFolder."/".$filenametostore;
            $links->big_thumbnail_url = env('IMG_URL') . $pathFile['Thumbnail'] . $BigThumbnail_filename;
            $links->thumbnail_url = env('IMG_URL') . $pathFile['Thumbnail'] . $Thumbnail_filename;
            $links->blur_thumbnail_url = env('IMG_URL') .$pathFile['Thumbnail'] . $BlurThumbnail_filename;

            $image = new \App\Models\Image();
            $image->user_id = $user->id;
            $image->image_url = $links->image_url;
            $image->thumbnail_url = $links->thumbnail_url;
            $image->big_thumbnail_url = $links->big_thumbnail_url;
            $image->blur_thumbnail_url = $links->blur_thumbnail_url;
            $image->category_id = $category_id;
            $image->save();

            if ($category_id == 1) {

                $user->avatar_url = 'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $image->image_url);
                $user->avatar_url_thumbnail =  'https://media.cooldreamy.com/' . str_replace('https://media.cooldreamy.com/', '', $image->thumbnail_url);
                $user->avatar_url_big_thumbnail =  'https://media.cooldreamy.com/'  . str_replace('https://media.cooldreamy.com/', '', $image->big_thumbnail_url);
                $user->save();
            }
            return response($image);
        } catch (\Throwable $e) {
            dd($e->getMessage(),$e->getLine(),$e->getFile(),$e->getTrace());
        }

        return true;
    }

    public static function resizeCropImage($file, $rWidth = null, $rHeight = null, $cWidth = null, $cHeight = null)
    {
        $image = Image::make($file);
        $image->resize($rWidth, $rHeight, function ($constraint) {
            $constraint->aspectRatio();
        })->crop($cWidth, $cHeight);
        $tmpName = now()->timestamp;
        $image->save(storage_path('app/media/tmp/' . $tmpName));
        $file = $image->getEncoded();
        $image->destroy();
        \File::delete(storage_path('app/media/tmp/' . $tmpName));
        return $file;
    }

//    public function createThumbnail($path, $width, $height) {
//        $img = Image::make($path)->resize($width, $height)->save($path);
//        return $path;
//    }
}
