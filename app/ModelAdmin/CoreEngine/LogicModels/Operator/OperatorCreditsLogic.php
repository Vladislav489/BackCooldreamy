<?php

namespace App\ModelAdmin\CoreEngine\LogicModels\Operator;

use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\User;
use App\Models\UserPayedMessagesToOperators;

class OperatorCreditsLogic extends CoreEngine
{
    public function __construct($params = [], $select = ["*"], $callback = null)
    {
        $this->engine = new UserPayedMessagesToOperators();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }

    protected function getFilter()
    {
        $tab = $this->engine->getTable();
        $this->filter = [
            ["field" => $tab . '.operator_id', "params" => 'operator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
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

    protected function compileGroupParams()
    {
        $this->group_params = [
            "select" => [],
            "by" => [
                'operator_id' => 'operator_id'

            ],
            "custom_select" => [],
            "relatedModel" => [
                "User" => [
                    "entity" => new User(),
                    "relationship" => ['id', 'operator_id'],
                ],

            ]
        ];
        return $this->group_params;
    }
}
