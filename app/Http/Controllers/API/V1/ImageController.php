<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Controller;
use App\Models\CsvUser;
use App\Models\Image;
use App\Models\User;
use App\Traits\ImageStoreTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PHPUnit\Framework\MockObject\Exception;
use Hash;
use Illuminate\Support\Facades\File;

//use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class ImageController extends Controller
{
    use ImageStoreTrait;

    public function store(Request $request) {
        logger(json_encode($request));
        logger(json_encode($_FILES));
        logger(json_encode($request->header()));

        $validator = Validator::make($request->all(), [
            'image' => ['required'],
            'category_id' => ['required', Rule::exists('image_categories', 'id'),]
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        if ($request->hasFile('image')) {
            $user = Auth::user();
            return self::store_image(Auth::user(), $request->file('image'), $request->category_id, $user ? $user->gender : null);
        }
    }

    public function deleteImage(Request $request) {
        $validator = Validator::make($request->all(), [
            'image_id' => ['required'],
            'user_id' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        return Image::find($request->image_id)->deleteWithImage($request->user_id);
    }

    public function storeImages(Request $request) {
        $validator = Validator::make($request->all(), [
            'count_image' => ['required'],
            'user_id' => ['required'],
            'category_id' => ['required', Rule::exists('image_categories', 'id'),]
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $user =  User::query()->where('id','=',$request->user_id)->get()->first();
        $file = [];
        for ($i = 0; $i < $request->count_image;$i++){
            if($request->exists('image' . $i)) {
                $file[] = $request->file('image' . $i);
            }
        }
        if(is_array($file)){
            $listResponsImage = [];
            foreach ($file as $image){

                $listResponsImage[]  = self::store_image($user ,$image, $request->category_id,  null);
            }
            return response()->json($listResponsImage);
        } else {
            return response()->json([self::store_image($user ,$request->file('image'), $request->category_id,  null)]);
        }
    }


    public static function store_from_path(User $user, $path, $category_id){
        try {
            $fileContents = File::get($path);
            // Создаем временный файл, используя содержимое файла:
            $tmpFile = tmpfile();
            fwrite($tmpFile, $fileContents);
            $metaData = stream_get_meta_data($tmpFile);
// Получаем имя и размер файла:
            $fileName = basename($path);
            $fileSize = $metaData['uri'] ? filesize($metaData['uri']) : 0;

// Создаем экземпляр UploadedFile:
            $uploadedFile = new UploadedFile(
                $metaData['uri'],
                $fileName,
                null,
                $fileSize,
                UPLOAD_ERR_OK,
                true // Устанавливаем удаление временного файла после выполнения запроса
            );

            return self::store_image($user, $uploadedFile, $category_id);
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public static function store_from_folder(User $user, $folder_path, $category_id)
    {
        $full_path = storage_path($folder_path);
        $files = File::files($full_path);

        foreach ($files as $file) {
            try {
                $path = $file->getPathname();
                self::store_from_path($user, $path, $category_id);
            } catch (\Exception $e) {
                echo $e;
            }
        }
        return 'All images in folder have been processed.';
    }

    public static function createUserFromCSV(CsvUser $csvUser)
    {
        try {
            $user = new User();
            $user->password = Hash::make($csvUser->password);
            $user->name = $csvUser->name;
            $user->email = $csvUser->email;
            $user->state = $csvUser->state;
            $user->country = $csvUser->country;
            $user->birthday = $csvUser->birthday;
            $user->about_self = $csvUser->about_self;
            $user->gender = 'female';
            $user->profile_type_id = $csvUser->profile_type_id;
            $user->is_real = false;
            $user->save();
//            echo json_encode($csvUser->profile_type->name);
            $prompt_targets = AdminController::generateTargets('prompt_targets', $csvUser->profile_type->name, 1, 3);
            $user->prompt_targets()->sync($prompt_targets);

            $prompt_interest = AdminController::generateTargets('prompt_interests', $csvUser->profile_type->name, 3, 5);
            $user->prompt_interests()->sync($prompt_interest);

            $prompt_finance_states = AdminController::generateTargets('prompt_finance_states', $csvUser->profile_type->name, 1, 1);
            $user->prompt_finance_states()->sync($prompt_finance_states);

            $prompt_sources = AdminController::generateTargets('prompt_sources', $csvUser->profile_type->name, 1, 1);
            $user->prompt_sources()->sync($prompt_sources);

            $prompt_want_kids = AdminController::generateTargets('prompt_want_kids', $csvUser->profile_type->name, 1, 1);
            $user->prompt_want_kids()->sync($prompt_want_kids);

            $prompt_relationships = AdminController::generateTargets('prompt_relationships', $csvUser->profile_type->name, 1, 1);
            $user->prompt_relationships()->sync($prompt_relationships);

            $prompt_careers = AdminController::generateTargets('prompt_careers', $csvUser->profile_type->name, 1, 1);
            $user->prompt_careers()->sync($prompt_careers);

            $user->save();
            $csvUser->is_sync = true;
            $csvUser->save();
            return $user;
        } catch (Exception $e) {
            echo json_encode($e->getMessage());
            return false;
        }
    }

    public static function storeImagesForCsvUser(User $user, CsvUser $csvUser)
    {
        $avatarCategoryId = 1;
        $publicCategoryId = 2;
        $privateCategoryId = 3;
        $intimCategoryId = 4;

//        $basePath = "app/public/uploads/" . $user->country . "/" . $user->state . "/" . $user->email;
        $basePath = "app/public/uploads/" . substr($user->email, 0, strpos($user->email, "@"));

        $avatarPath = $basePath . "/ava";
        $publicPath = $basePath . "/profile";
        $privatePath = $basePath . "/content";
        $intimPath = $basePath . "/18+";

        try {
            self::store_from_folder($user, $avatarPath, $avatarCategoryId);
        } catch (\Exception $exception) {
            echo $exception;
        }
        try {
            self::store_from_folder($user, $publicPath, $publicCategoryId);
        } catch (\Exception $exception) {
        }
        try {
            self::store_from_folder($user, $privatePath, $privateCategoryId);
        } catch (\Exception $exception) {
        }
        try {
            self::store_from_folder($user, $intimPath, $intimCategoryId);
        } catch (\Exception $exception) {
        }

        File::deleteDirectory(storage_path($basePath), true);
        rmdir(storage_path($basePath));
    }

    public static function workerForStoreCSVUsers(){
        $csvUsers = CsvUser::where('is_sync', false)->limit(15)->get();
        foreach ($csvUsers as $csvUser) {
            if (!isset($csvUser)) {
                return "not aviable users";
            }
            $user = false;
            try {
                $user = self::createUserFromCSV($csvUser);
            } catch (\Exception $exception) {
                echo json_encode($exception->getMessage());
            }
            if ($user) {
                try {
                    self::storeImagesForCsvUser($user, $csvUser);
                } catch (\Exception $exception) {
                }
            }
        }
    }

}
