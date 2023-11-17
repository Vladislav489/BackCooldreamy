<?php

use App\ModelAdmin\ImportExport\ExportFronDBCSV;
use App\Models\Import\CronImportUser;
use App\Services\NextCloud\NextCloud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/reset_password/{token}', function (Request $request, string $token) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:password_reset_tokens,email',
    ]);

    $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();
    $validToken = Hash::check($token, $reset?->token);

    return $validator->fails() || !$reset || !$validToken
        ? redirect("auth/forgot-password?expired=true")
        : redirect("reset-password/{$token}?email={$request->email}");
})->middleware('guest')->name('app.password.reset');

Route::get('/', function () {
    return redirect('/login');
});

Route::group(['prefix' => 'mail', 'middleware' => 'throttle:3,1'], function (){
    Route::get('preview/{type}/{token}', function ($type, $token) {
        if(!$user = \App\Models\User::where(['token' => $token])->first()) abort(404);

        // $sender = \App\Models\User::find(10005);

        return match ($type) {
            'verification' => new App\Mail\VerificationMail($user->token, $user, true),
            'verified' => new App\Mail\UserVerifiedMail($user, true),
            // 'message' => new App\Mail\ResetPasswordMail($user->token, $user), //TODO: Can be implemented stub
            // 'liked' => new App\Mail\LikeUserMail($user), //TODO: Can be implemented stub
            default => abort(404),

        };
    })->name('mail.preview');




    Route::get('verify/{token}', function ($token){
        if(!$user = \App\Models\User::where(['token' => $token])->first()) abort(419);
         $user->email_verified_at = now();
         $user->is_email_verified = true;
         $user->save();
         // Mail::to($user)->send(new App\Mail\UserVerifiedMail($user)); // Uncomment if needed to notify user
        // return new App\Mail\UserVerifiedMail($user, true);
        return redirect("https://cooldreamy.com/search?user_id={$user->id}&token={$token}");
    })->name('mail.verify');
});



Route::get('register', function () {
    abort(404);
});

Auth::routes(['register' => false]);

Auth::routes();
Route::middleware(['role:admin'])->prefix('admin')->group(function () {
    Route::get('/clear/cache', function (){
        Artisan::call("route:clear");
        Artisan::call("cache:clear");
        Artisan::call("view:clear");
        return redirect()->back();
    });


    Route::get('script/sync/anket/prompts', function (){
        Artisan::call("sync-anket-prompts");
        return redirect()->back();
    })->name("app:stripe-get-payments");

    Route::get('script/stripe/get/payments', function (){
        Artisan::call("app:stripe-get-payments");
        return redirect()->back();
    })->name("stripe-get-payments");

    Route::get('script/get/resolved/ankets', function (){
        Artisan::call("app:get-resolved-ankets");
        return redirect()->back();
    })->name("get-resolved-ankets");

    Route::get('script/assign/ankets/to/test/operator', function (){
        Artisan::call("app:assign-ankets-to-test-operator");
        return redirect()->back();
    })->name("assign-ankets-to-test-operator");

    Route::get('script/assign/ankets/profile/f/files', function (){
        Artisan::call("app:assign-ankets-profile-f-files");
        return redirect()->back();
    })->name("assign-ankets-profile-f-files");

    Route::get('script/assign/ankets/new/all/files', function (){
        Artisan::call("app:assign-ankets-new-all-files");
        return redirect()->back();
    })->name("assign-ankets-new-all-files");

    Route::get('script/assign/ankets/files', function (){
        set_time_limit(2000);
        Artisan::call("app:assign-ankets-files");
        return redirect()->back();
    })->name("assign-ankets-files");

    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashbord'])
        ->name('admin.dashbord');

    Route::get('/dashbord/user/list', [App\Http\Controllers\AdminController::class, 'getDataList'])
        ->name('admin.dashbord.user.list');

    Route::post('/dashbord/user/statistic', [App\Http\Controllers\AdminController::class, 'getCountStatistic'])
        ->name('admin.dashbord.user.statistic');
    Route::post('/dashbord/export/statistic', [App\Http\Controllers\AdminController::class, 'exportStatisticUser'])
        ->name('admin.dashbord.export.user.statistic');





    Route::post('/export-operators/csv', [App\Http\Controllers\AdminController::class, 'exportCsvOperator'])
        ->name('admin.export.csv.cperator');
    Route::post('/export-user/csv', [App\Http\Controllers\AdminController::class, 'exportCsvUser'])
        ->name('admin.export.csv.user');



    Route::get('/import', [App\Http\Controllers\AdminController::class, 'importPage']);
    Route::post('/save/user/one', [App\Http\Controllers\AdminController::class, 'seveUserImage'])
        ->name('admin.import.csv.user.one.image');
    Route::get('/import-aces', [App\Http\Controllers\AdminController::class, 'importAces']);
    Route::get('/import/operators/', [\App\Http\Controllers\AdminController::class, 'operatorsMultichat']);

    Route::post('/upload/fast', [App\Http\Controllers\AdminController::class, 'uploadFastUser'])
        ->name('admin.csv_users.upload.without.image');
    Route::post('/upload/image', [App\Http\Controllers\AdminController::class, 'uploadUseImage'])
        ->name('admin.csv_users.upload.with.image');


    Route::post('/csv-users/upload', [App\Http\Controllers\AdminController::class, 'upload'])->name('admin.csv_users.upload');
    Route::get('/csv-users/data', [App\Http\Controllers\AdminController::class, 'getData'])->name('admin.csv_users.data');
    Route::get('/operators/data', [\App\Http\Controllers\AdminController::class, 'getOperatorsData']);
    Route::post('/csv-operators/upload', [App\Http\Controllers\AdminController::class, 'uploadOperators'])->name('admin.csv_operators.upload');



    Route::post('/csv-operators-admin-ankets/upload', [\App\Http\Controllers\AdminController::class, 'uploadOperatorsAdminAnkets'])
        ->name('admin.csv_operator_admin_ankets.upload');
    Route::get('/csv-operators/data', [App\Http\Controllers\AdminController::class, 'getOperators'])->name('admin.csv_operators.data');

    Route::post('/ankets/timezone', [\App\Http\Controllers\AdminController::class, 'loadTimezone'])->name('admin.ankets.timezone');

    Route::post('/csv-administrators/upload', [App\Http\Controllers\AdminController::class, 'uploadAdministrators'])->name('admin.csv_administrators.upload');
    Route::get('/csv-administrators/data', [App\Http\Controllers\AdminController::class, 'getAdministrators'])->name('admin.csv_administrators.data');

    Route::post('/csv-aces/upload', [App\Http\Controllers\AdminController::class, 'uploadAces'])->name('admin.csv_aces.upload');
    Route::get('/csv-aces/data', [App\Http\Controllers\AdminController::class, 'getAces'])->name('admin.csv_aces.data');

    Route::get('/message-count', [App\Http\Controllers\StatisticsController::class, 'messageCount']);
    Route::get('/message-count-by-users', [App\Http\Controllers\StatisticsController::class, 'messageCountByUsers']);
    Route::get('/age-count-by-users', [App\Http\Controllers\StatisticsController::class, 'ageCountByUsers']);
    Route::get('/registration-users', [App\Http\Controllers\StatisticsController::class, 'getUsersList']);
    Route::get('/cities-list', [App\Http\Controllers\StatisticsController::class, 'getCityList']);
    Route::get('/countries-list', [App\Http\Controllers\StatisticsController::class, 'getCountriesList']);
    Route::post('/upload-countries', [\App\Http\Controllers\AdminController::class, 'uploadRegions'])->name('admin.upload_countries');

    Route::get('/pages', [\App\Http\Controllers\PageController::class, 'adminIndex']);
    Route::get('pages/data', [\App\Http\Controllers\PageController::class, 'adminData']);
    Route::get('/pages/{page}', [\App\Http\Controllers\PageController::class, 'adminShow']);
    Route::post('/pages/{page}', [\App\Http\Controllers\PageController::class, 'update'])->name('admin.pages.update');
    Route::post('/update', [\App\Http\Controllers\PageController::class, 'adminUpdate']);

    Route::get('/settings/', [\App\Http\Controllers\PageController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/change', [\App\Http\Controllers\PageController::class, 'changeSetting'])->name('admin.change_settings');
    Route::get('/get/folder/nextcloud', function (){
        $ApiStoreg = new NextCloud('dmitry','Ag@19836373!','nc.cooldreamy.com');

        $folder = $ApiStoreg->getFolder("/media")->getResponse();
        array_shift($folder);
        foreach ($folder as $key => $item){
            $folder[$key]  = str_replace( ['/media','/'],'',urldecode($item));
        }
        $csv = new ExportFronDBCSV();
        $csv->setDataFrom($folder);
        $csv->setFileName("folder_nextCloud");
        $csvBody = $csv->run();
        return response()->streamDownload(function () use ($csvBody) {echo $csvBody;},$csv->getFileName());
    });

});

Route::get('/data/settings/geo/{id}', [\App\Http\Controllers\PageController::class, 'getSettinggeo']);
Route::get('/data/settings/{id}', [\App\Http\Controllers\PageController::class, 'getSetting']);


Route::get('test/send/email',function() {
    $user = \App\Models\User::find(126961);
    $user->email = "79181017998@nvgroup.ru";
    dd(\Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\VerificationMail(1234, $user)));
});

Route::get('test/send/image',function(){
    $ApiStoreg = new NextCloud('dmitry','Ag@19836373!','nc.cooldreamy.com');
    $dir = "/media";



     dd( $ApiStoreg->createFolder($dir));
    //$file = storage_path("/app/public/s-l1600.jpg");
    //$content =  file_get_contents( $file);
    //$ApiStoreg->upoadFile($dir,$content,"test.jpg");
});
Route::get('/test1', [\App\Http\Controllers\PaymentController::class, 'testWebHook']);


Route::get('/test', function (){
   $userList = \App\Models\User\UserCooperation::query()->get()->toArray();
   foreach ($userList as $item){
       $curl = curl_init();
       curl_setopt_array($curl, array(
           CURLOPT_URL => "http://95.179.250.121/e39dfb1/postback?subid={$item['subid']}&status=lead",
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => '',
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 0,
           CURLOPT_FOLLOWLOCATION => true,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => 'GET',
       ));
       $response = curl_exec($curl);
       var_dump("Log User {$item['subid']} Conversion  ". json_encode($response));
       curl_close($curl);
   }
});



