<?php

namespace App\Http\Controllers;

use App\Events\ObjectNewChatEvent;
use App\Http\Controllers\API\V1\ChatController;
use App\Models\Ace;
use App\Models\AceLimit;
use App\Models\AceProbabilityByAceType;
use App\Models\AceProbabilityByAnketType;
use App\Models\Chat;
use App\Models\ChatImageMessage;
use App\Models\ChatMessage;
use App\Models\ChatTextMessage;
use App\Models\ListOfGreeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Log;

class AceController extends Controller
{
    public static function send_chat_message($target_user, $ace_user, $text)
    {
        $user = $ace_user;
        $chat = ChatController::getChat($user->id, $target_user->id);
        $user_id = $user->id;
        if ($chat->first_user_id !== $user_id && $chat->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
        }

        list($sender_user_id, $recepient_user_id) = ChatController::extracted($chat, $user_id);
        $chat_text_message = ChatTextMessage::create(['text' => $text]);
        $chat_message = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
            'is_ace' => 1
        ]);

        $chat_text_message->chat_message()->save($chat_message);
        $chat_message->chat_messageable = $chat_message->chat_messageable;

        $chatListItem = ChatController::get_current_chat_list_item($chat->id, $target_user, false, true, $ace_user);
        //ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem);

        return $chat_message;
    }

    public static function get_girl_for_ace(User $man_user)
    {
        $probabilites = AceProbabilityByAnketType::all();
        $random_number = rand() / getrandmax();
        $selected_category_id = null;
        $current_probability = 0;

        foreach ($probabilites as $probabilite) {
            $current_probability += $probabilite->probability;
            if ($random_number <= $current_probability) {
                $selected_category_id = $probabilite->type_id;
                break;
            }
        }

        $girl = User::select('users.*')
            ->whereIn('users.gender', ['female'])
            ->where('users.is_real', false)
            ->where('users.profile_type_id', $selected_category_id)
//            ->whereExists(function ($query) {
//                $query->select(DB::raw(1))
//                    ->from('aces')
//                    ->where('email', DB::raw('users.email'));
//            })
            ->whereNotExists(function ($query) use ($man_user) {
                $query->select(DB::raw(1))
                    ->from('ace_logs')
                    ->where([
                        ['to_user_id', '=', $man_user->id],
                        ['from_user_id', '=', DB::raw('users.id')]
                    ]);
            })
            ->inRandomOrder()->first();

        if (!$girl) {
            $girl = User::select('users.*')
                ->whereIn('users.gender', ['female'])
                ->where('users.is_real', false)
//                ->whereExists(function ($query) {
//                    $query->select(DB::raw(1))
//                        ->from('aces')
//                        ->where('email', DB::raw('users.email'));
//                })
                ->whereNotExists(function ($query) use ($man_user) {
                    $query->select(DB::raw(1))
                        ->from('ace_logs')
                        ->where([
                            ['to_user_id', '=', $man_user->id],
                            ['from_user_id', '=', DB::raw('users.id')]
                        ]);
                })->inRandomOrder()->first();
        }
        //todo only test
        if (!$girl) {
            $girl = User::select('users.*')
                ->whereIn('users.gender', ['female'])
                ->where('users.is_real', false)
//                ->whereExists(function ($query) {
//                    $query->select(DB::raw(1))
//                        ->from('aces')
//                        ->where('email', DB::raw('users.email'));
//                })
                ->first();
        }

        return $girl;
    }

    /**
     * @param User $user
     * @return mixed
     */
    public static function initialization_ace_limit_for_user(User $user)
    {
        return AceLimit::create([
            'user_id' => $user->id,
            'current_random_second' => 30,
            'ace_limit' => 10,
        ]);
    }

    public static function getAce(User $targetUser, User $girl)
    {
        if ($targetUser->is_confirmed_user) {
            $probabilites = AceProbabilityByAceType::where('profile_type_id', $girl->profile_type_id)->get();
            $random_number = rand() / getrandmax();
            $ace_type_id = null;
            $current_probability = 0;

            foreach ($probabilites as $probabilite) {
                $current_probability += $probabilite->probability;
                if ($random_number <= $current_probability) {
                    $ace_type_id = $probabilite->ice_type;
                    break;
                }
            }

            if ($ace_type_id <= 4) {
                $ace = Ace::where('message_type_id', $ace_type_id)->inRandomOrder()->first();
            }

            if ($ace_type_id == 5) {
                $userTargets = $targetUser->prompt_interests->pluck('id');
                $ace = Ace::where('message_type_id', $ace_type_id)
                    ->whereIn('target_id', $userTargets)
                    ->inRandomOrder()->first();
            }

            if ($ace_type_id == 6) {
                $userTargets = $targetUser->prompt_targets->pluck('id');
                $ace = Ace::where('message_type_id', $ace_type_id)
                    ->whereIn('target_id', $userTargets)
                    ->inRandomOrder()->first();
            }

        } else {
            $ace = Ace::where('message_type_id', 1)->inRandomOrder()->first();
        }

        if ($ace) {
            $ace->text = str_replace('<name>', $targetUser->name, $ace->text);
            $greeting = ListOfGreeting::inRandomOrder()->first();
            $ace->text = str_replace('<hi>', $greeting->text, $ace->text);
        }

        return $ace;
    }
}
