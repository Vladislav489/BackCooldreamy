<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Auth\AuthLogTypeEnum;
use App\Enum\Operator\WorkingShiftStatusEnum;
use App\Events\UpdateNotificationEvent;
use App\Jobs\ASdDataJob;
use App\Jobs\NewAceJob;
use App\Jobs\OperatorLimitJob;
use App\Jobs\ProcessPodcast;
use App\Mail\ResetPasswordMail;
use App\Mail\VerificationMail;
use App\ModelAdmin\CoreEngine\LogicModels\Ace\AceLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Limit\LimitChatOperator;
use App\ModelAdmin\CoreEngine\LogicModels\Limit\LimitChatOperatorLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorWorkingShiftLogic;
use App\ModelAdmin\CoreEngine\LogicModels\User\UserCooperationLogic;
use App\ModelAdmin\CoreEngine\LogicModels\UserLogic;
use App\Models\Auth\UsersTokenFireBase;
use App\Models\Chat;
use App\Models\Operator\WorkingShiftLog;
use App\Models\Promotion;
use App\Models\StatisticSite\RoutingUser;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\UserPromotion;
use App\Repositories\Auth\AuthLogRepository;
use App\Repositories\Geo\UserGeoRepository;
use App\Repositories\Operator\WorkingShiftRepository;
use App\Services\Geo\GeoRequest;
use App\Services\Operator\WorkingShiftService;
use App\Traits\ImageStoreTrait;
use GuzzleHttp\Client;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Hash;
use Illuminate\Support\Str;

use Psr\Log\LoggerInterface;

class AuthController extends Controller
{
    /** @var UserGeoRepository */
    private UserGeoRepository $userGeoRepository;

    /** @var AuthLogRepository */
    private AuthLogRepository $authLogRepository;

    public function __construct(UserGeoRepository $userGeoRepository, AuthLogRepository $authLogRepository)
    {
        $this->userGeoRepository = $userGeoRepository;
        $this->authLogRepository = $authLogRepository;
    }

    public function testJob()
    {
        OperatorLimitJob::dispatch(User::first())->onQueue('default')->delay(1);
    }

    /**
     * Check image
     */
    public function urlStatistic(Request $request)
    {
        if (isset($request->user_id)) {
            return RoutingUser::saveUrl($request->data_url_statistic, $request->user_id);
        }
        return RoutingUser::saveUrl($request->data_sratistic);
    }

    public function checkImage(Request $request)
    {
//        $file = $request->file('file');
//
//        if (!$file) {
//            return response()->json(['message' => 'Файл не был передан']);
//        }
//
//        // Сохраняем файл в storage
//        $file_path = $file->store('files');
//
//        $url = 'https://smarty.mail.ru/api/v1/objects/detect?oauth_token=5SD85LsaWJzZBgiZAdBsRZ964E8b9H7u4bRqegJqKYna9kLPh&oauth_provider=mcs';
//
//        $file_mime_type = $file->getMimeType();
//        $file_name = $file->getClientOriginalName();
//
//        $meta = [
//            'mode' => ['scene', 'multiobject', 'pedestrian'],
//            'images' => [
//                ['name' => 'file']
//            ]
//        ];
//
//        $data = [
//            'file' => new \CURLFile(storage_path('app/'.$file_path), $file_mime_type, $file_name),
//            'meta' => json_encode($meta)
//        ];
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Accept: application/json',
//            'Content-Type: multipart/form-data'
//        ));
//
//        $response = curl_exec($ch);
//
//        if(curl_errno($ch)) {
//            return 'Ошибка отправки запроса: ' . curl_error($ch);
//        }
//
//        curl_close($ch);
//
//        // Удаляем файл из storage
//        Storage::delete($file_path);
//
//        $data = json_decode($response, true);
//
//        if (Arr::get($data, 'body')) {
//            foreach (Arr::get($data, 'body', []) as $items) {
//                foreach ($items as $item) {
//                    foreach (Arr::get($item, 'labels', []) as $label) {
//                        if (Arr::get($label, 'eng') == 'Person') {
        return response()->json(['message' => 'success']);
//                        }
//                    }
//                }
//            }
//        }
//
//        abort(403);
//        return;
    }

    /**
     * @return JsonResponse
     */
    public function geoLocation(): JsonResponse
    {
        $geo = (new GeoRequest)->getIp()->infoByIp();

        return response()->json($geo);
    }

    public function sendVerification(Request $request ){
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => 'required',
                'token' => 'required'
            ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
       $user = User::find($request->get('user_id'));
       if($user->token != $request->get('token'))
           return response()->json(['error' => "token not valid!!!!"], 401);

    //    dd($user,$request->all());
        return response()->json([
            'token' => $user->createToken('auth_token', ['subscriber'])->plainTextToken,
            'id' => $user->id,
        ]);
    }

   public function sendVerificationMail(){
       $user = User::find(Auth::id());
       Mail::to($user)->send(new VerificationMail($user->token, $user));
   }

    public function register(Request $request){
        try {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'gender' => 'required',
                'birthday' => 'required'
            ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

            $dataUser = $request->all();
            if(isset($dataUser['about']))
                $dataUser['about_self'] = $dataUser['about'];



            $user = User::registrationClient($dataUser);
            if(isset($dataUser['file'])) {
                ImageStoreTrait::store_image_content_base_64($user, $dataUser['file'], 1, $dataUser['gender']);
            }

            if(isset($dataUser['subid'])  && isset($dataUser['app_name'])) {

                $userCooperation  = [
                    'user_id' => $user->id,
                    'subid' =>$dataUser['subid'],
                    'app_name' =>$dataUser['app_name']
                ];
                    if(isset($dataUser['af_id']))
                        $userCooperation['af_id'] = $dataUser['af_id'];

                if($dataUser['gender'] =="male" && $dataUser['search_gender'] == "female") {
                    User\UserCooperation::create($userCooperation);
                }
            }
            $token = $user->createToken('auth_token', ['subscriber'])->plainTextToken;
            /* try {
                  $geo = (new GeoRequest)->getIp()->infoByIp();
                  $this->userGeoRepository->store([
                      'user_id' => $user->id,
                      'ip' => $geo->userIp,
                      'city' => $geo->city,
                      'state' => $geo->state,
                      'country' => $geo->country,
                      'country_code' => $geo->countryCode,
                      'continent' => $geo->continent,
                      'continent_code' => $geo->continentCode
                  ]);
              }catch (\Throwable $e){

              }*/
            UserPromotion::create([
                'user_id' => $user->id,
                'promotion_id' => Promotion::query()->where('activation_type_id', 1)->first()->id,
                'status' => 'new'
            ]);
            //if(strpos($dataUser,'@gmail.com')!==false) {
              //  Mail::to($user)->send(new VerificationMail($user->token, $user));
           // }
           // $user->update(['is_email_verified' => (strpos($dataUser,'@gmail.com') !== false) ?0:1]);
            $this->authLogRepository->logAuth($user, AuthLogTypeEnum::REG);
       /// OperatorLimitJob::dispatch($user)->onQueue('default')->delay(1);
            return response()->json(['token' => $token, 'id' => $user->id,], 200);
        }catch (\Throwable $e){
            var_dump($e->getMessage(),$e->getTraceAsString());
        }
    }

    public function checkEmail(Request $request){
        $validator = Validator::make(
            $request->all(),
            ['email' => 'required|email|unique:users,email']);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
    }

    public function setAce(){
        $user = Auth::user();
        if ($user->gender != 'female') {
            $res = (new AceLogic())->addNewUser($user);
        }
    }


    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();
        $user->online = false;
        $user->save();
        $this->authLogRepository->logAuth($user, AuthLogTypeEnum::LOGOUT);

        return response()->json(['message' => 'success']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setInfo(Request $request)
    {
        $data = $request->only('country', 'state', 'language');
        $user = Auth::user();

        if ($country = Arr::get($data, 'country'))
            $user->country = $country;

        if ($state = Arr::get($data, 'state'))
            $user->state = $state;

        if ($language = Arr::get($data, 'language'))
            $user->language = $language;

        $user->save();
        return response()->json(['message' => 'success']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function addTokenFireBase(Request $request)
    {
        $validator = Validator::make($request->all(),
            [   'toket_fireBase' => 'required',]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        /** @var User $user */
        $user = Auth::user();
        if (isset($user->id) && !empty($user->id) ) {
            try {
               $userToken = UsersTokenFireBase::query()->where("user_id",$user->id)->first();
               if(is_null($userToken)){
                   UsersTokenFireBase::create([
                       'user_id' => $user->id,
                       'token_fire_base' => $request->toket_fireBase,
                   ]);
               }else{
                   $userToken->token_fire_base = $request->toket_fireBase;
                   $userToken->save();
               }


            }catch (\Throwable $e){
                dd($e->getMessage(),$e->getTraceAsString());
                return response()->json(['message' => 'error'], 500);
            }
            return response()->json(['message' => 'success']);
        } else {
            return response()->json(['message' => 'User not  Auth'], 401);
        }

        return response()->json(['message' => 'error'], 500);
    }





    public function sendCodeResetPassword(Request $request){
        $validator = Validator::make(
            $request->all(), [
                'email' => 'required|email',
            ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = new UserLogic(['email'=>$request->get('email')]);
        if($user->Exist()){
            $token = (rand(1,9).rand(1,9).rand(1,9).rand(1,9).rand(1,9).rand(1,9).rand(1,9));
            User\PasswordReset::insert([
                'email'=> $request->get('email'),
                'token' => $token,
                'created_at'=> now()
            ]);

            $userData =  $user->getOne();
            Mail::to($request->get('email'))->send(new ResetPasswordMail($token,$userData['name'] ));
            return  response()->json(['message' => 'success']);
        }
        return response()->json(['message' => 'this email is missing'], 500);
    }

    public function resetPassword(Request $request){
        $validator = Validator::make(
            $request->all(), [
            'email' => 'required|email',
            'code' =>  'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

      $rez =  User\PasswordReset::query()->where("email","=",$request->get('email'))
                                   ->where("token","=",$request->get('code'))
                                   ->orderBy('created_at','desc')->get()->first();

       if(!is_null($rez)) {
           $user = User::query()->where("email", "=", $request->get('email'))->get()->first();

         return  response()->json([
             'user'=> $user,
             'token' => $user->createToken('auth_token', ['subscriber'])->plainTextToken,
             'id' => $user->id,
             'role'=>$user->getRoleNames()->toArray()]);
       }else{
           return response()->json(['error' => 'code do not valid!!!'], 401);
       }
    }
    public function passChange(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
                'password_confirmation' => 'min:6',
            ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        if(!is_null(Auth::id())) {
            $user = User::find(Auth::user()->id);
            $user->password = \Illuminate\Support\Facades\Hash::make($request->get('password'));
            $user->save();
            return response()->json($user);
        }
        return response()->json(['error' => 'User do not login!!!!!'], 401);
    }


    public function verify(Request $request){
        /** @var User $user */
        $user = Auth::user();
        if ($user->token == $request->token) {
            $user->is_email_verified = true;
            $user->save();

            return response()->json(['message' => 'success']);
        }
        return response()->json(['message' => 'error'], 500);
    }

    public function pwaSet(Request $request){
           $user =  User::find(Auth::id());
           $user->is_pwa = 1;
           $user->save();
           return $user;
    }

    /**
     * @return JsonResponse
     */
    public function setAces(): JsonResponse {
        $user = Auth::user();
        if ($user->gender != 'female') {
            if(!empty($user->about_self)) {
                $res = (new AceLogic())->addNewUser($user);
                (new LimitChatOperatorLogic())->addRegistaration($user);
            }
        }
        if($res)
            return response()->json(['message' => 'success'], 200);
        else
            return response()->json(['message' => 'error'], 200);

    }

    public function token(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 401);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->getPasswordAttribute()))
            return response()->json(['error' => 'The provided credentials are incorrect.'], 401);

        if((new OperatorLogic(['operator'=>(string)$user->id,'role'=>'2']))->Exist()) {
           $count = count((new OperatorLogic(['operator' => (string)$user->id, 'is_work_operator' => '2']))->offPagination()->setJoin(['Ancet'])->getOne());
           if ($count> 1 ) {
                return response()->json(['error' => 'Another operator work to your accounts.'], 401);
           }
           $user->online = true;
           $user->save();
        }

       if ($user->is_blocked)
            return response()->json(['error' => 'You are blocked.'], 403);

        //(new LimitChatOperatorLogic())->changeUserGroup($user,2);
        $this->authLogRepository->logAuth($user, AuthLogTypeEnum::AUTH);
        OperatorLimitJob::dispatch($user)->onQueue('default')->delay(1);
        return response()->json([
            'token' => $user->createToken('auth_token', ['subscriber'])->plainTextToken,
            'id' => $user->id,
            'role'=>$user->getRoleNames()->toArray()
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse {
        $user = Auth::user();
        $data = Chat::query()->where('second_user_id', $user->id)->orWhere('first_user_id', $user->id)->with(['chat_messages' => function($query) use ($user) {
            $query->where('recepient_user_id', $user->id)->where('is_read_by_recepient', 0);
        }])->whereHas('chat_messages', function ($query) use ($user) {
            $query->where('recepient_user_id', $user->id)->where('is_read_by_recepient', 0);
        })->distinct()->get();

        $chats = array();
        foreach ($data as $chat) {
            array_push($chats, ['chat_id' => $chat->id, 'unread_messages' => $chat->chat_messages->count()]);
        }

        return response()->json([
            'my_watchers' => $user->myWatchers()->where('is_read', false)->count(),
            'liked_me' => $user->liked_me()->where('is_read', false)->count(),
            'feeds_me' => $user->feeds_users()->where('is_read', false)->count(),
            'mutual_likes' => $user->MutualLikedUsers()->where('is_read', false)->count(),
            'chats' => $chats
        ]);
    }

    /**
     * @param $type
     * @return JsonResponse
     */
    public function readNotifications($type): JsonResponse
    {
        $user = Auth::user();
        if ($type == 'my_watchers') {
            $user->myWatchers()->update(['is_read' => true]);
        } else if ($type == 'liked_me')  {
            $user->liked_me()->update(['is_read' => true]);
        } else if ($type == 'feeds_me') {
            $user->feeds_users()->update(['is_read' => true]);
        } else if ($type == 'mutual_likes') {
            $user->MutualLikedUsers()->update(['is_read' => true]);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * @return JsonResponse
     */
    public function resendEmail()
    {
        $user = Auth::user();
        if ($user->email_limit > 0) {
            $user->token = Str::uuid();
            $user->email_limit = $user->email_limit - 1;
            $user->save();
           // Mail::to("ryzhakovalexeynicol@gmail.com")->queue(new MyVerificationMail($user->token, $user->id));

            return response()->json(['message' => 'success']);
        }

        return response()->json(['error' => 'You dont have limits'], 403);
    }

    public function testAmplitude()
    {

        $apiUrl = 'https://api2.amplitude.com/2/httpapi';
        $apiKey = '14346ea0baafc45f8d0e8e80768d49d2';

        $client = new Client();

        $response = $client->post($apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => '*/*',
            ],
            'json' => [
                'api_key' => $apiKey,
                'events' => [
                    [
                        'device_id' => User::first()->id . '@cooldreamy.comtesttesttesttest',
                        'event_type' => 'New Login',
                    ],
                ],
            ],
        ]);

        $body = $response->getBody();
        $data = json_decode($body, true);

        return response()->json($data);
    }
}
