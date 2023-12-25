<?php

namespace App\Console\Commands;

use App\Models\Chat;
use App\Models\User;
use App\Services\NextCloud\NextCloud;
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
        $log = Log::build(['driver' => 'daily', 'path' => storage_path('logs/users/deleted_users.log')]);

        $users = DB::table('users')->where([['gender', '=', 'female'], ['is_real', '=', 1], ['is_operator', '=', 0]])->pluck('id')->toArray();
        $chats = DB::table('chats')->whereIn('first_user_id', $users)->orWhereIn('second_user_id', $users)->pluck('id')->toArray();
        $chat_messages = DB::table('chat_messages')->whereIn('chat_id', $chats)->delete();
        $winks = DB::table('chat_wink_messages')->whereIn('from_user_id', $users)->orWhereIn('to_user_id', $users)->delete();
        $images = DB::table('images')->whereIn('user_id', $users)->delete();
        $users = User::whereIn('id', $users)->delete();
        $chats = Chat::whereIn('id', $chats)->delete();
        $log->info('Users deleted: ' . $users);
        $log->info('Chats deleted: ' . $chats);
        $log->info('Winks deleted: ' . $winks);
        $log->info('Messages deleted: ' . $chat_messages);
        $log->info('Images deleted from table: ' . $images);

        $apiStorage = new NextCloud('dmitry','Aa@19528091!','nc.cooldreamy.com');
        foreach ($users as $user) {
            try {
                $apiStorage->deleteFolder("/media/female/{$user}");
                $log->info('NextCloud folder for user ' . $user . ' is deleted.');
            } catch (\Throwable $e) {
                $log->error('NextCloud error: ' . $e->getMessage());
            }
        }
    }
}
