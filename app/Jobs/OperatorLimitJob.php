<?php

namespace App\Jobs;

use App\Models\OperatorChatLimit;
use App\Models\OperatorLimitGirlTypeAssignment;
use App\Models\OperatorLimitTimeAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class OperatorLimitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var User */
    public User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        Log::info('[OperatorLimitJob::construct] Generate Operator Limit for user: ' . $user->id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/limits/limits.log')
        ]);

        $log->info('[OperatorLimitJob::handle] OperatorLimitJob');

        $user = $this->user;


        $chance1 = 0.4;
        $chance2 = 0.3;

        $randomNumber = mt_rand() / mt_getrandmax(); // Случайное число от 0 до 1
        $profileType = OperatorLimitGirlTypeAssignment::query()->where('chance', '<=', $randomNumber)->orderByDesc('created_at')->first();

        if ($profileType) {
            $profileType = $profileType->profile_type_id;
        } else {
            if ($randomNumber < $chance1) {
                $profileType = 1;
            } elseif ($randomNumber < $chance1 + $chance2) {
                $profileType = 2;
            } else {
                $profileType = 3;
            }
        }

        $birthday = $user->birthday;

        $startDate = Carbon::parse($birthday)->subYears(20)->format('Y-m-d');
        $endDate = Carbon::parse($birthday)->addYears(15)->format('Y-m-d');

        $girl = User::query()->where('profile_type_id', $profileType)
            ->where('gender', 'female')
            ->where('is_real', false)
            ->whereDate('birthday', '>=', $startDate)
            ->whereDate('birthday', '<=', $endDate)
            ->whereDoesntHave('operatorLimits', function ($query) use  ($user) {
                $query->where('man_id', $user->id);
            })->inRandomOrder()->first();

        if ($girl) {
            if (OperatorChatLimit::query()->where('man_id', $user->id)->where('girl_id', $girl->id)->exists()) {
                $log->info('Already exists for user: ' . $user->id . ' and girl ' . $girl->id);
                return;
            }

            $chatLimit = OperatorChatLimit::create([
                'man_id' => $user->id,
                'girl_id' => $girl->id,
                'limits' => 2,
                'chat_id' => null
            ]);


            $user->operator_limit_count = $user->operator_limit_count + 1;
            $user->save();

            $timeAssignment = OperatorLimitTimeAssignment::query()
                ->where('limit_count', '=', $user->operator_limit_count)
                ->orderByDesc('limit_count')
                ->first();

            if ($timeAssignment) {
                $time = rand($timeAssignment->time_from, $timeAssignment->time_to);
            } else {
                $log->warning("Not Found assignment for type: {$user->profile_type_id}, limits: {$user->operator_limit_count}");
                return;
            }

            $log->info('Limit Assignment: ' . $chatLimit->id . 'for user: ' . $user->id);
            $log->info('New Limits: ' . $chatLimit->id . ' by time: ' . $timeAssignment->id);
            $log->info('Sended new event for time: ' . $time);
//            OperatorLimitJob::dispatch($user)->onQueue('default')->delay($time);
        } else {
            $log->warning('Not found girl for : ' . $user->id);
        }
    }
}
