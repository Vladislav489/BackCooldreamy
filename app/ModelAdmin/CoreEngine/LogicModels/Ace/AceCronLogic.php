<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\Ace;


use App\Events\LetChatMessageNewReadEvent;
use App\Events\SympathyEvent;
use App\Mail\MessageUserMail;
use App\Models\Ace;
use App\Models\AceProbabilityByAceType;
use App\Models\AceProbabilityByAnketType;
use App\Models\AceSystems\AceSystemLimitAssignment;
use App\Models\AnketWatch;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\ChatTextMessage;
use App\Models\FavoriteProfile;
use App\Models\Feed;
use App\Models\ListOfGreeting;
use App\Services\FireBase\FireBaseService;
use App\Services\OneSignal\OneSignalService;
use App\Services\Probability\AnketProbabilityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class AceCronLogic extends AceLogic  {
    public function __construct($params = [],$select = ["*"],$callback = null){
        parent::__construct($params,$select);
        $this->getFilter();
        $this->compileGroupParams();
    }



    public function rangeProbabilites(array $probabilites,$target){
        $range = [];
        foreach ($probabilites as $key => $probabilite) {
            if(count($range)){
                if($probabilites[$key]['probability'] > 0)
                    $range[] = [
                       // 'orig'=>$probabilites[$key],
                        'target'=>$probabilites[$key][$target],
                        'start' => $range[count($range)-1]['end']+1,
                        'end' => ($probabilites[$key]['probability']*100+$range[count($range)-1]['end'])
                    ];
            }
            if(!count($range)) {
                if($probabilites[$key]['probability'] > 0)
                    $range[] = [
                      //  'orig'=>$probabilites[$key],
                        'target'=>$probabilites[$key][$target],
                        'start' => 1,
                        'end' => ($probabilites[$key]['probability']*100)
                    ];
            }

        }
        return $range;
    }

    public function probabiliteProfil(array $probabilites){
        $probabilitesNew = $this->rangeProbabilites($probabilites,'type_id');
        $random_number = rand() / getrandmax();
        $selected_category_id = null;
        $current_probability = 0;
        foreach ($probabilitesNew as $probabilite) {
            if ($probabilite['start'] <= $random_number ||$random_number  <= $probabilite['end']) {
                $selected_category_id = $probabilite['target'];
                break;
            }
        }
        return $selected_category_id;
    }
    public function getAncetForUser(array $user_ids){
       try {
           $probabilites = AceProbabilityByAnketType::get()->toArray();
           $sql = [];
           $newArr = [];
           foreach ($user_ids   as $key => $id){
               $profile_type_id = $this->probabiliteProfil($probabilites);
               $offset = rand(1,4);
               $randOrder = rand(0,7);
               $randOrderType = rand(0,1);
               $orderType = ['asc','desc'];
               $order = ['Girl.id','Girl.name','Girl.birthday','Girl.state','Girl.country','Girl.email','Girl.online','Girl.updated_at'];

               $sql[] =  "SELECT (
                   SELECT  Girl.id  FROM users AS Girl  WHERE
                    Girl.id NOT IN((SELECT ch1.first_user_id AS user_id FROM chats AS ch1 WHERE ch1.second_user_id = Main.id UNION
                        SELECT ch2.second_user_id AS user_id FROM chats as ch2 WHERE   ch2.first_user_id = Main.id))
                        AND Girl.is_real = 0
                                   AND Girl.profile_type_id = {$profile_type_id}
                                   AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),Main.birthday)), '%Y-m') + 20  >= DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),Girl.birthday)), '%Y-m') + 0
                                   AND DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),Main.birthday)), '%Y-m')  -15  <=  DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(),Girl.birthday)), '%Y-m') + 0
                        ORDER BY {$order[$randOrder]} {$orderType[$randOrderType]}  LIMIT {$offset},1
                    ) AS sendTo , Main.id,Main.name   FROM users AS Main  WHERE  Main.id = {$id} ";
               $newArr[$id] = ['id'=>$id, 'profile_type_id'=>$profile_type_id];
           }
           $result = DB::select(implode("\n UNION \n", $sql));
           $result = json_decode(json_encode($result),true);

           foreach ($result as $item){
               $newArr[$item['id']]['send_user'] = $item['sendTo'];
               $newArr[$item['id']]['name'] = $item['name'];
           }
           return $newArr;

       }catch (\Throwable $e){
           var_dump($e->getMessage(),$e->getFile(),$e->getLine());
       }
    }

    public function getAceForAncet($list){

        $probabilites = AceProbabilityByAceType::query()->whereIn('profile_type_id',[1,2,3,4])->get()->toArray();
        $counttype_id = Ace::query()->select(DB::raw("COUNT(*) as count  ,message_type_id"))->groupBy('message_type_id')->get()->toArray();
        $counttype_idN = [];
        foreach ($counttype_id as $key => $item){
            $counttype_idN[$item['message_type_id']] = $item['count'];
        }
        foreach ($list as $key => $item ) {
            $profile_type_id = $item['profile_type_id'];
            $probabilitesFilte = array_filter($probabilites, function ($value) use ($profile_type_id) {
                return $value['profile_type_id'] == $profile_type_id;
            });
            $probabilitesNew = $this->rangeProbabilites($probabilitesFilte,'ice_type');

            $random_number = rand() / getrandmax();
            $list[$key]['ace_type_id'] = null;
            foreach ($probabilitesNew as $probabilite) {
                if ($probabilite['start'] <= $random_number || $random_number  <= $probabilite['end']) {
                    $list[$key]['ace_type_id'] = $probabilite['target'];
                    break;
                }
            }
            $offset  = rand(1,$counttype_idN[$profile_type_id]-1);
            $ace = Ace::where('message_type_id', $list[$key]['ace_type_id'])->offset($offset)
                ->limit(1)->get()->toArray();
            $ace = (count($ace))?$ace[0]:null;
            if ($ace) {
            $list[$key]['text_ace'] = $ace['text'];
                $ace['text'] = str_replace('<name>',   $list[$key]['name'], $ace['text']);
                $greeting = ListOfGreeting::inRandomOrder()->first();
                $list[$key]['text_ace'] =  str_replace('<hi>', $greeting->text, $ace['text']);
            }else{
                $list[$key]['text_ace'] =  null;
            }
        }
        return $list;
    }

    private function getChat($list){
        $query = [];
        foreach ($list as $item){
            $query[] = "SELECT * FROM chats WHERE first_user_id = {$item['send_user']} AND second_user_id = {$item['id']}";
        }
        $result = DB::select(implode("\n UNION \n", $query));
        $result = json_decode(json_encode($result),true);
        foreach ($list as $key => $data ) {
            foreach ($result as $chat) {
                if($chat['first_user_id'] ==  $list[$key]['send_user'] && $chat['second_user_id'] == $list[$key]['id']){
                    $list[$key]['chat_id'] = $chat['id'];
                }
            }
        }
        return $list;
    }
    public function createChat($list){
     try {
         $dataInsChat = [];
         foreach ($list as $item) {
             $dataInsChat[] = [
                 'first_user_id' => $item['send_user'],
                 'second_user_id' => $item['id'],
                 'uuid' => Str::uuid(),
                 'created_at' => now(),
                 'updated_at' => now()
             ];
         }
         Chat::insert($dataInsChat);
         $list = $this->getChat($list);
         $addViewedUser =   $favorite = $like = [];
         foreach ($list as $item) {
             $chat_text_message = ChatTextMessage::create(['text' => $item['text_ace']]);
             $chat_message = new ChatMessage([
                 'chat_id' => $item['chat_id'],
                 'sender_user_id' => $item['send_user'],
                 'recepient_user_id' => $item['id'],
                 'is_ace' => '1',
                 'created_at' => now(),
                 'updated_at' => now()
             ]);
             $chatListItem = ['chat' => ['id' =>  $item['chat_id']]];
             $chat_text_message->chat_message()->save($chat_message);
             $recepient = User::find($item['id']);
             if (!$recepient->online && $recepient->is_real == 1 && $recepient->is_email_verified == 1) {
                 Mail::to($recepient)->send(new MessageUserMail($recepient, User::find($item['send_user'])));
             }
             $chat_message->chat_messageable = $chat_message->chat_messageable;
             //LetChatMessageNewReadEvent::dispatch($item['send_user'], $item['chat_id'], $chat_message->id);
             $addViewedUser[] = [
                 'user_id' => $item['send_user'],
                 'target_user_id' => $item['id'],
                 'created_at' => now(),
                 'updated_at' => now(),
             ];
             if (rand(1, 20) === 1) {
                 $favorite[] = [
                     'user_id' => $item['send_user'],
                     'favorite_user_id' => $item['id'],
                     'created_at' => now(),
                     'updated_at' => now(),
                 ];
             }
             if (rand(1, 100) <= 15) {
                 $like[] = [
                     'from_user_id' => $item['send_user'],
                     'to_user_id' => $item['id'],
                     'is_liked' => true,
                     'created_at' => now(),
                     'updated_at' => now()
                 ];
             }
         }

         AnketWatch::insert($addViewedUser);
         FavoriteProfile::insert($favorite);
         Feed::insert($like);
         foreach ($list as $item){
            $user = User::find($item['id']);
            $secondUser = User::find($item['send_user']);
             if(!is_null($user)){
                FireBaseService::sendPushFireBase($item['send_user'],"СoolDreamy","Someone visited your page", $user->avatar_url);
                usleep(400);
                FireBaseService::sendPushFireBase($item['send_user'],"СoolDreamy","Someone liked your photo", $user->avatar_url);
                usleep(400);
                FireBaseService::sendPushFireBase($item['send_user'],"СoolDreamy","{$user->name} sent you a message", $user->avatar_url);
                if (!is_null($secondUser->onesignal_token)) {
                    $to_user = $secondUser->onesignal_token;
                    var_dump(111111);
                    OneSignalService::sendNotification($to_user, 'CoolDreamy', 'Someone visited your page', $user->avatar_url);
                    usleep(400);
                    OneSignalService::sendNotification($to_user, 'CoolDreamy', 'Someone liked your photo', $user->avatar_url);
                    usleep(400);
                    OneSignalService::sendNotification($to_user, 'CoolDreamy', "{$user->name} sent you a message", $user->avatar_url);
                }
             }
            // SympathyEvent::dispatch($item['send_user'], AnketProbabilityService::LIKE, $item['id']);
         }
         return $list;
     }catch (\Throwable $e){
         var_dump($e->getMessage(),$e->getLine(),$e->getFile());
         logger("Send Crom Ace ".$e->getTraceAsString());
     }
    }

    public function sendMessageFromAncetCron(array $listUser){
        $listUser = (new self())->getAncetForUser($listUser);
        $listUser = (new self())->getAceForAncet($listUser);
        $listUser = (new self())->createChat($listUser);
    }
    public function chengeCounterStepCron(){
        try {
            Db::statement("UPDATE {$this->engine->getTable()}
                SET step_cron_counter = IF(step_cron_counter - 1 < 0,1000,step_cron_counter - 1)");
        // 126603
            return true;
        }catch (\Throwable $e){

            logger("AceLogic chengeCounterStep ".$e->getMessage());
            return  false;
        }
    }

    public function chengeLimitAssignmentsCron($listByGrouBySort){
        try {
                   $assignmentsList = (array)AceSystemLimitAssignment::query()->get()->toArray();
                   $newArray = [];
                   $groupMasxSort = [];
                   foreach ($assignmentsList as $assist) {
                       if (!isset($newArray[$assist['group_id']]))
                           $newArray[$assist['group_id']] = [];

                       if (!isset($newArray[$assist['group_id']][$assist['sort']]))
                           $newArray[$assist['group_id']][$assist['sort']] = [];
                       $newArray[$assist['group_id']][$assist['sort']] = $assist;

                       if (!isset($groupMasxSort[$assist['group_id']]))
                           $groupMasxSort[$assist['group_id']] = 0;

                       if ($groupMasxSort[$assist['group_id']] < $assist['sort'])
                           $groupMasxSort[$assist['group_id']] = $assist['sort'];

                   }
                   $assignmentsList['group'] = $newArray;
                   $assignmentsList['groupMaxSort'] = $groupMasxSort;


               foreach ($listByGrouBySort as $group_id => $sotrList) {
                   foreach ($sotrList as $sort => $userIds) {
                       if ($assignmentsList['groupMaxSort'][$group_id] > $sort + 1) {
                           $newSort = $sort + 1;
                       } else {
                           $newSort = $assignmentsList['groupMaxSort'][$group_id];
                       }

                       $randStep  =" FLOOR(RAND()* ({$assignmentsList['group'][$group_id][$newSort]['step_to']} -
                       {$assignmentsList['group'][$group_id][$newSort]['step_from']}+1)+{$assignmentsList['group'][$group_id][$newSort]['step_from']})";


                       DB::statement("UPDATE {$this->engine->getTable()}
                            SET step_cron_counter =   {$randStep},
                                last_assignments_id = {$assignmentsList['group'][$group_id][$newSort]['id']},
                                last_assignments_sort = {$newSort}

                            WHERE user_id IN (" . implode(",", $userIds) . ") ");
                   }
               }
               return true;
           }catch (\Throwable $e){
               var_dump($e->getMessage(),$e->getFile(),$e->getLine());
               return  false;
           }
    }




    public static function runCronAce(){
        try {
            $list = new self(['step' => '0']);
            $listUserSend =
                $list->setQuery($list->setLimit(false)->setJoin(['User'])->offPagination()->getFullQuery()
                ->whereRaw("users.gender IS NOT NULL"))->getSandartResultList();
            $listUserSend = (isset($listUserSend['result'])) ? $listUserSend['result'] : [];
            $user_ids = [];
            $apdateAssignments = [];

            if (count($listUserSend)) {
                foreach ($listUserSend as $user) {
                    $user_ids[] = $user['user_id'];
                    if (!isset($apdateAssignments[$user['group_id']]))
                        $apdateAssignments[$user['group_id']] = [];
                    if (!isset($apdateAssignments[$user['group_id']][$user['last_assignments_sort']]))
                        $apdateAssignments[$user['group_id']][$user['last_assignments_sort']] = [];
                    $apdateAssignments[$user['group_id']][$user['last_assignments_sort']][] = $user['user_id'];
                }
                (new self())->sendMessageFromAncetCron($user_ids);

            }
            $listUpdateCounter = new self(['step_not' => '0']);
            $listUpdateCounter->chengeCounterStepCron();
            (new self())->chengeLimitAssignmentsCron($apdateAssignments);
        }catch (\Throwable $e){
            var_dump($e->getMessage(),$e->getFile(),$e->getLine());
        }
    }

    protected function getFilter(){
        $tab = $this->engine->getTable();
        $this->filter = [];
        $this->filter = array_merge($this->filter,parent::getFilter());
        return $this->filter;
    }
    protected function compileGroupParams() {
        return parent::compileGroupParams();
    }
}
