<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Operator;
use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\ChatMessage;
use App\Models\Operator\WorkingShiftAnserOperators;
use App\Models\Operator\WorkingShiftLog;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OperatorWorkingShiftLogic extends CoreEngine {
    public function __construct($params = [], $select = ["*"], $callback = null){
        $this->engine = new WorkingShiftLog();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }

    protected function getFilter(){
        $tab = $this->engine->getTable();
        $this->filter = [
            ["field" => $tab . '.user_id', "params" => 'operator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            ["field" => 'status', "params" => 'status',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
            ],


            ["field" => 'DATE(created_at)', "params" => 'date_to',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '<=', "concat" => 'AND',
            ],
            ["field" => 'DATE(created_at)', "params" => 'date_from',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '>=', "concat" => 'AND',
            ],


            ["field" => 'Message.sender_user_id', "params" => 'sender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '!=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],


            ["field" => 'DATE( Message.created_at)', "params" => 'date_from_message',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],
            ["field" => 'DATE( Message.created_at)', "params" => 'date_to_message',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND',
                'relatedModel' => "Message"
            ],
        ];
        $this->filter = array_merge($this->filter, parent::getFilter());
        return $this->filter;
    }

    protected function compileGroupParams(){
        $this->group_params = [
            "select" => [],
            "by" => [
                'sender_user_id'=>'Message.sender_user_id',
                'user_id'=>'user_id',
            ],
            "custom_select" => [
                'user_id'=>'user_id as operator_id',
            ],
            "relatedModel" => [
                "User" => [
                    "entity" => new User(),
                    "relationship" => ['id', 'user_id'],
                    "type"=>"left"
                ],
                "MessageAnsver" => [
                    "entity" => new WorkingShiftAnserOperators(),
                    "relationship" => ['operator_id', 'user_id'],
                    "type"=>"left"
                ],
                "Sender" => [
                    "entity" => DB::raw((new User())->getTable()." as Sender ON ".
                        "Message.sender_user_id = Sender.id "),
                    "type"=>"left"
                ],
                "Message"=>[
                    "entity" => DB::raw((new ChatMessage())->getTable()." as Message  ON
                        Message.sender_user_id IN
                    ((SELECT user_id FROM ".(new OperatorLinkUsers())->getTable()." WHERE operator_id = user_id))")
                ],
                "OperatorMessage"=>[
                    "entity" => DB::raw((new WorkingShiftAnserOperators())->getTable()." as OperatorMessage  ON
                        OperatorMessage.operatore_id = user_id))")
                ],
                "Ancet"=>[
                    "entity" => (new OperatorLinkUsers())->getTable()." as Ancet",
                    "relationship" => ['operator_id', 'user_id'],
                    "type"=>"right"
                ]

            ]
        ];
        return $this->group_params;
    }
}
