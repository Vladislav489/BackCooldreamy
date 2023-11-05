<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResponsibleLikeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $target_user;
    private $girl_user;

    public function __construct(User $target_user, User $girl_user)
    {
        $this->target_user = $target_user;
        $this->girl_user = $girl_user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->girl_user->addLike($this->target_user);

    }
}
