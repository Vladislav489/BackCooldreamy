<?php

namespace App\Console;

use App\Http\Controllers\UserController;
use App\ModelAdmin\CoreEngine\LogicModels\Ace\AceCronLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Limit\LimitChatOperatorCronLogic;
use App\ModelAdmin\CoreEngine\LogicModels\User\UserCooperationCronLogic;
use App\Models\Import\CronImportUser;
use App\Models\Setting;
use App\Models\User;
use App\Services\Operator\WorkingShiftService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void{

        $schedule->call(function (){
            try {
                set_time_limit(0);
                CronImportUser::runCron();
            }catch (\Throwable $e ){
                var_dump($e->getMessage(),$e->getFile(),$e->getLine());
            }
        })->everyMinute()->name('import_user');


        $schedule->call(function (){
            try {
                set_time_limit(0);
                UserCooperationCronLogic::RunCronSend();
            }catch (\Throwable $e ){
                var_dump($e->getMessage(),$e->getFile(),$e->getLine());
            }
        })->everyMinute()->name('user_cooperation');


        $schedule->call(function (){
            try {
                set_time_limit(0);
                UserCooperationCronLogic::RunCronSearchLead();
            }catch (\Throwable $e ){
                var_dump($e->getMessage(),$e->getFile(),$e->getLine());
            }
        })->everyMinute()->name('user_cooperation_rule');

        $schedule->call(function (){
            try {
                set_time_limit(0);
                CronImportUser::runCron();
            }catch (\Throwable $e ){
                var_dump($e->getMessage(),$e->getFile(),$e->getLine());
            }
        })->everyMinute()->name('import_user2');
        $schedule->call(function (){
            try {
                set_time_limit(0);
                CronImportUser::runCron();
            }catch (\Throwable $e ){
                logger($e->getMessage());
            }
        })->everyMinute()->name('import_user1');
        $schedule->call(function (){
            try {
                logger('ace:ace-send-user');
                set_time_limit(0);
                AceCronLogic::runCronAce();
            }catch (\Throwable $e ){
                logger($e->getMessage());
            }
        })->everyMinute()->name('ace_send_user');


        $schedule->call(function (){
            try {
                set_time_limit(0);
                LimitChatOperatorCronLogic::runCronLimit();
            }catch (\Throwable $e ){
                var_dump($e->getMessage(),$e->getFile(),$e->getLine(),$e->getTrace());
            }
        })->everyMinute()->name('limit_cron');

        $schedule->call(function () {
            WorkingShiftService::closeWorkIfInactiveMoreLimit();
        })->everyMinute()->name('work_close_cron');

        $schedule->call(function () {
            try {
                $settingFrom = Setting::query()->where('name', 'count_online_girls_from')->first();
                $settingTo = Setting::query()->where('name', 'count_online_girls_to')->first();
                $counts = rand($settingFrom->value ?? 100, $settingTo->value ?? 200);
                $users = User::query()->where('is_real', "=", '0')->where('gender', 'female')->inRandomOrder()->take($counts)->get();
                foreach ($users as $user) {
                    $user->online = 1;
                    $user->updated_at = now();
                    $user->save();
                }
            }catch (\Throwable $e){
                    var_dump($e->getMessage(),$e->getFile(),$e->getLine());
            }
        })->everyTenMinutes()->name('girl_random_online');
        // выключает юзеров из онлайн
        $schedule->call(function () {
            UserController::changeOnline();
        })->everyMinute()->name('user_offline');
        // todo this is for checkout
//        $schedule->command(StripeGetPayments::class)->everyFiveMinutes();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
