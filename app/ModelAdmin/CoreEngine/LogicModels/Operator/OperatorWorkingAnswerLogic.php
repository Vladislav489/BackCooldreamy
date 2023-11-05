<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Operator;


use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\Operator\WorkingShiftAnserOperators;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OperatorWorkingAnswerLogic extends CoreEngine {
    public function __construct($params = [], $select = ["*"], $callback = null){
        $this->engine = new WorkingShiftAnserOperators();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }

    protected function getFilter(){
        $tab = $this->engine->getTable();
        $this->filter = [
            ["field" => $tab . '.operator_id', "params" => 'operator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            ["field" => $tab .'.ancet_id', "params" => 'ancet',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
            ],

            ["field" => $tab .'.man_id', "params" => 'name',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
            ],
            ["field" => $tab .'.man_id', "params" => 'message',
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
                "Operator" => [
                    "entity" => new User(),
                    "relationship" => ['id', 'operator_id'],
                ],
                "Ancet"=>[
                    "entity" => new User(),
                    "relationship" => ['id', 'ancet_id'],
                ],

                "Man"=>[
                    "entity" => new User(),
                    "relationship" => ['id', 'man_id'],
                ],
            ]
        ];
        return $this->group_params;
    }
}
