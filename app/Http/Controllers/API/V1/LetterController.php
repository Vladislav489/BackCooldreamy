<?php

namespace App\Http\Controllers\API\V1;

use App\Events\AbstractLetterMessageReadEvent;
use App\Events\NewWOperatorLettersEvent;
use App\Events\OperatorLetterMessageReadEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\V1\CreditsController;
use App\Models\Image;
use App\Models\Letter;
use App\Models\LetterGiftMessage;
use App\Models\LetterImageMessage;
use App\Models\LetterMessage;
use App\Models\LetterStickerMessage;
use App\Models\LetterTextMessage;
use App\Models\FavoriteProfile;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Events\LetterEvent;
use DB;

class LetterController extends Controller
{
    public static function get_current_letter_list_item($letter_id, $its_event = false)
    {
        $letter = Letter::query()->with(['firstUser', 'secondUser'])->findOrFail($letter_id);

        $last_message = LetterMessage::with('letter_messageable.gifts', 'letter_messageable.sticker')
            ->where('letter_id', $letter_id)->latest()->first();
        $letter->last_message = $last_message;
        $letter->last_message->letter_messageable = $last_message->letter_messageable;

        $item = [];
        $letter->updated_at = now();
        if ($its_event) {
            $letter->another_user = Auth::user();
        } else {
            if ($letter->first_user_id == Auth::user()->id) {
                $another_user_id = $letter->second_user_id;
            } else {
                $another_user_id = $letter->first_user_id;
            }
            $letter->another_user = User::find($another_user_id);
            $images = $letter->last_message->letter_messageable->images;
            $letter->images = $images;
        }

        $item['letter'] = $letter;

        return $item;
    }

    public function get_my_letter_list()
    {
        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }
        $user = Auth::user();
        $user_id = Auth::user()->id;
        $favorite_users = FavoriteProfile::where('user_id', $user_id)
            ->where('disabled', false)
            ->pluck('favorite_user_id');

        $letter_list = Letter::query()->withCount(['unreadMessages' => function($query) use ($user) {
            $query->where('recepient_user_id', $user->id);
        }])->where(function ($query) use ($user_id) {
            $query->where('first_user_id', $user_id)
                ->orWhere('second_user_id', $user_id);
        })
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        foreach ($letter_list as $letter) {
            $last_message = letterMessage::with('letter_messageable.gifts', 'letter_messageable.sticker')->where('letter_id', $letter->id)->latest()->first();
            $letter->last_message = $last_message;
            if (isset($letter->last_message->letter_messageable)) {
                $letter->last_message->letter_messageable = $last_message->letter_messageable;
            }
            $letter->favorite = ($favorite_users->contains($letter->first_user_id) || $favorite_users->contains($letter->second_user_id)) ? 1 : 0;
            $letter->another_user = $letter->another_user;
        }

        return response($letter_list);
    }

    public function get_letter_with_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required', 'integer',
                Rule::exists('users', 'id'),
            ],
        ]);
        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $target_user_id = $request->user_id;
        $user_id = Auth::user()->id;

        $letter = letter::where(function ($query) use ($user_id, $target_user_id) {
            $query->where(function ($subquery) use ($user_id, $target_user_id) {
                $subquery->where('first_user_id', $user_id)
                    ->where('second_user_id', $target_user_id);
            })
                ->orWhere(function ($subquery) use ($user_id, $target_user_id) {
                    $subquery->where('first_user_id', $target_user_id)
                        ->where('second_user_id', $user_id);
                });
        })->first();
        if (!isset($letter)) {
            $letter = new letter();
            $letter->first_user_id = Auth::user()->id;
            $letter->second_user_id = $target_user_id;
            $letter->save();
        }
        $letter_messages = $this->getletter_messages($letter, $perPage);
        $resp = new \stdClass();
        $resp->letter_messages = $letter_messages;
        $resp->letter_id = $letter->id;
        $resp->another_user = $letter->another_user;

        return response(json_encode($resp));
    }

    public function get_current_letter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'letter_id' => [
                'required',
                Rule::exists('letters', 'id'),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $perPage = 10;
        if (isset($request->per_page)) {
            $perPage = $request->per_page;
        }
        $user_id = Auth::user()->id;
        $letter = Letter::findOrFail($request->letter_id);
        if ($letter->first_user_id !== $user_id && $letter->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to open this letter.'], 401);
        }
        $letter_messages = $this->getletter_messages($letter, $perPage);
        $resp = new \stdClass();
        $resp->letter_messages = $letter_messages;
        $resp->letter_id = $letter->id;
        $resp->another_user = $letter->another_user;

        return response(json_encode($resp));
    }

    public function send_letter_text_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'letter_id' => [
                'required', 'integer',
                Rule::exists('letters', 'id'),
            ],
            'text' => 'max:1800',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user_id = Auth::user()->id;
        $letter = letter::findorfail($request->letter_id);
        if ($letter->first_user_id !== $user_id && $letter->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to post in this letter.'], 401);
        }
        //some billing actions

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(2,"letter", $letter->second_user_id == $user_id ? $letter->first_user_id: $letter->second_user_id);

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        if ($letter->first_user_id == $user_id) {
            $sender_user_id = $user_id;
            $recepient_user_id = $letter->second_user_id;
        } else {
            $sender_user_id = $user_id;
            $recepient_user_id = $letter->first_user_id;
        }
        $images = json_decode($request->input('images'));


        $letter_text_message = letterTextMessage::create(['text' => $request->text]);
        if (isset($images)) {
            foreach ($images as $image) {
                $image = Image::findorfail($image);
                $letter_text_message->images()->attach($image);
            }
        }

        $letter_message = new letterMessage([
            'letter_id' => $letter->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
        ]);
        $letter_text_message->letter_message()->save($letter_message);
        $letter_message->letter_messageable = $letter_message->letter_messageable;
//        letterEvent::dispatch($recepient_user_id, $letter_message);


        if (!User::find($recepient_user_id)->is_real) {
            OperatorLetterLimitController::addLetterLimits($recepient_user_id, OperatorLetterLimitController::SEND_MESSAGE, $letter->id);
        }
        //letterEvent::dispatch($recepient_user_id, $letter_message, $letter);
        $this->sendOperatorEvent($recepient_user_id, $letter_message, $letter);

        return (self::get_current_letter_list_item($request->letter_id));
//        return response($letter_text_message);
    }


    /**
     * @param $recepient_user_id
     * @param $letter_message
     * @param $letterListItem
     */
    private function sendOperatorEvent($recepient_user_id, $letter_message, $letterListItem)
    {
        $recepient = User::findOrFail($recepient_user_id);
        if ($recepient->is_real == false) {
            if ($recepient->operator) {
                $operator = $recepient->operator->operator;
                if ($operator) {
                   // NewWOperatorLettersEvent::dispatch($operator->id, $letter_message, $letterListItem);
                    $letter = $letter_message->letter;
                    $letter->is_answered_by_operator = false;
                    $letter->updated_at = now();
                    $letter->save();
                }
            }
        }
    }

    public function send_letter_image_message(Request $request){
        $validator = Validator::make($request->all(), [
            'letter_id' => [
                'required', 'integer',
                Rule::exists('letters', 'id'),
            ],
            'thumbnail_url' => 'required|string|max:255',
            'image_url' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user = Auth::user();

        $user_id = $user->id;
        $letter = Letter::findorfail($request->letter_id);
        if ($letter->first_user_id !== $user_id && $letter->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to post in this chat.'], 401);
        }
        if ($letter->first_user_id == Auth::user()->id) {
            if ($letter->deleted_by_first_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        } else {
            if ($letter->deleted_by_second_user) {
                return response()->json(['error' => 'chat is deleted'], 404);
            }
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(3,"chat",$letter->second_user_id == $user->id ? $letter->first_user_id: $letter->second_user_id);

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        list($sender_user_id, $recepient_user_id) = $this->extracted($letter, $user_id);

        $letter_image_message = LetterImageMessage::create([
            'image_url' => $request->image_url,
            'thumbnail_url' => $request->thumbnail_url,
        ]);
        $countImages = LetterMessage::query()->where('chat_id', $letter->id)->where('chat_messageable_type', LetterImageMessage::class)->count();
        if ($user->gender == 'female' && $countImages >= Letter::COUNT_FREE_IMAGES) {
            $isPayed = false;
        } else {
            $isPayed = true;
        }
        $letter_message = new LetterMessage([
            'chat_id' => $letter->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
            'is_payed' => $isPayed
        ]);
        $letter_image_message->letter_message()->save($letter_message);

        $letter_message->letter_messageable = $letter_message->letter_messageable;
        $letterListItem = self::get_current_letter_list_item($request->chat_id, true);
       // LetterEvent::dispatch($recepient_user_id, $letter_message, $letterListItem['letter']);
        $this->setChatAnswered($letter);
        $this->sendOperatorEvent($recepient_user_id, $letter_message, $letterListItem['letter']);
        //OperatorLimitController::addChatLimits($recepient_user_id, 9, $letter->id);
        return (self::get_current_letter_list_item($request->letter_id));
//        return response($chat_image_message);
    }

    public function send_letter_sticker_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'letter_id' => [
                'required', 'integer',
                Rule::exists('letters', 'id'),
            ],
            'sticker_id' => [
                'required', 'integer',
                Rule::exists('stickers', 'id'),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user_id = Auth::user()->id;
        $letter = letter::findorfail($request->letter_id);
        if ($letter->first_user_id !== $user_id && $letter->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to post in this letter.'], 401);
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(15,"letter", $letter->second_user_id == $user_id ? $letter->first_user_id: $letter->second_user_id);

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        //some billing actions
        if ($letter->first_user_id == $user_id) {
            $sender_user_id = $user_id;
            $recepient_user_id = $letter->second_user_id;
        } else {
            $sender_user_id = $user_id;
            $recepient_user_id = $letter->first_user_id;
        }

        $letter_sticker_message = letterStickerMessage::create(['sticker_id' => $request->sticker_id]);
        $letter_message = new letterMessage([
            'letter_id' => $letter->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
        ]);
        $letter_sticker_message->letter_message()->save($letter_message);
        $letter_message->letter_messageable->sticker = $letter_message->letter_messageable->sticker;
        //letterEvent::dispatch($recepient_user_id, $letter_message, $letter);
        $this->sendOperatorEvent($recepient_user_id, $letter_message, $letter);
        return (self::get_current_letter_list_item($request->letter_id));
    }

    public function send_letter_gift_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'letter_id' => [
                'required', 'integer',
                Rule::exists('letters', 'id'),
            ],
            'gifts' => [
                'required',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $user_id = Auth::user()->id;
        $letter = letter::findorfail($request->letter_id);
        if ($letter->first_user_id !== $user_id && $letter->second_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized to post in this letter.'], 401);
        }
        //some billing actions
        if ($letter->first_user_id == $user_id) {
            $sender_user_id = $user_id;
            $recepient_user_id = $letter->second_user_id;
        } else {
            $sender_user_id = $user_id;
            $recepient_user_id = $letter->first_user_id;
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(14,"letter", $letter->second_user_id == $user_id ? $letter->first_user_id: $letter->second_user_id);

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        $gifts = json_decode($request->input('gifts'));
        if (!isset($gifts)) {
            return response()->json(['error' => 'Not correct gifts id array'], 500);
        }

        $letter_gift_message = letterGiftMessage::create();
        foreach ($gifts as $item) {
            $gift = Gift::findorfail($item);
            $letter_gift_message->gifts()->attach($gift);
        }
        $letter_message = new letterMessage([
            'letter_id' => $letter->id,
            'sender_user_id' => $sender_user_id,
            'recepient_user_id' => $recepient_user_id,
        ]);
        $letter_gift_message->letter_message()->save($letter_message);
        $letter_message->letter_messageable->gifts = $letter_message->letter_messageable->gifts;
        //letterEvent::dispatch($recepient_user_id, $letter_message, $letter);
        $this->sendOperatorEvent($recepient_user_id, $letter_message, $letter);
        return (self::get_current_letter_list_item($request->letter_id));
    }

    public function set_letter_message_is_read(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'letter_message_id' => [
                'required', 'integer',
                Rule::exists('letter_messages', 'id'),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }
        $user_id = Auth::user()->id;
        $letter_message = letterMessage::where('id', $request->letter_message_id)->where('is_read_by_recepient', false)->firstorfail();
        if ($letter_message->recepient_user_id !== $user_id) {
            return response()->json(['error' => 'You are not authorized for this action.'], 401);
        }
        $letter_message->is_read_by_recepient = true;
        $letter_message->save();
        //AbstractLetterMessageReadEvent::dispatch($letter_message->sender_user_id, $letter_message->letter_id, $letter_message->id);
        $sender = User::findOrFail($letter_message->sender_user_id);
        if ($sender->is_real == false) {

            OperatorLetterLimitController::addLetterLimits($sender->id, OperatorLetterLimitController::OPEN, $letter_message->letter_id);
            if ($sender->operator) {
                $operator = $sender->operator->operator;
                //if ($operator) {
                 //   OperatorLetterMessageReadEvent::dispatch($letter_message->sender_user_id, $letter_message->letter_id, $letter_message->id);
               // }
            }
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function getletter_messages(letter $letter, $perPage = 10)
    {
        $letter_messages = $letter->letter_messages()
            ->with(['sender_user' => function ($query) {
                $query->select('id', 'name', 'avatar_url_thumbnail');
            }, 'letter_messageable.sticker', 'letter_messageable.gifts'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);


        foreach ($letter_messages as $letter_message) {

            if ($letter_message->letter_messageable_type == 'App\Models\LetterTextMessage') {
                if (Auth::user()->id != $letter_message->sender_user_id) {
                    if ($letter_message->letter_messageable->is_payed == false) {
                        $letter_message->letter_messageable->text = substr($letter_message->letter_messageable->text, 0, 200);
                    } else {
                        foreach ($letter_message->letter_messageable->images as $image) {
                            if ($image->pivot->is_payed == false) {
                                $image->image_url = null;
                                $image->thumbnail_url = null;
                                $image->big_thumbnail_url = null;
                                $image->is_payed = false;
                            } else {
                                $image->is_payed = true;
                            }
                            $image->images_in_letter_id = $image->pivot->id;
                        }
                    }
                } else {
                    $letter_message->letter_messageable->images = $letter_message->letter_messageable->images;
                }
            }
        }

        return $letter_messages;
    }

    public function pay_for_letter_text_message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'letter_text_message_id' => [
                'required', 'integer',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(8,"letter");

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        $letter_text_message = LetterTextMessage::findOrFail($request->letter_text_message_id);
        $letter_text_message->is_payed = true;
        $letter_text_message->save();

        $images = $letter_text_message->images;

        if (isset($images)) {
            foreach ($images as $image) {
                if ($image->pivot->is_payed == false) {
                    $image->image_url = null;
                    $image->thumbnail_url = null;
                    $image->big_thumbnail_url = null;
                    $image->is_payed = false;
                } else {
                    $image->is_payed = true;
                }
                $image->images_in_letter_id = $image->pivot->id;
                $letter_text_message->images[] = $image;
            }
        }

        return response($letter_text_message);
    }

    public function pay_for_letter_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_in_letter_id' => [
                'required', 'integer',
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 500);
        }

        $credits = new CreditsController();
        $resultCheckPayment = $credits->check_payment(9,"letter");

        if(is_object($resultCheckPayment)) {
            return $resultCheckPayment;
        }

        DB::table('images_in_letters')->where('id', $request->image_in_letter_id)
            ->update(['is_payed' => true]);
        $image_id = DB::table('images_in_letters')->where('id', $request->image_in_letter_id)->first()->image_id;
        return response(Image::findorfail($image_id));
    }

}
