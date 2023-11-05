<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Operator;

use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\ChatMessage;
use App\Models\Operator\WorkingShiftAnserOperators;
use App\Models\Operator\WorkingShiftCron;
use App\Models\Operator\WorkingShiftLog;
use App\Models\OperatorLinkUsers;
use App\Models\User\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OperatorLogic extends CoreEngine
{
    public function __construct($params = [], $select = ["*"], $callback = null)
    {
        $this->engine = new UserRole();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }

    protected function defaultSelect(){
        $tab = $this->engine->getTable();
        $this->default = [];
        return $this->default;
    }



    protected function getFilter()
    {
        $tab = $this->engine->getTable();
        $this->filter = [
            ["field" => $tab . '.model_id', "params" => 'operator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            ["field" => $tab . '.role_id', "params" => 'role',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            ["field" => 'Ancet.operator_work	', "params" => 'is_work',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
            ],
            ["field" => (new User())->getTable() . '.online', "params" => 'online',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'LIKE', "concat" => 'AND',
                'relatedModel' => "Operator"
            ],
            ["field" => (new User())->getTable() . '.name', "params" => 'name',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'LIKE', "concat" => 'AND',
                'relatedModel' => "Operator"
            ],

            ["field" => '(SELECT COUNT(user_id) AS c__ FROM operator_link_users AS worl___ WHERE worl___.operator_work = 1 GROUP BY user_id ORDER BY c__ desc LIMIT 1) <= ? ', "params" => 'is_work_operator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'RAW', "concat" => 'AND',
                'relatedModel' => "Ancet"
            ],





            ["field" => 'DATE(' . (new WorkingShiftLog())->getTable() . '.updated_at)', "params" => 'date_to_work',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '<=', "concat" => 'AND',
                'relatedModel' => "WorkTime"
            ],
            ["field" => 'DATE(' . (new WorkingShiftLog())->getTable() . '.updated_at)', "params" => 'date_from_work',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND',
                'relatedModel' => "WorkTime"
            ],

            ["field" => 'Message.sender_user_id', "params" => 'sender',
            ///    "defaultValue"=>'null',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '!=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],

            ["field" => 'Message.is_ace', "params" => 'is_ace',
                ///    "defaultValue"=>'null',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],

            ["field" => 'Message.sender_user_id', "params" => 'sender',
                ///    "defaultValue"=>'null',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '!=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],

            ["field" => 'DATE( Message.created_at)', "params" => 'date_from_message',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '>=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],
            ["field" => 'DATE( Message.created_at)', "params" => 'date_to_message',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '<=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],
        ];
        $this->filter = array_merge($this->filter, parent::getFilter());
        return $this->filter;
    }

    protected function compileGroupParams()
    {
        $this->group_params = [
            "select" => [],
            "by" => [
                'sender_user_id'=>'Message.sender_user_id',
                'model_id'=>'model_id',
            ],
            "custom_select" => [
                'model_id'=>'model_id as operator_id',
            ],
            "relatedModel" => [
                "User" => [
                    "entity" => new User(),
                    "relationship" => ['id', 'model_id'],
                    "type"=>"left"
                ],
                "MessageAnsver" => [
                    "entity" => new WorkingShiftAnserOperators(),
                    "relationship" => ['operator_id', 'model_id'],
                    "type"=>"left"
                ],
                "Sender" => [
                    "entity" => DB::raw((new User())->getTable()." as Sender ON ".
                        "Message.sender_user_id = Sender.id "),
                    "type"=>"left"
                ],
                "WorkTime" => [
                    "entity" => new WorkingShiftLog(),
                    "relationship" => ['user_id', 'model_id'],
                    "type"=>"right"
                ],
                "Message"=>[
                    "entity" => DB::raw((new ChatMessage())->getTable()." as Message  ON
                        (Message.sender_user_id IN
                    ((SELECT user_id FROM ".(new OperatorLinkUsers())->getTable()." WHERE operator_id = model_id)) OR
                     Message.recepient_user_id IN
                    ((SELECT user_id FROM ".(new OperatorLinkUsers())->getTable()." WHERE operator_id = model_id)))")
                ],

                "OperatorMessage"=>[
                    "entity" => DB::raw((new WorkingShiftAnserOperators())->getTable()." as OperatorMessage  ON
                        OperatorMessage.operatore_id = model_id))")
                ],
                "Ancet"=>[
                    "entity" => (new OperatorLinkUsers())->getTable()." as Ancet",
                    "relationship" => ['operator_id', 'model_id'],
                    "type"=>"right"
                ]

            ]
        ];
        return $this->group_params;
    }
}
