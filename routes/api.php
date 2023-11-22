<?php

use App\Events\UpdateNotificationEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
//костыль ради фронта

Route::post('add/token/firebase',[\App\Http\Controllers\API\V1\AuthController::class, 'addTokenFireBase'])->middleware('auth:sanctum');
Route::any('/stripe', [\App\Http\Controllers\PaymentController::class, 'stripeWebhook']);

Route::post('/deploy', [\App\Http\Controllers\DeployController::class, 'deploy']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {return $request->user();});
Route::middleware('auth:sanctum')->get('/name', function (Request $request) {
    return response()->json(['name' => $request->user()->name]);
});






Route::get('library/countries', [\App\Http\Controllers\API\V1\Library\LibraryController::class, 'countries']);

// Geo
Route::get('/location', [\App\Http\Controllers\API\V1\AuthController::class, 'geoLocation']);
Route::get('pages/', [\App\Http\Controllers\PageController::class, 'index']);
Route::get('/time/server',  function (Request $request) {
    return response()->json(['data' => now()]);
});


Route::post('register', [\App\Http\Controllers\API\V1\AuthController::class, 'register']);

Route::post('check/email', [\App\Http\Controllers\API\V1\AuthController::class, 'checkEmail']);

Route::post('verification', [\App\Http\Controllers\API\V1\AuthController::class, 'sendVerification']);
Route::post('reset/password', [\App\Http\Controllers\API\V1\AuthController::class, 'sendCodeResetPassword']);
Route::post('send/code/password', [\App\Http\Controllers\API\V1\AuthController::class, 'resetPassword']);
Route::post('send/password/change', [\App\Http\Controllers\API\V1\AuthController::class, 'passChange'])->middleware('auth:sanctum');



Route::post('send/verification', [\App\Http\Controllers\API\V1\AuthController::class, 'sendVerificationMail'])->middleware('auth:sanctum');



Route::post('ace/add', [\App\Http\Controllers\API\V1\AuthController::class, 'setAces'])->middleware('auth:sanctum');
Route::post('/email/resend', [\App\Http\Controllers\API\V1\AuthController::class, 'resendEmail'])->middleware('auth:sanctum', 'throttle:3.10');
Route::post('/{arbitrator_id}/register', [\App\Http\Controllers\API\V1\AuthController::class, 'register']);
Route::post('token', [\App\Http\Controllers\API\V1\AuthController::class, 'token']);
Route::post('send/url/statistiс', [\App\Http\Controllers\API\V1\AuthController::class, 'urlStatistic']);
Route::post('logout', [\App\Http\Controllers\API\V1\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('user/set/info', [\App\Http\Controllers\API\V1\AuthController::class, 'setInfo'])->middleware('auth:sanctum');

Route::post('send_mail', [\App\Http\Controllers\MailController::class, 'send']);

Route::resource('prompt_target', "\App\Http\Controllers\API\V1\PromptTargetController", ['except' => ['create', 'edit']])->middleware('auth:sanctum');



Route::post('image_store', [\App\Http\Controllers\API\V1\ImageController::class, 'store'])->middleware('auth:sanctum');
Route::post('file_store', [\App\Http\Controllers\API\V1\FileController::class, 'store'])->middleware('auth:sanctum');
Route::post('video_store', [\App\Http\Controllers\API\V1\FileController::class, 'store_video'])->middleware('auth:sanctum');

Route::get('feeds', [\App\Http\Controllers\API\V1\FeedController::class, 'index'])->middleware('auth:sanctum');
Route::post('feed/set_like', [\App\Http\Controllers\API\V1\FeedController::class, 'set_feed_liked'])->middleware('auth:sanctum');
Route::post('feed/set_skipe', [\App\Http\Controllers\API\V1\FeedController::class, 'set_feed_skipped'])->middleware('auth:sanctum');

// Статистика
Route::get('/feeds/statistic', [\App\Http\Controllers\API\V1\FeedController::class, 'statistic'])->middleware('auth:sanctum');
Route::post('/feeds/read', [\App\Http\Controllers\API\V1\FeedController::class, 'read'])->middleware('auth:sanctum');

Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->middleware('auth:sanctum');
Route::any('users/search', [\App\Http\Controllers\UserController::class, 'search'])->middleware('auth:sanctum');
Route::get('get_prompt_targets_table', [\App\Http\Controllers\API\V1\PromptController::class, 'get_prompt_targets_table'])->middleware('auth:sanctum');
Route::get('get_prompt_finance_states_table', [\App\Http\Controllers\API\V1\PromptController::class, 'get_prompt_finance_states_table'])->middleware('auth:sanctum');
Route::get('get_all_prompts', [\App\Http\Controllers\API\V1\PromptController::class, 'get_all_prompts'])->middleware('auth:sanctum');

Route::post('wink/send_wink', [\App\Http\Controllers\API\V1\WinkController::class, 'send_wink'])->middleware('auth:sanctum');

Route::get('get_stickers', [\App\Http\Controllers\API\V1\StickerController::class, 'index'])->middleware('auth:sanctum');
Route::get('get_gifts', [\App\Http\Controllers\API\V1\GiftController::class, 'index'])->middleware('auth:sanctum');

Route::get('get_countries_validate_user', [\App\Http\Controllers\API\V1\LocationController::class, 'get_countries_validate_user']);
Route::get('get_countries', [\App\Http\Controllers\API\V1\LocationController::class, 'get_countries']);

Route::get('get_states_validate_user', [\App\Http\Controllers\API\V1\LocationController::class, 'get_states_validate_user']);
Route::get('get_states', [\App\Http\Controllers\API\V1\LocationController::class, 'get_states']);


Route::get('chats/get_my_chat_list1', [\App\Http\Controllers\API\V1\ChatController::class, 'get_my_chat_list1'])->middleware('auth:sanctum');


Route::get('chats/get_my_chat_list', [\App\Http\Controllers\API\V1\ChatController::class, 'get_my_chat_list'])->middleware('auth:sanctum');
Route::get('chats/get_my_favorite_chat_list', [\App\Http\Controllers\API\V1\ChatController::class, 'get_my_favorite_chat_list'])->middleware('auth:sanctum');
Route::get('chats/get_chat_with_user', [\App\Http\Controllers\API\V1\ChatController::class, 'get_chat_with_user'])->middleware('auth:sanctum');

Route::get('chats/get_current_chat1', [\App\Http\Controllers\API\V1\ChatController::class, 'get_current_chat1'])->middleware('auth:sanctum');


Route::get('chats/get_current_chat', [\App\Http\Controllers\API\V1\ChatController::class, 'get_current_chat'])->middleware('auth:sanctum');
Route::get('chats/search_chat_message', [\App\Http\Controllers\API\V1\ChatController::class, 'searchChatMessage'])->middleware('auth:sanctum');
Route::post('chats/send_chat_text_message', [\App\Http\Controllers\API\V1\ChatController::class, 'send_chat_text_message'])->middleware('auth:sanctum');
Route::post('chats/send_chat_sticker_message', [\App\Http\Controllers\API\V1\ChatController::class, 'send_chat_sticker_message'])->middleware('auth:sanctum');
Route::post('chats/send_chat_gift_message', [\App\Http\Controllers\API\V1\ChatController::class, 'send_chat_gift_message'])->middleware('auth:sanctum');
Route::post('chats/send_chat_image_message', [\App\Http\Controllers\API\V1\ChatController::class, 'send_chat_image_message'])->middleware('auth:sanctum');
Route::post('chats/send_chat_image_video', [\App\Http\Controllers\API\V1\ChatController::class, 'send_chat_image_video'])->middleware('auth:sanctum');

Route::post('chats/set_chat_message_is_read', [\App\Http\Controllers\API\V1\ChatController::class, 'set_chat_message_is_read'])->middleware('auth:sanctum');

Route::get('chats/statistics', [\App\Http\Controllers\API\V1\ChatController::class, 'get_chat_statistics'])->middleware('auth:sanctum');

Route::get('pwa/set', [\App\Http\Controllers\API\V1\AuthController::class, 'pwaSet'])->middleware('auth:sanctum');
Route::delete('chats/delete_chat/{id}', [\App\Http\Controllers\API\V1\ChatController::class, 'delete'])->middleware('auth:sanctum')->name('chat.delete');
Route::get('chats/media/{id}', [\App\Http\Controllers\API\V1\ChatController::class, 'media'])->middleware('auth:sanctum');
Route::patch('chats/{id}/ignore', [\App\Http\Controllers\API\V1\ChatController::class, 'ignore'])->middleware('auth:sanctum');
Route::post('chats/{id}/report', [\App\Http\Controllers\API\V1\ChatController::class, 'report'])->middleware('auth:sanctum');

Route::post('store/image/delete', [\App\Http\Controllers\API\V1\ImageController::class, 'deleteImage'])->middleware('auth:sanctum');


Route::post('images/{message}/pay', [\App\Http\Controllers\API\V1\ChatController::class, 'payForImage'])->middleware('auth:sanctum');

// statistic
Route::get('chats/unread', [\App\Http\Controllers\API\V1\ChatController::class, 'unread'])->middleware('auth:sanctum');

Route::get('letters/get_my_letter_list', [\App\Http\Controllers\API\V1\LetterController::class, 'get_my_letter_list'])->middleware('auth:sanctum');
Route::get('letters/get_letter_with_user', [\App\Http\Controllers\API\V1\LetterController::class, 'get_letter_with_user'])->middleware('auth:sanctum');
Route::get('letters/get_current_letter', [\App\Http\Controllers\API\V1\LetterController::class, 'get_current_letter'])->middleware('auth:sanctum');
Route::post('letters/send_letter_text_message', [\App\Http\Controllers\API\V1\LetterController::class, 'send_letter_text_message'])->middleware('auth:sanctum');
Route::post('letters/send_letter_sticker_message', [\App\Http\Controllers\API\V1\LetterController::class, 'send_letter_sticker_message'])->middleware('auth:sanctum');
Route::post('letters/send_letter_gift_message', [\App\Http\Controllers\API\V1\LetterController::class, 'send_letter_gift_message'])->middleware('auth:sanctum');
Route::post('letters/send_letter_image_message', [\App\Http\Controllers\API\V1\LetterController::class, 'send_letter_image_message'])->middleware('auth:sanctum');

Route::post('letters/set_letter_message_is_read', [\App\Http\Controllers\API\V1\LetterController::class, 'set_letter_message_is_read'])->middleware('auth:sanctum');
Route::post('letters/pay_for_letter_text_message', [\App\Http\Controllers\API\V1\LetterController::class, 'pay_for_letter_text_message'])->middleware('auth:sanctum');
Route::post('letters/pay_for_letter_image', [\App\Http\Controllers\API\V1\LetterController::class, 'pay_for_letter_image'])->middleware('auth:sanctum');

Route::get('profile/get_my_profile', [\App\Http\Controllers\API\V1\ProfileController::class, 'get_my_profile'])->middleware('auth:sanctum');
Route::get('profile/get_profile', [\App\Http\Controllers\API\V1\ProfileController::class, 'get_profile'])->middleware('auth:sanctum');
Route::put('profile/update_my_profile', [\App\Http\Controllers\API\V1\ProfileController::class, 'update_my_profile'])->middleware('auth:sanctum');

Route::get('activities/get_my_watchers', [\App\Http\Controllers\API\V1\Activities\AnketWatchController::class, 'getMyWatchers'])->middleware('auth:sanctum');
Route::get('activities/get_my_watched', [\App\Http\Controllers\API\V1\Activities\AnketWatchController::class, 'getMyWatched'])->middleware('auth:sanctum');
Route::get('activities/get_mutual_watched_users', [\App\Http\Controllers\API\V1\Activities\AnketWatchController::class, 'getMutualWatchedUsers'])->middleware('auth:sanctum');

Route::get('profile/get_free_message', [\App\Http\Controllers\API\V1\CreditsController::class, 'get_free_message'])->middleware('auth:sanctum');

Route::get('activities/get_my_favorite', [\App\Http\Controllers\API\V1\Activities\AnketFavoriteController::class, 'getMyFavorite'])->middleware('auth:sanctum');
Route::get('activities/get_favorited_me', [\App\Http\Controllers\API\V1\Activities\AnketFavoriteController::class, 'getFavoritedMe'])->middleware('auth:sanctum');
Route::get('activities/get_mutual_favorite', [\App\Http\Controllers\API\V1\Activities\AnketFavoriteController::class, 'getMutualFavorite'])->middleware('auth:sanctum');
Route::post('activities/add_favorite', [\App\Http\Controllers\API\V1\Activities\AnketFavoriteController::class, 'addFavorite'])->middleware('auth:sanctum');
Route::post('activities/disable_from_favorite', [\App\Http\Controllers\API\V1\Activities\AnketFavoriteController::class, 'disableFromFavorite'])->middleware('auth:sanctum');

Route::get('activities/get_my_likes', [\App\Http\Controllers\API\V1\Activities\AnketLikeController::class, 'getMyLikes'])->middleware('auth:sanctum');
Route::get('activities/get_liked_me', [\App\Http\Controllers\API\V1\Activities\AnketLikeController::class, 'getLikedMe'])->middleware('auth:sanctum');
Route::get('activities/get_mutual_likes', [\App\Http\Controllers\API\V1\Activities\AnketLikeController::class, 'getMutualLikedUsers'])->middleware('auth:sanctum');

Route::get('profile/get_my_credits', [\App\Http\Controllers\API\V1\CreditsController::class, 'get_my_credits'])->middleware('auth:sanctum');

Route::post('profile/check_payment', [\App\Http\Controllers\API\V1\CreditsController::class, 'check_payment'])->middleware('auth:sanctum');
Route::post('profile/get_services_cost', [\App\Http\Controllers\API\V1\CreditsController::class, 'get_services_cost'])->middleware('auth:sanctum');
Route::post('profile/put_service_cost', [\App\Http\Controllers\API\V1\CreditsController::class, 'put_service_cost'])->middleware('auth:sanctum');
Route::post('profile/set_service_cost', [\App\Http\Controllers\API\V1\CreditsController::class, 'set_service_cost'])->middleware('auth:sanctum');

Route::get('profile/{id}/open', [\App\Http\Controllers\API\V1\Activities\AnketLikeController::class, 'openUserProfile'])->middleware('auth:sanctum');

Route::post('/verify', [\App\Http\Controllers\API\V1\AuthController::class, 'verify'])->middleware('auth:sanctum');

// Подписки
Route::group(['prefix' => 'payments/', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/watch', [\App\Http\Controllers\PaymentController::class, 'watch']);
    Route::get('/credits/list', [\App\Http\Controllers\PaymentController::class, 'creditList']);
    Route::get('/subscription/list', [\App\Http\Controllers\PaymentController::class, 'subscriptionList']);
    Route::get('/premium/list', [\App\Http\Controllers\PaymentController::class, 'premiumList']);
    Route::get('/promotions/list', [\App\Http\Controllers\PaymentController::class, 'promotion']);
    Route::get('/promotions/lists', [\App\Http\Controllers\PaymentController::class, 'promotions']);

    Route::post('/subscribe', [\App\Http\Controllers\PaymentController::class, 'subscribe']);
    Route::post('/pay', [\App\Http\Controllers\PaymentController::class, 'pay']);
    Route::post('/promotions/activate', [\App\Http\Controllers\PaymentController::class, 'activatePromotion']);
    Route::get('/subscription', [\App\Http\Controllers\PaymentController::class, 'subscription']);
    Route::get('/premium', [\App\Http\Controllers\PaymentController::class, 'premium']);
});


// Мультичат  /operators/chats/
Route::group(['prefix' => 'operators/', 'middleware' => ['auth:sanctum', 'role:operator|admin']], function () {
    Route::post('store/images/add', [\App\Http\Controllers\API\V1\ImageController::class, 'storeImages'])->middleware('auth:sanctum');
    Route::post('store/image/delete', [\App\Http\Controllers\API\V1\ImageController::class, 'deleteImage'])->middleware('auth:sanctum');



    Route::post('/forfeits/messages', [\App\Http\Controllers\API\V1\OperatorMessageController::class, 'forfeitsMessage']);


    Route::get('/messages', [\App\Http\Controllers\API\V1\OperatorMessageController::class, 'index']);
    Route::get('/limits', [\App\Http\Controllers\API\V1\OperatorMessageController::class, 'limits']);
    Route::get('/latterlimits', [\App\Http\Controllers\API\V1\OperatorMessageController::class, 'latterlimits']);
    Route::get('/statistics', [\App\Http\Controllers\API\V1\OperatorMessageController::class, 'statistics']);
    Route::get('/me', [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'me']);
    Route::post('/block/limits', [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'blockLimits']);
    Route::get('/man/{id}/info', [\App\Http\Controllers\API\V1\OperatorAncetController::class, 'info']);

   /** Анкеты */
    Route::group(['prefix' => 'ancets/'], function () {
        Route::get('/', [\App\Http\Controllers\API\V1\OperatorAncetController::class, 'index']);
        Route::get('/{id}/media', [\App\Http\Controllers\API\V1\OperatorAncetController::class, 'media']);
        Route::get('/{id}/info', [\App\Http\Controllers\API\V1\OperatorAncetController::class, 'info']);
        Route::get('/{id}/get/{manId}', [\App\Http\Controllers\API\V1\OperatorAncetController::class, 'getMan']);
    });

    /** Статистика */
    Route::group(['prefix' => 'statistics/'], function () {
        Route::get('/', [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'statistic']);

        Route::get('/operator/message/first/time',
            [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'getOperatorMessageFirstTime']);
        Route::get('/operator/message/time/all',
            [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'getOperatorMessageTime']);
        Route::get('/operator/message/count',
            [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'getOperatorMessageCount']);
        Route::get('/operator/message/statistic',
            [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'getOperatorMessageStatistic']);
    });

    /** Отчеты */
    Route::group(['prefix' => 'reports/'], function () {
        Route::get('/', [\App\Http\Controllers\API\V1\OperatorReportController::class, 'reports']);
        Route::post('/', [\App\Http\Controllers\API\V1\OperatorReportController::class, 'store']);
        Route::delete('/{id}', [\App\Http\Controllers\API\V1\OperatorReportController::class, 'delete']);
    });

    /** Логи */
    Route::group(['prefix' => 'logs/'], function () {
        Route::get('/', [\App\Http\Controllers\API\V1\OperatorLogController::class, 'logs']);
        Route::post('/', [\App\Http\Controllers\API\V1\OperatorLogController::class, 'store']);
        Route::delete('/{id}', [\App\Http\Controllers\API\V1\OperatorLogController::class, 'delete']);
    });

    /** Штрафы */
    Route::group(['prefix' => 'fines/'], function () {
        Route::get('/', [\App\Http\Controllers\API\V1\OperatorFineController::class, 'fines']);
        Route::post('/', [\App\Http\Controllers\API\V1\OperatorFineController::class, 'store']);
        Route::delete('/{id}', [\App\Http\Controllers\API\V1\OperatorFineController::class, 'delete']);
    });

    /** Рабочий режим */
    Route::group(['prefix' => 'working-shifts'], function() {
     //   Route::get('/', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'find']);
        Route::get('/get/status/list', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'getStatusList']);
        Route::get('/get/current/status', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'getCurrentStatus']);
        Route::post('/work/start', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'start']);
        Route::post('/work/stop', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'stop']);
        Route::post('/paused/start', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'pausedStart']);
        Route::post('/paused/stop', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'pausedStop']);
        Route::post('/inactive', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'inactive']);
        Route::post('/work/log/time', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'workTimeLog']);
        Route::delete('/inactive/delete', [\App\Http\Controllers\API\V1\WorkingShiftController::class, 'inactiveDelete']);
    });


    /** Чаты */
    Route::group(['prefix' => 'chats/'], function () {
        // Получение всех чатов с пользователем
        Route::get('/', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'index']);
        // Поиск по сообщениям
        Route::get('/{id}/search', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'searchChatMessage']);
        // Отправка стикера
        Route::post('/{id}/send/sticker/{sticker}', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'sendSticker']);
        // Отправка гифки
        Route::post('/{id}/send/gift/{gift}', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'sendGift']);
        // Отправка сообщения
        Route::post('/{id}/send/message', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'sendMessage']);
        // Отправка изображений
        Route::post('/{id}/send/image', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'sendImage']);
        // Чтение сообщения
        Route::post('/{id}/read/{message}', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'read']);
        // Получение всех медиафайлов
        Route::get('/{id}/media', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'media']);
        // Получение медиафайлов анкеты
        Route::get('/{id}/anket/media', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'anketMedia']);

        // Отправка чата в игнор
        Route::patch('/{id}/ignore', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'ignore']);
        // Отправка чата в избранное
        Route::patch('/{id}/favorite', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'favorite']);
        // Личная страница
        Route::get('/{chat}', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'show']);
        Route::post('/store/chat', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'storeChat']);
        // Удаление чата
        Route::delete('delete/{id}', [\App\Http\Controllers\API\V1\OperatorChatController::class, 'delete']);
    });

    /** Письма */
    Route::group(['prefix' => 'letter/'], function () {
        // Получение всех писем с пользователем
        Route::get('/', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'index']);
        // Получение письма
        Route::get('/{letter}', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'show']);
        // Отправка текстового сообщения
        Route::post('/{letter}/send/message', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'sendMessage']);
        // Отправка стикера
        Route::post('/{letter}/send/sticker/{sticker}', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'sendSticker']);
        // Отправка подарка
        Route::post('/{letter}/send/gift/{gift}', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'sendGift']);
        // Чтение сообщения
        Route::post('/{letter}/read/{message}', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'read']);
        Route::post('/store/letter', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'storeLetter']);

        // Отправка изображения
        Route::post('/{id}/send/image', [\App\Http\Controllers\API\V1\OperatorLetterController::class, 'sendImage']);
    });
});

// Мультичат админа
Route::group(['prefix' => 'admin/', 'middleware' => ['auth:sanctum', 'role:admin']], function () {
    // Все чаты + письма операторов админов
    Route::get('/messages', [\App\Http\Controllers\API\V1\Admin\OperatorMessageController::class, 'index']);
    // Текущий админ
    Route::get('/me', [\App\Http\Controllers\API\V1\OperatorStatisticController::class, 'me']);

    Route::post('/load/media/user', [\App\Http\Controllers\API\V1\OperatorAncetController::class, 'loadMediaUser']);

    Route::get('/messages/last/work/day', [\App\Http\Controllers\API\V1\Admin\OperatorMessageController::class, 'getAllMessageLast8Hour']);
    /** Статистика */
    Route::group(['prefix' => 'statistics/'], function () {
        Route::get('/', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class, 'statistic']);
        Route::get('/graphic', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class, 'graphic']);
        Route::get('/table', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class, 'table']);
        Route::get('/operator/inactive', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'inactiveOperator']);

        Route::get('/operator/list/admin', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getOperatorListStatistic']);
        Route::get('/operator/list', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getOperatorList']);


        Route::get('/count/message', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getCountMessage']);

        Route::get('/sale/balnce', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getSaleBalance']);
        Route::get('/operator/first/message/avg/time', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getAverageMessageFirstTimeOperator']);
        Route::get('/operator/first/message/chat/time', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getMessageFirstTimeByChat']);
        Route::get('/operator/count/message', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getCountMessageListOperator']);
        Route::get('/count/ancet', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getCountAncet']);
        Route::get('/count/ancet/work', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getCountAncetWork']);
        Route::get('/first/message/avg/time', [\App\Http\Controllers\API\V1\Admin\OperatorStatisticController::class,'getAverageMessageFirstTime']);
    });

    Route::group(['prefix' => 'chats/'], function () {
        Route::get('/', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'index']);
        // Отправка стикера
        Route::post('/{id}/send/sticker/{sticker}', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'sendSticker']);
        // Отправка гифки
        Route::post('/{id}/send/gift/{gift}', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'sendGift']);
        // Отправка сообщения
        Route::post('/{id}/send/message', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'sendMessage']);
        // Отправка изображений
        Route::post('/{id}/send/image', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'sendImage']);
        // Чтение сообщения
        Route::post('/{id}/read/{message}', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'read']);
        // Получение всех медиафайлов
        Route::get('/{id}/media', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'media']);
        // Отправка чата в игнор
        Route::patch('/{id}/ignore', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'ignore']);
        // Отправка чата в избранное
        Route::patch('/{id}/favorite', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'favorite']);
        // Личная страница
        Route::get('/{chat}', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'show']);
        // Удаление чата
        Route::delete('/{id}', [\App\Http\Controllers\API\V1\Admin\OperatorChatController::class, 'delete']);
    });

    /** Письма */
    Route::group(['prefix' => 'letter/'], function () {
        // Получение всех писем с пользователем
        Route::get('/', [\App\Http\Controllers\API\V1\Admin\OperatorLetterController::class, 'index']);
        // Получение письма
        Route::get('/{letter}', [\App\Http\Controllers\API\V1\Admin\OperatorLetterController::class, 'show']);
        // Отправка текстового сообщения
        Route::post('/{letter}/send/message', [\App\Http\Controllers\API\V1\Admin\OperatorLetterController::class, 'sendMessage']);
        // Отправка стикера
        Route::post('/{letter}/send/sticker/{sticker}', [\App\Http\Controllers\API\V1\Admin\OperatorLetterController::class, 'sendSticker']);
        // Отправка подарка
        Route::post('/{letter}/send/gift/{gift}', [\App\Http\Controllers\API\V1\Admin\OperatorLetterController::class, 'sendGift']);
        // Чтение сообщения
        Route::post('/{letter}/read/{message}', [\App\Http\Controllers\API\V1\Admin\OperatorLetterController::class, 'read']);
        // Отправка изображения
        Route::post('/{id}/send/image', [\App\Http\Controllers\API\V1\Admin\OperatorLetterController::class, 'sendImage']);
    });
});

// Конец мультичата

// Оповещения для вебсокетов
Route::get('notifications/statistics', [\App\Http\Controllers\API\V1\AuthController::class, 'getStatistics'])->middleware('auth:sanctum');
Route::post('notifications/read/{type}', [\App\Http\Controllers\API\V1\AuthController::class, 'readNotifications'])->middleware('auth:sanctum');



// Проверка изображений
Route::post('check-image', [\App\Http\Controllers\API\V1\AuthController::class, 'checkImage']);



/*
BadMethodCallException: Call to undefined method App\Models\User\UserRole::BadMethodCallException: Call to undefined method App\Models\User\UserRole::tableName() in file C:\OSPanel\domains\site.com\vendor\laravel\framework\src\Illuminate\Support\Traits\ForwardsCalls.php on line 67
 in file C:\OSPanel\domains\site.com\vendor\laravel\framework\src\Illuminate\Support\Traits\ForwardsCalls.php on line 67

 */
