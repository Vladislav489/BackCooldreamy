<?php

namespace App\Console\Commands;

use App\Enum\Image\ImageStatusEnum;
use App\Models\Image;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AssignAnketsAllFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-ankets-new-all-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folders = Storage::disk('public')->directories('images');
        $baseUrl = 'https://media.cooldreamy.com';

        foreach ($folders as $folder) {
            $folderName = basename($folder);

            if ($folderName < 40000 || $folderName > 50000) {
                continue;
            }

            $user = User::where('id', $folderName)->where('gender', 'female')->first();
            if ($user) {
                $this->info('Creating Images for user: '. $user->id);
                $avaFiles = Storage::disk('public')->allFiles("images/{$folderName}/ava");

                if (count($avaFiles)) {
                    foreach ($avaFiles as $file) {
                        $fileName = basename($file);
                        $fileUrl = "$baseUrl/{$folderName}/ava/$fileName";
                        $image = Image::where('user_id', $user->id)->where('image_url', $fileUrl)->first();

                        if (!$image) {
                            $image = Image::create([
                                'user_id' => $user->id,
                                'category_id' => 1,
                                'image_url' => $fileUrl,
                                'blur_thumbnail_url' => $fileUrl,
                                'thumbnail_url' => $fileUrl,
                                'big_thumbnail_url' => $fileUrl,
                                'status' => ImageStatusEnum::ANKETS,
                            ]);

                            $this->info('Created Image: '. $image->id);
                        } else {
                            $this->info('Exists Image: '. $image->id);
                        }

                        $user->avatar_url = $fileUrl;
                        $user->avatar_url_thumbnail  = $fileUrl;
                        $user->save();
                        $this->info('Saved for user: '. $user->id);
                    }
                } else {
                    $this->error('Nothing exists for user: ' .  $user->id);
                }
            }
        }
    }
}
