<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Chat;
use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Operator\WorkingShiftLog;
use App\Models\OperatorChatLimit;
use App\Models\OperatorLinkUsers;
use App\Models\Subscriptions;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ChatLogic extends CoreEngine {
    public function __construct($params = [],$select = ["*"],$callback = null){
        $this->engine = new Chat\Chat();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params,$select);
    }


    public function getListChatUser($user_id,$request){
        $params = ['ancet' => (string)$user_id, 'deleted_first_user' => '0', 'deleted_second_user' => '0', 'exist_message' => '1'];
        if($request['chat_id'])
            $params['id'] = $request['chat_id'];


        if ($request['filter'] == 'favorite') {
            $favorite_users = FavoriteProfile::where('user_id', $user_id)->where('disabled', false)->pluck('favorite_user_id');
            $params['chat_by_first_sec_user'] = [[$user_id],$favorite_users];
        }
        if ($request['filter'] == 'unread') {
            $params['read_by_recepient'] = '0';
            $params['recepient_user'] = $user_id;
        }

        if ($request['filter'] == 'ignored')
            $params['is_ignored_by'] = '1';
        if (isset($request['search']) && !empty($request['search']))
            $params['search_name'] = $request['search'];


        $chat = new ChatLogic($params,
            ['id', 'is_answered_by_operator','first_user_id','second_user_id',
                DB::raw('(SELECT  json_object("id",id,"name",name,"avatar_url_thumbnail",avatar_url_thumbnail,"online",online,
                "age",DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(birthday)), "%Y")+0) FROM users WHERE  users.id = first_user_id) as first_user'),
                DB::raw('(SELECT  json_object("id",id,"name",name,"avatar_url_thumbnail",avatar_url_thumbnail,"online",online,
                "age",DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(birthday)), "%Y")+0) FROM users WHERE  users.id = second_user_id) as second_user')
            ]);

        $chat_list = $chat->offPagination()->order("desc", 'updated_at')->getList()['result'];
        foreach ($chat_list as &$item) {
            $item['first_user'] = json_decode($item['first_user'],true);
            $item['second_user'] = json_decode($item['second_user'],true);
            $this->reversUserChat($item,$user_id);

        }
        return $chat_list;
    }

    public function reversUserChat(&$item,$user_id){
        if($item['first_user']['id'] ==  $user_id){
            $item['my_self_user'] = $item['first_user'];
            $item['another_user'] = $item['second_user'];
        }else if($item['second_user']['id'] ==  $user_id){
            $item['my_self_user'] = $item['second_user'];
            $item['another_user'] = $item['first_user'];
        }
    }

    public function getListChatUserOperator(){

    }



    protected function defaultSelect(){
        $tab = $this->engine->tableName();
        $this->default = [];
        return $this->default;
    }
    private function filterChat(){
        $tab = $this->engine->getTable();
        $validate = ["string" => true, "empty" => true];
        return [
            [   "field" =>$tab.'.id', "params" => 'id',"validate" =>$validate ,
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'],

            [   "field" =>'(select IF(`chat_messages`.`id` IS NULL ,0,1) from `chat_messages`
                   where `chats`.`id` = `chat_messages`.`chat_id` LIMIT 1)', "params" => 'exist_message',"validate" =>$validate ,
                "type" => 'string', "action" => '=', "concat" => 'AND',
            ],

            [   "field" =>'ChatMessage.is_read_by_recepient', "params" => 'read_by_recepient',"validate" =>$validate ,
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel'=>'ChatMessage'
            ],

            [   "field" =>'ChatMessage.recepient_user_id', "params" => 'recepient_user_id',"validate" =>$validate ,
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel'=>'ChatMessage'
            ],

            [   "field" =>'ChatMessage.recepient_user_id', "params" => 'recepient_user_id',"validate" =>$validate ,
                "type" => 'string', "action" => '=', "concat" => 'AND',
            ],


            [   "field" =>$tab.'.first_user_id', "params" => 'first_user',"validate" =>$validate ,
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'],
            [   "field" =>$tab.'.second_user_id', "params" => 'second_user',"validate" => $validate,
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'],
            [   "field" =>$tab.'.is_ignored_by_first_user', "params" => 'ignored_first_user',"validate" => $validate,
                "type" => 'string', "action" => '=', "concat" => 'AND'],
            [   "field" =>$tab.'.is_ignored_by_second_user', "params" => 'ignored_second_user',"validate" =>$validate,
                "type" => 'string', "action" => '=', "concat" => 'AND'],
            [   "field" =>$tab.'.deleted_by_second_user', "params" => 'deleted_first_user',"validate" =>$validate,
                "type" => 'string', "action" => '=', "concat" => 'AND'],
            [   "field" =>$tab.'.deleted_by_first_user', "params" => 'deleted_second_user',"validate" =>$validate,
                "type" => 'string', "action" => '=', "concat" => 'AND'],
            [   "field" =>$tab.'.is_answered_by_operator', "params" => 'answered_operatorr',"validate" => $validate,
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'],

            [   "field" =>$tab.'.first_user_id', "params" => 'first_user_or', "validate" => $validate,
                "type" => 'string|array', "action" => 'IN', "concat" => 'OR'],
            [   "field" =>$tab.'.second_user_id', "params" => 'second_user_or',"validate" =>$validate,
                "type" => 'string|array', "action" => 'IN', "concat" => 'OR'],

            [   "field" =>$tab.'.id  IN (SELECT chat_id FROM '.(new ChatMessage())->getTable().'
                                        left join chat_text_messages ON  chat_text_messages.id = chat_messageable_id AND
                                        chat_messageable_type LIKE "%ChatTextMessage%"
                                        WHERE chat_text_messages.text LIKE ?
                                    )',
                "params" => 'text_message',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],

        ];
    }
    private function filteUser(){
        $tab = $this->engine->getTable();
        $validate = ["string" => true, "empty" => true];
        return [
            [   "field" =>$tab.'.first_user_id', "params" => 'search_id',"validate" => $validate,
                "type" => 'string|array', "action" => '%LIKE%', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.second_user_id', "params" => 'search_id',"validate" => $validate,
                "type" => 'string|array', "action" => '%LIKE%', "concat" => '',
            ],

            [   "field" =>"(".$tab.'.first_user_id  IN (SELECT id FROM '.(new User())->getTable().'
                                        WHERE online = ? AND is_real= 1 )',
                "params" => 'online',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.second_user_id  IN (SELECT id FROM '.(new User())->getTable().'
                                        WHERE online = ? AND is_real= 1  ))',
                "params" => 'online',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
            ],


            [   "field" =>"(".$tab.'.is_ignored_by_first_user  = "?"',
                "params" => 'is_ignored_by',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>$tab.'is_ignored_by_second_user  = "?" )',
                "params" => 'is_ignored_by',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
            ],

            [   "field" =>"(".$tab.'.first_user_id  IN (?)',
                "params" => 'ancet',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.second_user_id  IN (?))',
                "params" => 'ancet',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
            ],

            [
                "field" =>"(".'FirstUser.first_user_id  LIKE "%?%"',
                "params" => 'search_name',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
                'relatedModel'=>'FirstUser'
            ],

            [
                "field" =>'SecondUser.second_user_id  LIKE "%?%")',
                "params" => 'search_name',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
                'relatedModel'=>'SecondUser'
            ],

            [   "field" =>"((".$tab.".first_user_id IN (~?1~)  AND ".$tab.".second_user_id IN (~?2~))",
                "params" => 'chat_by_first_sec_user',
                "validate" => ["array" => true, "empty" => true],
                "type" => 'array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>"(".$tab.".first_user_id IN (~?2~) AND ".$tab.".second_user_id IN (~?1~)) )",
                "params" => 'chat_by_first_sec_user',
                "validate" => ["array" => true, "empty" => true],
                "type" => 'array', "action" => 'RAW', "concat" => 'OR',
            ],




            [   "field" =>"((SELECT COUNT(*) FROM ".(new User\Payment())->getTable()."
                    WHERE user_id = ".$tab.".first_user_id AND status = 'success' )  >= ?",
                "params" => 'payed_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>"(SELECT COUNT(*) FROM ".(new User\Payment())->getTable()."
                    WHERE user_id = ".$tab.".second_user_id AND status = 'success' ) >= ?)",
                "params" => 'payed_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
            ],

            [   "field" =>"((SELECT COUNT(*) FROM ".(new Subscriptions())->getTable()."
                    WHERE user_id = ".$tab.".first_user_id AND  period_start <= '".now()."' AND period_end >= '".now()."')  >= ?",
                "params" => 'subscription_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>"(SELECT COUNT(*) FROM ".(new Subscriptions())->getTable()."
                    WHERE user_id = ".$tab.".second_user_id AND period_start <= '".now()."' AND period_end >= '".now()."')  >= ?)",
                "params" => 'subscription_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
            ],


            [   "field" =>"((SELECT COUNT(*) FROM ".(new User\Premuim())->getTable()."
                    WHERE user_id = ".$tab.".first_user_id AND period_start <= '".now()."' AND period_end >= '".now()."')  >= ?",
                "params" => 'premium_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>"(SELECT COUNT(*) FROM ".(new User\Premuim())->getTable()."
                    WHERE user_id = ".$tab.".second_user_id AND period_start <= '".now()."' AND period_end >= '".now()."') >= ?)",
                "params" => 'premium_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
            ],


            [   "field" =>"(".$tab.'.first_user_id  IN (SELECT id FROM '.(new User())->getTable().'
                                        WHERE is_real= ? )',
                "params" => 'real',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.second_user_id  IN (SELECT id FROM '.(new User())->getTable().'
                                        WHERE is_real = ? ))',
                "params" => 'real',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'OR',
            ],

        ];
    }
    protected function getFilter(){
        $tab = $this->engine->getTable();
        $tabUser = ( new User())->getTable();
        $this->filter = [
            [   "field" =>'ChatLimit.limits', "params" => 'limit_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '>=', "concat" => 'AND',
                'relatedModel'=>"ChatLimit"
            ],
            [   "field" =>'OperatorWork.operator_id', "params" => 'operator_id',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel'=>'OperatorWork'
            ],
            [   "field" =>'OperatorWork.operator_work', "params" => 'operator_work',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel'=>'OperatorWork'
            ],



            [   "field" => 'DATE(ChatMessage.created_at)', "params" => 'date_to',
                "validate" => ["date" => true,"empty" => true],
                "type" => 'date', "action" => '<=',"concat" =>'AND',
                'relatedModel'=>'ChatMessage'
            ],

            [   "field" => 'DATE(ChatMessage.created_at)', "params" => 'date_from',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '>=', "concat" => 'AND',
                'relatedModel'=>'ChatMessage'
            ],

        ];
        $this->filter = array_merge($this->filter,$this->filterChat());
        $this->filter = array_merge($this->filter,$this->filteUser());
        $this->filter = array_merge($this->filter,parent::getFilter());
        return $this->filter;
    }

    protected function compileGroupParams() {
        $this->group_params = [
            "select" => [],
            "by" => [
                'operator_id'=>'operator_id',
                'id'=>['group'=>'id','filed'=>'']
            ],
            "custom_select" => [],
            "relatedModel" => [

                "FirstUser" => [
                    "entity" => DB::raw((new User())->getTable()." as FirstUser  ON
                        FirstUser.id = first_user_id  OR  FirstUser.id = second_user_id"),
                ],
                "SecondUser" => [
                    "entity" => DB::raw((new User())->getTable()." as SecondUser  ON
                        SecondUser.id = first_user_id  OR SecondUser.id = second_user_id"),
                ],
                "OperatorAncet" =>[
                    "entity" => DB::raw((new OperatorLinkUsers())->getTable()." as OperatorAncet  ON
                        OperatorAncet.user_id = first_user_id  OR  OperatorAncet.user_id = second_user_id"),
                ],

                "OperatorWork" =>[
                    "entity" => DB::raw((new OperatorLinkUsers())->getTable()." as OperatorWork ON
                        (OperatorWork.user_id = first_user_id  OR OperatorWork.user_id = second_user_id)"),
                ],

                "ChatMessage"=>[
                    "entity" => DB::raw((new ChatMessage())->getTable()." as ChatMessage ON
                      chats.id = ChatMessage.chat_id "),
                ],

                "ChatLimit" =>[
                    "entity" => DB::raw((new OperatorChatLimit())->getTable()." as ChatLimit  ON
                        ChatLimit.chat_id = chats.id "),
                ],

            ]
        ];
        return $this->group_params;
    }
}
