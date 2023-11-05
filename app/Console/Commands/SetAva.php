<?php


namespace App\Console\Commands;


use App\Models\Image;
use App\Models\Import\CronImportUser;
use App\Models\User;
use Illuminate\Console\Command;

class SetAva extends Command
{

    protected $signature = 'set_ava:user';

    protected $description = 'run Import User';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(){
        try {
            $userObj = User::query()->whereNull('avatar_url')->get();
            foreach ($userObj as $user) {
                $contentFoto = Image::query()->where('user_id', '=', $user->id)->where('category_id', '=', 3)->first();
                var_dump($contentFoto);
                if (!is_null($contentFoto)) {
                    $user->where('user_id', '=', $user->id)->update([
                        'avatar_url' => $contentFoto->image_url,
                        'avatar_url_thumbnail' => $contentFoto->image_url,
                    ]);
                }
            }
        }catch (\Throwable $e){
            var_dump($e->getMessage(),$e->getFile(),$e->getLine());
        }
    }
}
