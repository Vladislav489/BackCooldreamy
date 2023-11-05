<?php

namespace App\Console\Commands\Ankets;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GetResolvedAnkets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-resolved-ankets';

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
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/ankets/anket.log')
        ]);

        foreach ($folders as $folder) {
            $folderName = basename($folder);

            $user = User::where('id', $folderName)->where('gender', 'female')->first();
            if ($user) {
                $this->info($user->id);
                $log->info($user->id);
            }
        }
    }
}
