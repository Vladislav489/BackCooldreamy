<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\Limit;

use App\Events\SympathyEvent;

use App\Models\AnketWatch;
use App\Models\Chat;
use App\Models\FavoriteProfile;
use App\Models\Feed;
use App\Models\LimitSystem\LimitProbabilityByAnketType;
use App\Models\LimitSystem\LimitSystemLimitAssignment;
use App\Models\OperatorChatLimit;
use App\Services\Probability\AnketProbabilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class LimitChatOperatorCronLogic extends LimitChatOperatorLogic {

    public function __construct($params = [], $select = ["*"], $callback = null){

        parent::__construct($params, $select);
        $this->getFilter();
        $this->compileGroupParams();
    }


    public function rangeProbabilites(array $probabilites, $target)
    {
        $range = [];
        foreach ($probabilites as $key => $probabilite) {
            if (count($range)) {
                if ($probabilites[$key]['probability'] > 0)
                    $range[] = [
                        // 'orig'=>$probabilites[$key],
                        'target' => $probabilites[$key][$target],
                        'start' => $range[count($range) - 1]['end'] + 1,
                        'end' => ($probabilites[$key]['probability'] * 100 + $range[count($range) - 1]['end'])
                    ];
            }
            if (!count($range)) {
                if ($probabilites[$key]['probability'] > 0)
                    $range[] = [
                        //  'orig'=>$probabilites[$key],
                        'target' => $probabilites[$key][$target],
                        'start' => 1,
                        'end' => ($probabilites[$key]['probability'] * 100)
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

    public function ChatLimit($list){
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
                if (rand(1, 100) <= 50) {
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
            var_dump($list);
            foreach ($list as $item){
                if (!OperatorChatLimit::query()->where('man_id', $item['id'])->where('girl_id',$item['send_user'])->exists()) {
                    $chatLimit = OperatorChatLimit::create([
                        'man_id' =>  $item['id'],
                        'girl_id' => $item['send_user'],
                        'limits' => 2,
                        //'chat_id' => $item['chat_id']
                    ]);
                }
            }
           // foreach ($list as $item)
             //   SympathyEvent::dispatch($item['send_user'], AnketProbabilityService::LIKE, $item['id']);

            return $list;
    }


    public function getAncetForUser(array $user_ids){
            if(count($user_ids)>0) {
                $probabilites = LimitProbabilityByAnketType::get()->toArray();
                $sql = [];
                $newArr = [];
                foreach ($user_ids as $key => $id) {
                    $profile_type_id = $this->probabiliteProfil($probabilites);
                    $offset = rand(0, 1);
                    $randOrder = rand(0, 8);
                    $randOrderType = rand(0, 1);
                    $orderType = ['asc', 'desc'];
                    $order = ['Girl.id', 'Girl.name', 'Girl.birthday', 'Girl.state', 'Girl.country', 'Girl.email', 'Girl.online',
                        'Girl.created_at', 'Girl.updated_at'];


                    $sql[] = "SELECT (
                   SELECT  Girl.id  FROM users AS Girl  WHERE
                    Girl.id NOT IN((
                        SELECT ch1.first_user_id AS user_id FROM chats AS ch1 WHERE ch1.second_user_id = Main.id
                        UNION
                        SELECT ch2.second_user_id AS user_id FROM chats as ch2 WHERE   ch2.first_user_id = Main.id))
                        AND Girl.is_real = 0
                                   AND Girl.profile_type_id = {$profile_type_id}
                        ORDER BY {$order[$randOrder]} {$orderType[$randOrderType]}  LIMIT {$offset},1
                    ) AS sendTo , Main.id,Main.name   FROM users AS Main  WHERE  Main.id = {$id} ";

                    $newArr[$id] = ['id' => $id, 'profile_type_id' => $profile_type_id];
                }
                $result = DB::select(implode("\n UNION \n", $sql));
                $result = json_decode(json_encode($result), true);
                foreach ($result as $item) {
                    if ($item['sendTo'] != NULL) {
                        $newArr[$item['id']]['send_user'] = $item['sendTo'];
                        $newArr[$item['id']]['name'] = $item['name'];
                    } else {
                        unset($newArr[$item['id']]);
                    }
                }
                return $newArr;
            }return false;
    }

    public function chengeCounterStepCron()
    {
            Db::statement("UPDATE {$this->engine->getTable()}
               SET step_cron_counter = IF(step_cron_counter - 1 < 0,1000,step_cron_counter - 1)");
            return true;

    }

    public function chengeLimitAssignmentsCron($listByGrouBySort){
            $assignmentsList = (array)LimitSystemLimitAssignment::query()->get()->toArray();
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

                 try {

                     $randStep = " FLOOR(RAND()* ({$assignmentsList['group'][$group_id][$newSort]['step_to']} -
                       {$assignmentsList['group'][$group_id][$newSort]['step_from']}+1)+{$assignmentsList['group'][$group_id][$newSort]['step_from']})";


                     DB::statement("UPDATE {$this->engine->getTable()}
                            SET step_cron_counter =   {$randStep},
                                last_assignments_id = {$assignmentsList['group'][$group_id][$newSort]['id']},
                                last_assignments_sort = {$newSort}

                            WHERE user_id IN (" . implode(",", $userIds) . ") ");
                 }catch (\Throwable $e ){
                        var_dump(
                            $group_id,
                            $sotrList,
                            $assignmentsList['group'][$group_id],
                            $assignmentsList['group'][$group_id][$newSort]
                        );
                 }

            }
        }
            return true;
    }


    public static function runCronLimit(){
        try {
            $list = new self(['step' => '0']);
            $listUserSend = $list->setQuery($list->setLimit(false)->setJoin(['User'])->offPagination()->getFullQuery()
                ->whereRaw("users.gender IS NOT NULL"))->getSandartResultList();
            $listUserSend = (isset($listUserSend['result'])) ? $listUserSend['result'] : [];
            $user_ids = [];
            $apdateAssignments = [];
            if (count($listUserSend)) {
                foreach ($listUserSend as $user) {
                        //$user__ = \App\Models\User::find($user['user_id']);
                        /*if (!is_null($user__->prompt_target_id) || !is_null($user__->prompt_finance_state_id) || !is_null($user__->prompt_source_id) ||
                            !is_null($user__->prompt_want_kids_id) || !is_null($user__->prompt_relationship_id) || !is_null($user__->prompt_career_id)) {
                            $user_ids[] = $user__->id;
                        }*/
                    $user_ids[] = $user['user_id'];
                        if (!isset($apdateAssignments[$user['group_id']]))
                            $apdateAssignments[$user['group_id']] = [];
                        if (!isset($apdateAssignments[$user['group_id']][$user['last_assignments_sort']]))
                            $apdateAssignments[$user['group_id']][$user['last_assignments_sort']] = [];
                        $apdateAssignments[$user['group_id']][$user['last_assignments_sort']][] = $user['user_id'];
                }

                $user_ids = (new self())->getAncetForUser($user_ids);
                if ($user_ids) {
                    (new self())->ChatLimit($user_ids);
                }
            }
            $listUpdateCounter = new self(['step_not' => '0']);

            $listUpdateCounter->chengeCounterStepCron();
            (new self())->chengeLimitAssignmentsCron($apdateAssignments);
        }catch (\Throwable $e){
            var_dump($e->getMessage(),$e->getFile(),$e->getLine());
        }
    }

    protected function getFilter()
    {
        $tab = $this->engine->getTable();
        $this->filter = [];
        $this->filter = array_merge($this->filter, parent::getFilter());
        return $this->filter;
    }

    protected function compileGroupParams()
    {
        return parent::compileGroupParams();
    }
}


