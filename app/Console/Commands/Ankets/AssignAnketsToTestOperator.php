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
        $operators = [124519, 124518];
        foreach (User::query()->where('is_real', false)->where('gender', 'female')->get() as $user) {
            foreach ($operators as $operator) {
                $record = OperatorLinkUsers::where([['operator_id', $operator], ['user_id', $user->id]])->exists();
                if (!$record) {
                    OperatorLinkUsers::create([
                        'operator_id' => $operator,
                        'user_id' => $user->id,
                        'operator_work' => true,
                        'admin_work' => false,
                        'description' => false,
                        'disabled' => false,]);
                }
            }
        }
    }
}
