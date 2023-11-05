<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnketFavorite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var User */
    private User $user;
    /** @var User */
    private User $favorite;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, User $favoriteUser)
    {
        $this->user = $user;
        $this->favorite = $favoriteUser;
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

        $this->user->addViewedUser($this->favorite);
        $this->user->addFavorite($this->favorite);

        $log->info("[AnketFavorite::handle] Add Favorite from anket: {$this->user->id} to user: {$this->favorite->id}");
    }
}
