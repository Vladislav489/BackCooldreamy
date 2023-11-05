<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MigrationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('migrations')->delete();
        
        \DB::table('migrations')->insert(array (
            0 => 
            array (
                'id' => 1,
                'migration' => '2019_12_14_000001_create_personal_access_tokens_table',
                'batch' => 1,
            ),
            1 => 
            array (
                'id' => 2,
                'migration' => '2023_03_27_124601_create_Chats_AttachedFiles_table',
                'batch' => 1,
            ),
            2 => 
            array (
                'id' => 3,
                'migration' => '2023_03_27_124601_create_Chats_Messages_table',
                'batch' => 1,
            ),
            3 => 
            array (
                'id' => 4,
                'migration' => '2023_03_27_124601_create_Chats_Settings_table',
                'batch' => 1,
            ),
            4 => 
            array (
                'id' => 5,
                'migration' => '2023_03_27_124601_create_Chats_table',
                'batch' => 1,
            ),
            5 => 
            array (
                'id' => 6,
                'migration' => '2023_03_27_124601_create_DepositRequestSource_table',
                'batch' => 1,
            ),
            6 => 
            array (
                'id' => 7,
                'migration' => '2023_03_27_124601_create_FavoriteProfiles_table',
                'batch' => 1,
            ),
            7 => 
            array (
                'id' => 8,
                'migration' => '2023_03_27_124601_create_FeedsHistory_table',
                'batch' => 1,
            ),
            8 => 
            array (
                'id' => 9,
                'migration' => '2023_03_27_124601_create_GiftsList_table',
                'batch' => 1,
            ),
            9 => 
            array (
                'id' => 10,
                'migration' => '2023_03_27_124601_create_Letters_AttachedFiles_table',
                'batch' => 1,
            ),
            10 => 
            array (
                'id' => 11,
                'migration' => '2023_03_27_124601_create_Letters_Messages_table',
                'batch' => 1,
            ),
            11 => 
            array (
                'id' => 12,
                'migration' => '2023_03_27_124601_create_Letters_Settings_table',
                'batch' => 1,
            ),
            12 => 
            array (
                'id' => 13,
                'migration' => '2023_03_27_124601_create_Letters_table',
                'batch' => 1,
            ),
            13 => 
            array (
                'id' => 14,
                'migration' => '2023_03_27_124601_create_ProfilePictures_table',
                'batch' => 1,
            ),
            14 => 
            array (
                'id' => 15,
                'migration' => '2023_03_27_124601_create_Prompts_Careers_table',
                'batch' => 1,
            ),
            15 => 
            array (
                'id' => 16,
                'migration' => '2023_03_27_124601_create_Prompts_ChatMessages_table',
                'batch' => 1,
            ),
            16 => 
            array (
                'id' => 17,
                'migration' => '2023_03_27_124601_create_Prompts_FinanceStates_table',
                'batch' => 1,
            ),
            17 => 
            array (
                'id' => 18,
                'migration' => '2023_03_27_124601_create_Prompts_Interests_table',
                'batch' => 1,
            ),
            18 => 
            array (
                'id' => 19,
                'migration' => '2023_03_27_124601_create_Prompts_Relationships_table',
                'batch' => 1,
            ),
            19 => 
            array (
                'id' => 20,
                'migration' => '2023_03_27_124601_create_Prompts_Reports_table',
                'batch' => 1,
            ),
            20 => 
            array (
                'id' => 21,
                'migration' => '2023_03_27_124601_create_Prompts_Sources_table',
                'batch' => 1,
            ),
            21 => 
            array (
                'id' => 22,
                'migration' => '2023_03_27_124601_create_Prompts_Targets_table',
                'batch' => 1,
            ),
            22 => 
            array (
                'id' => 23,
                'migration' => '2023_03_27_124601_create_Prompts_TopChatMessages_table',
                'batch' => 1,
            ),
            23 => 
            array (
                'id' => 24,
                'migration' => '2023_03_27_124601_create_Prompts_WantKids_table',
                'batch' => 1,
            ),
            24 => 
            array (
                'id' => 25,
                'migration' => '2023_03_27_124601_create_Ratings_ActionsMale_table',
                'batch' => 1,
            ),
            25 => 
            array (
                'id' => 26,
                'migration' => '2023_03_27_124601_create_Ratings_Male_table',
                'batch' => 1,
            ),
            26 => 
            array (
                'id' => 27,
                'migration' => '2023_03_27_124601_create_Ratings_TableBalanceMale_table',
                'batch' => 1,
            ),
            27 => 
            array (
                'id' => 28,
                'migration' => '2023_03_27_124601_create_Reports_table',
                'batch' => 1,
            ),
            28 => 
            array (
                'id' => 29,
                'migration' => '2023_03_27_124601_create_UserData_table',
                'batch' => 1,
            ),
            29 => 
            array (
                'id' => 30,
                'migration' => '2023_03_27_124601_create_failed_jobs_table',
                'batch' => 1,
            ),
            30 => 
            array (
                'id' => 31,
                'migration' => '2023_03_27_124601_create_password_reset_tokens_table',
                'batch' => 1,
            ),
            31 => 
            array (
                'id' => 32,
                'migration' => '2023_03_27_124601_create_users_table',
                'batch' => 1,
            ),
            32 => 
            array (
                'id' => 33,
                'migration' => '2023_03_28_121044_create_Chats_table',
                'batch' => 3,
            ),
            33 => 
            array (
                'id' => 34,
                'migration' => '2023_03_28_121044_create_Chats_AttachedFiles_table',
                'batch' => 3,
            ),
            34 => 
            array (
                'id' => 35,
                'migration' => '2023_03_28_121044_create_Chats_Messages_table',
                'batch' => 3,
            ),
            35 => 
            array (
                'id' => 36,
                'migration' => '2023_03_28_121044_create_Chats_Settings_table',
                'batch' => 3,
            ),
            36 => 
            array (
                'id' => 37,
                'migration' => '2023_03_28_121044_create_DepositRequestSource_table',
                'batch' => 3,
            ),
            37 => 
            array (
                'id' => 38,
                'migration' => '2023_03_28_121044_create_FeedsHistory_table',
                'batch' => 3,
            ),
            38 => 
            array (
                'id' => 39,
                'migration' => '2023_03_28_121044_create_GiftsList_table',
                'batch' => 3,
            ),
            39 => 
            array (
                'id' => 40,
                'migration' => '2023_03_28_121044_create_Letters_table',
                'batch' => 3,
            ),
            40 => 
            array (
                'id' => 41,
                'migration' => '2023_03_28_121044_create_Letters_AttachedFiles_table',
                'batch' => 3,
            ),
            41 => 
            array (
                'id' => 42,
                'migration' => '2023_03_28_121044_create_Letters_Messages_table',
                'batch' => 3,
            ),
            42 => 
            array (
                'id' => 43,
                'migration' => '2023_03_28_121044_create_Letters_Settings_table',
                'batch' => 3,
            ),
            43 => 
            array (
                'id' => 44,
                'migration' => '2023_03_28_121044_create_Ratings_ActionsMale_table',
                'batch' => 3,
            ),
            44 => 
            array (
                'id' => 45,
                'migration' => '2023_03_28_121044_create_Ratings_Male_table',
                'batch' => 3,
            ),
            45 => 
            array (
                'id' => 46,
                'migration' => '2023_03_28_121044_create_Ratings_TableBalanceMale_table',
                'batch' => 3,
            ),
            46 => 
            array (
                'id' => 47,
                'migration' => '2023_03_28_121044_create_Reports_table',
                'batch' => 3,
            ),
            47 => 
            array (
                'id' => 48,
                'migration' => '2023_03_28_121044_create_UserData_table',
                'batch' => 3,
            ),
            48 => 
            array (
                'id' => 49,
                'migration' => '2023_03_28_121044_create_failed_jobs_table',
                'batch' => 3,
            ),
            49 => 
            array (
                'id' => 50,
                'migration' => '2023_03_28_121044_create_favorite_profiles_table',
                'batch' => 3,
            ),
            50 => 
            array (
                'id' => 51,
                'migration' => '2023_03_28_121044_create_password_reset_tokens_table',
                'batch' => 3,
            ),
            51 => 
            array (
                'id' => 52,
                'migration' => '2023_03_28_121044_create_personal_access_tokens_table',
                'batch' => 3,
            ),
            52 => 
            array (
                'id' => 53,
                'migration' => '2023_03_28_121044_create_profile_pictures_table',
                'batch' => 3,
            ),
            53 => 
            array (
                'id' => 54,
                'migration' => '2023_03_28_121044_create_prompt_careers_table',
                'batch' => 3,
            ),
            54 => 
            array (
                'id' => 55,
                'migration' => '2023_03_28_121044_create_prompt_chat_messages_table',
                'batch' => 3,
            ),
            55 => 
            array (
                'id' => 56,
                'migration' => '2023_03_28_121044_create_prompt_finance_states_table',
                'batch' => 3,
            ),
            56 => 
            array (
                'id' => 57,
                'migration' => '2023_03_28_121044_create_prompt_interests_table',
                'batch' => 3,
            ),
            57 => 
            array (
                'id' => 58,
                'migration' => '2023_03_28_121044_create_prompt_relationships_table',
                'batch' => 3,
            ),
            58 => 
            array (
                'id' => 59,
                'migration' => '2023_03_28_121044_create_prompt_reports_table',
                'batch' => 3,
            ),
            59 => 
            array (
                'id' => 60,
                'migration' => '2023_03_28_121044_create_prompt_sources_table',
                'batch' => 3,
            ),
            60 => 
            array (
                'id' => 61,
                'migration' => '2023_03_28_121044_create_prompt_targets_table',
                'batch' => 3,
            ),
            61 => 
            array (
                'id' => 62,
                'migration' => '2023_03_28_121044_create_prompt_top_chat_messages_table',
                'batch' => 3,
            ),
            62 => 
            array (
                'id' => 63,
                'migration' => '2023_03_28_121044_create_prompt_want_kids_table',
                'batch' => 3,
            ),
            63 => 
            array (
                'id' => 64,
                'migration' => '2023_03_28_121044_create_users_table',
                'batch' => 3,
            ),
            64 => 
            array (
                'id' => 65,
                'migration' => '2023_03_28_121047_add_foreign_keys_to_favorite_profiles_table',
                'batch' => 3,
            ),
            65 => 
            array (
                'id' => 66,
                'migration' => '2023_03_28_121047_add_foreign_keys_to_profile_pictures_table',
                'batch' => 3,
            ),
            66 => 
            array (
                'id' => 67,
                'migration' => '2023_03_28_121047_add_foreign_keys_to_users_table',
                'batch' => 3,
            ),
        ));
        
        
    }
}