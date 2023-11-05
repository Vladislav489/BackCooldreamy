<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\Limit;


use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\Chat;
use App\Models\OperatorChatLimit;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OperatorChatLimitLogic extends CoreEngine {

    public function __construct($params = [], $select = ["*"], $callback = null)
    {
        $this->engine = new OperatorChatLimit();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }
    protected function defaultSelect()
    {
        $tab = $this->engine->tableName();
        $this->default = [];
        return $this->default;
    }

    protected function getFilter()
    {
        $tab = $this->engine->getTable();
        $this->filter = [
            ["field" => $tab . '.man_id', "params" => 'man',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            ["field" => $tab . '.girl_id', "params" => 'girl',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            ["field" => $tab . '.limits', "params" => 'limit_less',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '<', "concat" => 'AND',
            ],

            ["field" => $tab . '.limits', "params" => 'limit',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '=', "concat" => 'AND',
            ],
            ["field" => $tab . '.limits', "params" => 'limit_more',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '>', "concat" => 'AND',
            ],
            [   "field" => 'DATE('.$tab.'.created_at)', "params" => 'date_to',
                "validate" => ["date" => true,"empty" => true],
                "type" => 'date', "action" => '<=',"concat" =>'AND'
            ],

            [   "field" => 'DATE('.$tab.'.created_at)', "params" => 'date_from',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '>=', "concat" => 'AND'
            ],
        ];
        $this->filter = array_merge($this->filter, parent::getFilter());
        return $this->filter;
    }

    protected function compileGroupParams(){
        $this->group_params = [
            "select" => [],
            "by" => [
            //    'man_id'=>'man_id'
            ],
            "custom_select" => [],
            "relatedModel" => [
                "Chat" => [
                    "entity" => new Chat(),
                    "relationship" => ['chat_id','user_id'],
                ],
                "OperatorAncet" => [
                    "entity" => new  OperatorLinkUsers(),
                    "relationship" => ['user_id','girl_id'],
                ],
                "Man" => [
                    "entity" => new User(),
                    "relationship" => ['man_id','user_id'],
                ],
                "Girl" => [
                    "entity" => new User(),
                    "relationship" => ['grir_id','user_id'],
                ],

            ]
        ];
        return $this->group_params;
    }
}
