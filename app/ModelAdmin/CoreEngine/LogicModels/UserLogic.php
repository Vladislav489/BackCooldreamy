<?php
namespace App\ModelAdmin\CoreEngine\LogicModels;
use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\ChatMessage;
use App\Models\Feed;
use App\Models\StatisticSite\UserInputs;
use App\Models\StatisticSite\UserWatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLogic extends CoreEngine
{
    public function __construct($params = [],$select = ["*"],$callback = null){
        $this->engine = new User();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params,$select);
    }

    protected function defaultSelect(){
        $tab = $this->engine->getTable();
        $this->default = [];
        return $this->default;
    }

    public function getCountUsers()
    {
        $sub = new UserLogic($this->params);
        return $sub->setLimit(false)->offPagination()->setJoin(['UserCooperationAll'])->getTotal();
    }

    public function getPayUser(){
        $this->select = [DB::raw("SUM(UserPayment.price) as pay")];
        return $this->setLimit('')->setJoin(["UserPayment", 'UserCooperationAll'])->getOne()['pay'] ?? '0';
    }

    public function getCountUser(){
          return $this->getTotal();
    }

    public function getList(){
        $result = parent::getList();
        if(isset($result['result']) && count($result['result']) > 0) {
            foreach ($result['result'] as &$item) {
                $item['created_at'] = date("Y-m-d H:i:s", strtotime($item['created_at']));
            }
        }
        return $result;
    }

    public function getAVGMessage(){
        $sub = new UserLogic(array_merge($this->params, ['gender' => 'male']));
        $sub->select = [DB::raw("COUNT(chat_messages.id) / COUNT(DISTINCT(chat_messages.sender_user_id)) as avg_message ")];
        return $sub->setLimit(false)->setJoin(["ChatMessageSend", 'UserCooperationAll'])->getOne()['avg_message'] ?? '0';
    }

    public function getFakeAncetId(){
        $this->params['is_real'] = '0';
        return $this->setParams($this->params)
             ->offPagination()
             ->setLimit('false')
             ->executeFilter()
             ->pluck('user_id')->toArray();
    }

    public function getCountSession()
    {
        $sub = new UserLogic($this->params, [DB::raw('COUNT(UserInputs.id) as user_input')]);
        return $sub->setLimit(false)->setJoin(['UserInputs', 'UserCooperationAll'])->getOne()['user_input'] ?? '0';
    }


    protected function getFilter(){
        $tab = $this->engine->getTable();
        $this->filter = [
            [   "field" =>$tab.'.id', "params" => 'user',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.email', "params" => 'email',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            [   "field" =>$tab.'.id', "params" => 'user_not',
                "validate" => ["array" => true, "empty" => true],
                "type" => 'array', "action" => 'NOT IN', "concat" => 'AND',
            ],

            [   "field" =>$tab.'.gender', "params" => 'gender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            ["field" => $tab.'.search_gender', "params" => 'search_gender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel' => "User"
            ],
            [   "field" =>"`{$tab}`.`avatar_url`", "params" => 'ava',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => 'CHECK_NULL', "concat" => 'AND',
            ],

            [   "field" =>$tab.'.id', "params" => 'id_from',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '>=', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.id', "params" => 'id_to',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '<=', "concat" => 'AND',
            ],

            [   "field" => $tab.'.state', "params" => 'state',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
            ],
            [   "field" => $tab.'.country', "params" => 'country',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND'
            ],

            [   "field" => $tab.'.is_real', "params" => 'is_real',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND'
            ],

            [   "field" => DB::raw(" DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),".$tab.".birthday)), '%Y-m') +0"), "params" => 'age_user',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
            ],

            [   "field" => 'DATE('.$tab.'.created_at)', "params" => 'date_registration_to',
                "validate" => ["date" => true,"empty" => true],
                "type" => 'date', "action" => '<=',"concat" =>'AND'
            ],

            [   "field" => 'DATE('.$tab.'.created_at)', "params" => 'date_registration_from',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND'
            ],



            [   "field" => 'DATE(UserLike.created_at)', "params" => 'date_to',
                "validate" => ["date" => true,"empty" => true],
                "type" => 'date', "action" => '<=',"concat" =>'AND',
                'relatedModel' => ['UserLikeFrom', 'UserLikeTo']
            ],

            [   "field" => 'DATE(UserLike.created_at)', "params" => 'date_from',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND',
                'relatedModel' => ['UserLikeFrom', 'UserLikeTo']
            ],

            [   "field" => 'UserPayment.status', "params" => 'payment_status',
                "validate" => ["string" => true,"empty" => true],
                "type" => 'string', "action" => '=',"concat" =>'AND',

            ],
            [   "field" => 'DATE(Payment.created_at)', "params" => 'date_to',
                "validate" => ["date" => true,"empty" => true],
                "type" => 'date', "action" => '<=',"concat" =>'AND',
                'relatedModel' => "UserPayment"
            ],
            [   "field" => 'DATE(Payment.created_at)', "params" => 'date_from',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND',
                'relatedModel' => "UserPayment"
            ],
            ["field" =>"(SELECT COUNT(*) FROM ".(new ChatMessage())->getTable()." as message_chat
            WHERE  message_chat.sender_user_id = `". $tab ."`.id  )",
                "params" => 'count_message_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '>=', "concat" => 'AND'
            ],
            ["field" =>"(SELECT COUNT(*) FROM ".(new ChatMessage())->getTable()." as message_chat
            WHERE  message_chat.sender_user_id = `". $tab ."`.id  )",
                "params" => 'count_message_less',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '<=', "concat" => 'AND'
            ],
            ["field" =>"UserCooperation.utm_source", "params" => 'utm_source',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel' => 'UserCooperation'
            ],
            ["field" =>"UserCooperation.utm_medium", "params" => 'utm_medium',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel' => 'UserCooperation'
            ],
            ["field" =>"UserCooperation.utm_campaign", "params" => 'utm_campaign',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel' => 'UserCooperation'
            ],
            ["field" =>"UserCooperation.utm_term", "params" => 'utm_term',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel' => 'UserCooperation'
            ],
            ["field" =>"UserCooperation.utm_advertiser", "params" => 'utm_advertiser',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel' => 'UserCooperation'
            ],

        ];
        $this->filter = array_merge($this->filter,parent::getFilter());
        return $this->filter;
    }
    protected function compileGroupParams() {
        $this->group_params = [
            "select" => [],
            "by" => ["gender" => "gender",
                     "country" => "country",
                     "sender_user_id" => "sender_user_id",
                     "recepient_user_id" => "recepient_user_id",
                     "state" => "state" ,
                     "user_id" => DB::raw("SUM(price)"),
                     "birthday" => DB::raw(" DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),birthday)), '%Y-m') +0")
                    ],
            "custom_select" => [
                "count_user" => DB::raw("COUNT(id) as count"),
                "avg_user" => DB::raw("AVG(SUM(id)) as avg"),
                "sender_user_id" =>DB::raw("COUNT(*) / (SELECT COUNT(*) FROM users WHERE gender ='male' AND is_real = 1 )  as avg"),
                'birthday'=>DB::raw(" DATE_FORMAT(FROM_DAYS(DATEDIFF(now(),birthday)), '%Y-m') +0 as age_user")],
            "relatedModel" => [
                "ChatMessageSend" => [
                    "entity" => new ChatMessage(),
                    "relationship" => ['sender_user_id','id'],
                ],
                "ChatMessageGet" => [
                    "entity" => new ChatMessage(),
                    "relationship" => ['recepient_user_id','id'],
                ],

                "UserPayment" => [
                    "entity" => (new User\Payment())->getTable()." as UserPayment",
                    "relationship" => ['user_id','id'],
                ],
                "UserLikeFrom" => [
                    "entity" => (new Feed())->getTable()." as UserLike",
                    "relationship" => ['from_user_id','id'],
                ],
                "UserLikeTo" => [
                    "entity" => (new Feed())->getTable()." as UserLike",
                    "relationship" => ['to_user_id','id'],
                ],
                "UserWatch"=>[
                    "entity" => (new UserWatch())->getTable()." asUserWatch",
                    "relationship" => ['user_id','id'],
                ],
                "UserCoop"=>[
                    "entity" => (new User\UserCooperation())->getTable()." as UserCoop",
                    "relationship" => ['user_id','id'],
                    "type" => "inner"
                ],
                "UserCooperationAll"=>[
                    "entity" => (new User\UserCooperation())->getTable()." as UserCooperation",
                    "relationship" => ['user_id','id'],
                ],
                "UserInputs"=>[
                    "entity" => (new UserInputs())->getTable()." as UserInputs",
                    "relationship" => ['user_id','id'],
                ],
            ]
        ];
        return $this->group_params;
    }


}
