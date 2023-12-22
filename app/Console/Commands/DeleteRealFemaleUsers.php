<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteRealFemaleUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-real-female-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all real female users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = DB::table('users')->whereNotIn('id', [124518, 124519, 124487])->where([['gender', '=', 'female'], ['is_real', '=', 1]])->delete();
        $log = Log::build(['driver' => 'daily', 'path' => storage_path('logs/users/deleted_users.log')]);
        $log->info('Users deleted: ' . $users);
    }
}
