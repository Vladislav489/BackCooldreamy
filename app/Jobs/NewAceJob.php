<?php

namespace App\Jobs;

use App\Http\Controllers\AceController;
use App\Models\Ace;
use App\Models\AceLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\Translation\t;

class NewAceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $target_user;

    public function __construct(User $target_user)
    {
        $this->target_user = $target_user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/ace/ace.log')
        ]);

        $log->info('Initializing aces for user: ' . $this->target_user->id);

        try {
            $user = $this->target_user;
            //todo its hardcode
            if ($this->target_user->ace_limit->ace_limit <= 0 and $this->target_user->ace_limit->random_second_from == 30) {
                $this->target_user->ace_limit->ace_limit = 10;
                $this->target_user->ace_limit->random_second_from = 60;
                $this->target_user->ace_limit->random_second_to = 120;
                $this->target_user->ace_limit->current_random_second = 0;
                $this->target_user->ace_limit->save();
            }
            if ($this->target_user->ace_limit->ace_limit <= 0 and $this->target_user->ace_limit->random_second_from == 60
                and $this->target_user->ace_limit->random_second_to == 120) {
                $this->target_user->ace_limit->ace_limit = 10;
                $this->target_user->ace_limit->random_second_from = 60;
                $this->target_user->ace_limit->random_second_to = 720;
                $this->target_user->ace_limit->current_random_second = 0;
                $this->target_user->ace_limit->save();
            }
            if ($this->target_user->ace_limit->ace_limit <= 0 and $this->target_user->ace_limit->random_second_to == 720) {
                $this->target_user->ace_limit->ace_limit = 10;
                $this->target_user->ace_limit->random_second_from = 3600;
                $this->target_user->ace_limit->random_second_to = 7200;
                $this->target_user->ace_limit->current_random_second = 0;
                $this->target_user->ace_limit->is_regular = true;
                $this->target_user->ace_limit->save();
            }

            if ($this->target_user->ace_limit->ace_limit > 0) {
                $this->target_user->ace_limit->ace_limit = $this->target_user->ace_limit->ace_limit - 1;
                $current_random_second = $this->target_user->ace_limit->current_random_second + rand($this->target_user->ace_limit->random_second_from, $this->target_user->ace_limit->random_second_to);
                $this->target_user->ace_limit->current_random_second = $current_random_second;
                $this->target_user->ace_limit->save();
                $girl = AceController::get_girl_for_ace($this->target_user);
                $ace = null;
                $log->info($girl);

                if ($girl) {
                    $ace = AceController::getAce($this->target_user, $girl);

                } else {
                    $log->info('Girl not found...');
                }

                if ($girl && $ace) {
                    $chat_message = AceController::send_chat_message($this->target_user, $girl, $ace->text);
                    $chat_id = $chat_message->chat->id;
                    $ace_log = AceLog::create(
                        [
                            'from_user_id' => $girl->id,
                            'to_user_id' => $this->target_user->id,
                            'chat_id' => $chat_id,
                            'ace_id' => $ace->id,
                            'chat_message_id' => $chat_message->id
                        ]
                    );

                    $girl->addViewedUser($this->target_user);

                    if (rand(1, 20) === 1) {
                        $girl->addFavorite($this->target_user);
                    }

                    if (rand(1, 100) <= 15) {
                        $girl->addLike($this->target_user);
                    }
                    $user->ace_object_limit = $user->ace_object_limit + 1;
                    $user->save();

                    if ($user->ace_object_limit <= 1) {
                        $time = rand(60, 120);
                    } else if ($user->ace_object_limit <= 2) {
                        $time = rand(120, 240);
                    } else if ($user->ace_object_limit <= 4) {
                        $time = rand(360, 720);
                    } else if ($user->ace_object_limit <= 9) {
                        $time = rand(3600, 7200);
                    } else {
                        $time = rand(21600, 64800);
                    }

                    $log->info('Sended new event for time: ' . $time);
                    $log->info('Sending Chat User Ace Message');

                    NewAceJob::dispatch($this->target_user)->onQueue('queue_ace')->delay($time);
                }
            }
        } catch (\Throwable $e) {
            var_dump($e->getMessage());
            logger('ERror  ACE: ' . $e->getMessage());
        }
    }
}
