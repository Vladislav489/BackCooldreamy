<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\User;

use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\ModelAdmin\CoreEngine\LogicModels\UserLogic;
use App\Models\User;
use App\Models\User\UserCooperationCron;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function NunoMaduro\Collision\Exceptions\getClassName;

class UserCooperationCronLogic extends CoreEngine {

    const STATUS_NEW = 0;
    const STATUS_DONE = 1;

    const ACTION_SALE = 1;
    const ACTION_LEAD  = 2;

    private $list = [1=>'sale',2=>'lead'];
    public function __construct($params = [], $select = ["*"], $callback = null){
        $this->engine = new UserCooperationCron();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }

    public function getListAction(){
        return $this->list;
    }

    protected function defaultSelect(){
        $tab = $this->engine->tableName();
        $this->default = [];
        return $this->default;
    }


    /*
     * Dmitriy_J, [18.09.2023 10:11]
Для регистрации - http://95.179.250.121/e39dfb1/postback?subid=REPLACE&status=lead

Dmitriy_J, [18.09.2023 10:15]
Для продаж http://95.179.250.121/e39dfb1/postback?subid=REPLACE&status=sale&payout=123&tid=1111
     */

    public function addTaskSale(User\Payment $payment){
        try {
            return  $this->addNewTask($payment,$payment->user_id,self::ACTION_SALE);
        }catch (\Throwable $e){
            var_dump($e->getMessage());
        }

    }
    public function addTaskLead(User $user){
        return  $this->addNewTask($user,$user->id,self::ACTION_LEAD);
    }

    public function addNewTask($data,$user_id,$action){
        return  UserCooperationCron::create([
            'user_id' => $user_id,
            'data_user'=>json_encode($data),
            'type_action' => $action,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public static function RunCronSearchLead(){
       $cron = new self(['action'=>(string)self::ACTION_LEAD]);
       $chack_id = $cron->offPagination()->setLimit(false)->getFullQuery()->get()->pluck('user_id')->toArray();


       $list = new  UserLogic(['count_message_more'=>'5', 'count_message_less'=>'10',
          'user_not' => $chack_id, 'gender'=>'male', 'is_real'=>'1', 'search_gender'=>'female']);
       $res = $list->setJoin(['UserCooperation'])->offPagination()->setLimit(false)->getList();
       $dataLead = [];
       if(isset($res['result']) && count($res['result']) > 0) {
           foreach ($res['result'] as $item) {
               $dataLead[] = [
                   'user_id' => $item['id'],
                   'data_user' => json_encode($item),
                   'type_action' => self::ACTION_LEAD,
                   'created_at' => now(),
                   'updated_at' => now()
               ];
           }
           UserCooperationCron::insert($dataLead);
       }
    }

    public static function RunCronSend(){
        $cronList = new self(['status' => (string)self::STATUS_NEW],
        ['id','user_id','data_user','type_action','status',
            DB::raw("User.email as email"),DB::raw('UserCooperation.subid as subid ')]
        );
        var_dump($cronList->setJoin(['UserCooperation','User'])->offPagination()->setLimit(false)->getSqlToStr());

        $ListSend = $cronList->setJoin(['UserCooperation','User'])->offPagination()->setLimit(false)->getList();

        $list = (new self())->getListAction();

        if(count($ListSend['result'])) {
            $idCron = [];
            foreach ($ListSend['result'] as $item) {
                $idCron[] = $item['id'];
                $status = $list[$item['type_action']];
                $url = "http://95.179.250.121/e39dfb1/postback?subid={$item['subid']}&status={$status}";
                $data = json_decode($item['data_user'],true);
                switch ($item['type_action']) {
                    case self::ACTION_SALE:
                        $url .= "&payout={$data['price']}&tid={$data['id']}";
                        break;
                }

                if($item['type_action'] == self::ACTION_SALE ){
                    $email =  Hash::make($data['user']['email']);
                }else{
                    $email =  Hash::make($data['email']);
                }

                $url .="&sub_id_12={$email}";
                //http://95.179.250.121/e39dfb1/postback?subid={$item['subid']}&status={$status}&sub_id_12={$email}
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));
                $response = curl_exec($curl);
                curl_close($curl);
            }
            $cronList->getEngine()->newQuery()->whereIn('id', $idCron)->update(
                ['status' => (string)self::STATUS_DONE]
            );
        }
    }


    protected function getFilter() {
    $tab = $this->engine->getTable();
    $this->filter = [
        ["field" => $tab . '.user_id', "params" => 'user',
            "validate" => ["string" => true, "empty" => true],
            "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
        ],

        ["field" => $tab . '.type_action', "params" => 'action',
            "validate" => ["string" => true, "empty" => true],
            "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
        ],
        ["field" => $tab . '.status', "params" => 'status',
            "validate" => ["string" => true, "empty" => true],
            "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
        ],
        ["field" => $tab . '.status', "params" => 'status_or',
            "validate" => ["string" => true, "empty" => true],
            "type" => 'string|array', "action" => 'IN', "concat" => 'OR'
        ],
        ["field" => 'DATE(' . $tab . '.created_at)', "params" => 'date_to',
            "validate" => ["date" => true, "empty" => true],
            "type" => 'date', "action" => '<=', "concat" => 'AND'
        ],

        ["field" => 'DATE(' . $tab . '.created_at)', "params" => 'date_from',
            "validate" => ["datetime" => true, "empty" => true],
            "type" => 'datetime', "action" => '>=', "concat" => 'AND'
        ],
    ];
    $this->filter = array_merge($this->filter, parent::getFilter());
    return $this->filter;
}

    protected function compileGroupParams()
{
    $this->group_params = [
        "select" => [],
        "by" => [],
        "custom_select" => [],
        "relatedModel" => [
            "User" => [
                "entity" => (new User())->getTable() . " as User",
                "relationship" => ['id', 'user_id'],
                "type" => "left"
            ],
            "UserCooperation" => [
                "entity" => (new User\UserCooperation())->getTable() . " as UserCooperation",
                "relationship" => ['user_id', 'user_id'],
                "type" => "left"
            ],
        ]
    ];
    return $this->group_params;
}
}
