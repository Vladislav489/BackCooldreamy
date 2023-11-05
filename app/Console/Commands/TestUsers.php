<?php

namespace App\Console\Commands;

use App\Http\Controllers\AceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\API\V1\Activities\AnketLikeController;
use App\Http\Controllers\API\V1\ChatController;
use App\Http\Controllers\API\V1\ImageController;
use App\Http\Controllers\API\V1\OperatorLimitController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\UserController;
use App\Jobs\NewAceJob;
use App\Jobs\TestLogJob;
use App\Mail\SendMail;
use App\Models\Ace;
use App\Models\AceLimit;
use App\Models\AnketWatch;
use App\Models\ChatMessage;
use App\Models\ChatSetting;
use App\Models\CsvUser;
use App\Models\Gift;
use App\Models\Letter;
use App\Models\ProfileType;
use App\Models\PromptTarget;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Events\ObjectNewChatEvent;
use Log;
use Carbon;
use DB;

class TestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-users {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        OperatorLimitController::addOperatorCoolDown(201);


//        $letter = Letter::find(3);
//
//        $letter_messages = $letter->letter_messages()
//            ->with(['sender_user' => function ($query) {
//                $query->select('id', 'name', 'avatar_url_thumbnail');
//            }, 'letter_messageable.sticker', 'letter_messageable.gifts'])
//            ->orderBy('created_at', 'desc')
//            ->paginate(3);
//
//
//        foreach ($letter_messages as $letter_message) {
//            if ($letter_message->letter_messageable_type == 'App\Models\LetterTextMessage') {
//                if (3282 != $letter_message->sender_user_id) {
//                    if ($letter_message->letter_messageable->is_payed == false) {
//                        $letter_message->letter_messageable->text = substr($letter_message->letter_messageable->text, 0, 200);
//                    } else {
//                        foreach ($letter_message->letter_messageable->images as $image) {
//                            if ($image->pivot->is_payed == false) {
//                                $image->image_url = null;
//                                $image->thumbnail_url = null;
//                                $image->big_thumbnail_url = null;
//                                $image->is_payed = false;
//                            } else {
//                                $image->is_payed = true;
//                            }
//                            $image->images_in_letter_id = $image->pivot->id;
//                        }
//                    }
//                } else {
//                    $letter_message->letter_messageable->images = $letter_message->letter_messageable->images;
//                }
//            }
//        }
//
//        echo json_encode($letter_messages);


//        $targetUser = User::find(3241);
//        $userTargets = $targetUser->prompt_interests->pluck('id');
//        echo $userTargets->toJson();
//
//
//        UserController::changeOnline();

//        $user = User::find(209);
//        $user2 = User::find(3234);
//        $user2->addLike($user);
//        echo(AnketLikeController::getResponsibleLikeProbability($user));

//        ChatEvent::dispatch(221, ChatMessage::first(), "fsdfs");


//        $user = User::find(302);
//        $girl = AceController::get_girl_for_ace($user);
//        echo json_encode($girl);

//        $user = User::find(3244);
//        $when = now()->addSeconds(10);
//        AceJob::dispatch($user)->onQueue('queue_ace')->delay($when);

//       echo  AceController::send_chat_message(3244);
//        $user = User::where('email','olya@test')->first();
//        echo json_encode($user->aces);
//
//        $ace=Ace::first();
//        echo json_encode($ace->user);

//        Log::info('Hello, this is a test log!Easy');
//
//        $when = now()->addSeconds(10);
//        TestLogJob::dispatch()->onQueue('queue_name')->delay($when);

//        ImageController::workerForStoreCSVUsers();
//        echo json_encode(AdminController::generateTargets('prompt_careers', '18+', 1, 1));
//
//AdminController::generateTargets('prompt_finance_states',$user->profile_type->name,1,1);
//        $user = new CsvUser();
//        $user->profile_type_id=1;

//        $user = CsvUser::first();
//        echo json_encode($user->profile_type->name);
    }
}
