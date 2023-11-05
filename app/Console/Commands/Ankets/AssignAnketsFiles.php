<?php

namespace App\Console\Commands\Ankets;

use App\Enum\Image\ImageStatusEnum;
use App\Models\Image as ImageModal;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class AssignAnketsFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-ankets-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Указываем по команде для юзера все фотки';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//        ImageModal::query()->where('status', ImageStatusEnum::ANKETS)->delete();
        $folders = Storage::disk('public')->directories('images');
        $baseUrl = 'https://media.cooldreamy.com';

        foreach ($folders as $folder) {
            $folderName = basename($folder);
//            if ($folderName < 60000 || $folderName > 60100) {
//                $this->info('Diff');
//                continue;
//            }

//            if ($folderName >= 50) {
//                $this->info('Diff');
//
//                continue;
//            }

            $user = User::where('id', $folderName)->where('gender', 'female')->first();
            if ($user) {
                $this->info('Creating Images for user: '. $user->id);

                $subDirectories = Storage::disk('public')->directories("images/{$folderName}");
                foreach ($subDirectories as $subDirectory) {
                    $subDirectoryName = basename($subDirectory);
                    switch ($subDirectoryName) {
                        case 'ava':
                            $categoryId = 1;
                            break;
                        case '18+':
                            $categoryId = 4;
                            break;
                        case 'content':
                            $categoryId = 3;
                            break;
                        case 'profile':
                            $categoryId = 2;
                            break;
                    }
                    $files = Storage::disk('public')->allFiles("images/{$folderName}/{$subDirectoryName}");
                    foreach ($files as $file) {
                        try {
                            $fileName = basename($file);
                            $fileUrl = "$baseUrl/{$folderName}/{$subDirectoryName}/$fileName";

                            $imageModal = ImageModal::where('user_id', $user->id)->where('image_url', $fileUrl)->first();

                            if (!$imageModal) {
//                            $blurUrl = "$baseUrl/{$folderName}/{$subDirectoryName}/blur_$fileName";
                                $filePath = public_path("storage/images/{$folderName}/{$subDirectoryName}/{$fileName}");
                                $BigThumbnailPath = public_path("storage/images/{$folderName}/{$subDirectoryName}/big_{$fileName}");
                                $BigThumbnailUrl = "$baseUrl/{$folderName}/{$subDirectoryName}/big_$fileName";
                                $ThumbnailPath = public_path("storage/images/{$folderName}/{$subDirectoryName}/thumbnail_{$fileName}");
                                $ThumbnailUrl = "$baseUrl/{$folderName}/{$subDirectoryName}/thumbnail_$fileName";
//                            $blurImage = Image::make($filePath);
//                            $blurImage->resize(null, 350, function ($constraint) {
//                                $constraint->aspectRatio();
//                            });
//                            $width = $blurImage->getWidth();
//                            $height = $blurImage->getHeight();
//                            $blurImage->resize(round($width / 8), round($height / 8));
//                            $blurImage->blur(10);
//                            $blurImage->resize($width, $height);
//                            $blurImage->blur(70);
//                            $blurImage->brightness(15);
//                            $blurImage->save($blurPath);


                                if ($categoryId == 1 || $categoryId == 2) {
                                    $BigThumbnail = Image::make($filePath);
                                    $BigThumbnail->resize(null, 700, function ($constraint) {
                                        $constraint->aspectRatio();
                                    });
                                    $BigThumbnail->save($BigThumbnailPath);

                                    $Thumbnail = Image::make($filePath);
                                    $Thumbnail->resize(null, 350, function ($constraint) {
                                        $constraint->aspectRatio();
                                    });
                                    $Thumbnail->save($ThumbnailPath);
                                }

                                $image = ImageModal::create([
                                    'user_id' => $user->id,
                                    'category_id' => $categoryId,
                                    'image_url' => $fileUrl,
                                    'blur_thumbnail_url' => $fileUrl,
                                    'thumbnail_url' => $categoryId == 1 || $categoryId == 2 ? $ThumbnailUrl : null,
                                    'big_thumbnail_url' => $categoryId == 1 || $categoryId == 2 ?  $BigThumbnailUrl : null,
                                    'status' => ImageStatusEnum::ANKETS,
                                ]);

                                if ($categoryId == 1) {
                                    $user->avatar_url = $fileUrl;
                                    $user->avatar_url_thumbnail  = $ThumbnailUrl;
                                    $user->save();
                                }

                                $this->info('Created Image: '. $image->id);
                            }
                        } catch (\Exception $e) {
                            $this->error('Error ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
