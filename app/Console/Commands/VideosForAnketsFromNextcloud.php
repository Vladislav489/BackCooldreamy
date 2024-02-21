<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Video;
use App\Services\NextCloud\NextCloud;
use Illuminate\Console\Command;

class VideosForAnketsFromNextcloud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-videos-to-ankets';

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
        $storage = new NextCloud();
        $ankets = User::where('is_real', 0)->get('id');
        foreach ($ankets as $anket) {
            $storage->getFolder('/media/' . $anket->id . '/video/');
            $results = $storage->getResponse();
            if (strpos($results, '<s:exception>')) {
                continue;
            }
            if (!empty($results)) {
                if (!is_array($results)) {
                    $results = array($results);
                }
                foreach ($results as $result) {
                    Video::create(['user_id' => $anket->id, 'video_url' => 'https://media.cooldreamy.com/' . str_replace('/media/', '', $result)]);
                }
            }
        }
    }
}
