<?php

namespace App\Jobs\Online;

use App\Models\User;
use App\Services\Online\OnlineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetOnlineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var OnlineService|\Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|mixed
     */
    private OnlineService $onlineService;

    /**
     * @var User
     */
    private User $targetUser;

    /**
     * @var int
     */
    private int $action;

    /**
     * Create a new job instance.
     */
    public function __construct(User $targetUser)
    {
        $this->targetUser = $targetUser;
        $this->onlineService = resolve(OnlineService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->onlineService->setOnline($this->targetUser);
    }
}
