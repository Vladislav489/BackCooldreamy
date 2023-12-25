<?php

namespace App\Jobs;

use App\Http\Controllers\AceController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAce implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public function __construct(private $user, private $sender, private $ace)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = Log::build(['driver' => 'daily', 'path' => storage_path('/logs/probability/probability.log')]);
        AceController::send_chat_message($this->user, $this->sender, $this->ace->text);
        $log->info('[SendAce::message] from ' . $this->sender->id . ' to ' . $this->user->id);
    }
}
