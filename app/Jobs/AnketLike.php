<?php

namespace App\Jobs;

use App\Events\SympathyEvent;
use App\Mail\LikeUserMail;
use App\Models\User;
use App\Services\Probability\AnketProbabilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnketLike implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var User */
    private User $user;
    /** @var User */
    private User $likeUser;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, User $likeUser)
    {
        $this->user = $user;
        $this->likeUser = $likeUser;
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

        if (!$this->likeUser->liked_me()->where('users.id', $this->user->id)->exists()) {
            $this->user->addViewedUser($this->likeUser);
            $this->user->addLike($this->likeUser);
           // SympathyEvent::dispatch($this->likeUser->id, AnketProbabilityService::LIKE, $this->user);
           // SympathyEvent::dispatch($this->likeUser->id, AnketProbabilityService::WATCH, $this->user);

            $log->info("[AnketLike::handle] Add Like from anket: {$this->user->id} to user: {$this->likeUser->id}");
            if (!$this->likeUser->online && $this->likeUser->is_real == 1 && $this->likeUser->is_email_verified == 1){
                \Mail::to($this->likeUser->email)->send(new LikeUserMail($this->likeUser, $this->user));
            }
        } else {
            $log->info("[AnketLike::handle] Already liked: {$this->user->id} to user: {$this->likeUser->id}");
        }
    }
}
