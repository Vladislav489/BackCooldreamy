<?php

namespace App\Jobs;

use App\Events\SympathyEvent;
use App\Models\User;
use App\Services\Probability\AnketProbabilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnketWatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var User */
    private User $user;
    /** @var User */
    private User $watchUser;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, User $watchUser)
    {
        $this->user = $user;
        $this->watchUser = $watchUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('/logs/probability/probability.log')
        ]);

        try {
            if (!$this->user->usersWatchedByThisUser()->where('users.id', $this->watchUser->id)->exists()) {

                $this->user->addViewedUser($this->watchUser);

                //SympathyEvent::dispatch($this->watchUser->id, AnketProbabilityService::WATCH, $this->user);

                $log->info("[AnketWatch::handle] Add Watch from anket: {$this->user->id} to user: {$this->watchUser->id}");
            } else {
                $log->info("[AnketWatch::handle] Already Viewd: {$this->user->id} to user: {$this->watchUser->id}");
            }
        } catch (\Exception $exception) {
            $log->info('exeepction' . $exception->getMessage());
        }
    }
}
