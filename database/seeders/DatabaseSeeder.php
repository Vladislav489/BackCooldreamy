<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(PromptSourcesTableSeeder::class);
        $this->call(LettersTableSeeder::class);
        $this->call(PersonalAccessTokensTableSeeder::class);
        $this->call(ChatsSettingsTableSeeder::class);
        $this->call(RatingsTableBalanceMaleTableSeeder::class);
        $this->call(PromptChatMessagesTableSeeder::class);
        $this->call(ProfilePicturesTableSeeder::class);
        $this->call(PromptTopChatMessagesTableSeeder::class);
        $this->call(LettersMessagesTableSeeder::class);
        $this->call(ReportsTableSeeder::class);
        $this->call(UserDataTableSeeder::class);
        $this->call(ChatsTableSeeder::class);
        $this->call(GiftsListTableSeeder::class);
        $this->call(PromptFinanceStatesTableSeeder::class);
        $this->call(DepositRequestSourceTableSeeder::class);
        $this->call(RatingsActionsMaleTableSeeder::class);
        $this->call(FavoriteProfilesTableSeeder::class);
        $this->call(ChatsMessagesTableSeeder::class);
        $this->call(LettersAttachedFilesTableSeeder::class);
        $this->call(PromptRelationshipsTableSeeder::class);
        $this->call(PromptCareersTableSeeder::class);
        $this->call(RatingsMaleTableSeeder::class);
        $this->call(LettersSettingsTableSeeder::class);
        $this->call(ChatsAttachedFilesTableSeeder::class);
        $this->call(PasswordResetTokensTableSeeder::class);
        $this->call(PromptInterestsTableSeeder::class);
        $this->call(PromptReportsTableSeeder::class);
        $this->call(FailedJobsTableSeeder::class);
        $this->call(PromptWantKidsTableSeeder::class);
        $this->call(PromptTargetsTableSeeder::class);
        $this->call(MigrationsTableSeeder::class);
        $this->call(UsersTableFactorySeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(FeedsTableSeeder::class);
        $this->call(WinksTableSeeder::class);
    }
}
