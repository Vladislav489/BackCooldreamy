<?php

namespace App\Console\Commands\Ankets;

use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Console\Command;

class AssignAnketsToTestOperator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:assign-ankets-to-test-operator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (User::query()->where('is_real', false)->where('gender', 'female')->get() as $user) {
            $operators = [124519, 124518];
            OperatorLinkUsers::create([
                'operator_id' => array_rand($operators),
                'user_id' => $user->id,
                'operator_work' => true,
                'admin_work' => false,
                'description' => false,
                'disabled' => false,
            ]);
        }
    }
}
