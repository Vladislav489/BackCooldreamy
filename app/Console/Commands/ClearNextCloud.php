<?php


namespace App\Console\Commands;
use App\Enum\Image\ImageStatusEnum;
use App\Models\Image;
use App\Models\User;
use App\Services\NextCloud\NextCloud;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearNextCloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-next-cloud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */public function handle(){


            $ApiStoreg = new NextCloud('dmitry','Aa@19528091!','nc.cooldreamy.com');
            $folder = $ApiStoreg->getFolder("/media")->getResponse();
            array_shift($folder);
            foreach ($folder as $item ){
                try {
                    $subFolder = $ApiStoreg->getFolder($item)->getResponse();
                    $ava = $ApiStoreg->getFolder($subFolder[1])->getResponse();
                    // dd($item,$subFolder,$ava);
                    if (is_null($ava) || count($ava) == 0) {
                        var_dump("DELETE " . $item);
                        $ApiStoreg->deleteFolder($item);
                    }
                }catch (\Throwable $e){
                    var_dump("DELETE " . $item);
                    $ApiStoreg->deleteFolder($item);
                }
            }
    }
}

