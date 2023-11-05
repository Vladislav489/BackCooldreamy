<?php

namespace App\Console\Commands\Ankets;

use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Console\Command;

class SyncAnketPrompts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-anket-prompts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Anket Prompts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::query()->where('gender', 'female')->where('is_real', false)->get();

        foreach ($users as $user) {
            $prompt_targets = AdminController::generateTargets('prompt_targets', $user->profile_type->name, 1, 3);

            $user->prompt_targets()->sync($prompt_targets);

            $prompt_interest = AdminController::generateTargets('prompt_interests', $user->profile_type->name, 3, 5);
            $user->prompt_interests()->sync($prompt_interest);

            $prompt_finance_states = AdminController::generateTargets('prompt_finance_states', $user->profile_type->name, 1, 1);
            $user->prompt_finance_states()->sync($prompt_finance_states);

            $prompt_sources = AdminController::generateTargets('prompt_sources', $user->profile_type->name, 1, 1);
            $user->prompt_sources()->sync($prompt_sources);

            $prompt_want_kids = AdminController::generateTargets('prompt_want_kids', $user->profile_type->name, 1, 1);
            $user->prompt_want_kids()->sync($prompt_want_kids);

            $prompt_relationships = AdminController::generateTargets('prompt_relationships', $user->profile_type->name, 1, 1);
            $user->prompt_relationships()->sync($prompt_relationships);

            $prompt_careers = AdminController::generateTargets('prompt_careers', $user->profile_type->name, 1, 1);
            $user->prompt_careers()->sync($prompt_careers);

            $this->info('Sync User prompts: '. $user->id);
        }
    }
}
