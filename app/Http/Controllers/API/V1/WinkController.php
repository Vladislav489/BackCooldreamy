<?php

namespace App\Http\Controllers\API\V1;

use App\Events\ObjectNewChatEvent;
use App\Http\Controllers\Controller;
use App\Models\AceLog;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatWinkMessage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Rule;
use PHPUnit\Exception;
use Illuminate\Database\QueryException;

class WinkController extends Controller
{
    public function send_wink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            Rule::exists('users', 'id'),
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $target_user_id = $request->user_id;
        $chat = ChatController::getChat(Auth::user()->id, $target_user_id);
        list($sender_user_id, $recepient_user_id) = ChatController::extracted($chat,Auth::user()->id);
        if (ChatWinkMessage::where('from_user_id', $sender_user_id)->where('to_user_id', $recepient_user_id)->count() > 0) {
            return response()->json(['error' => 'You have already winked at this user'], 401);
        }
        $chat_wink_message = ChatWinkMessage::create([
            'from_user_id' => $sender_user_id,
            'to_user_id' => $recepient_user_id,
        ]);
        $chat_message = new ChatMessage([
            'chat_id' => $chat->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
        ]);

        $chat_wink_message->chat_message()->save($chat_message);

        $chatListItem = ChatController::get_current_chat_list_item($chat->id,Auth::user());
      //  ObjectNewChatEvent::dispatch($recepient_user_id, $chat_message, $chatListItem);

        $another_user = User::find($target_user_id);
        if (!$another_user->is_real) {
            $probability = Setting::where('name', 'wink_limit_probability')->first()->value;
            if (mt_rand(1, 100) <= $probability) {
                OperatorLimitController::addChatLimits($target_user_id, 3);
            }
        }

        return response($chat_wink_message);
    }
}
