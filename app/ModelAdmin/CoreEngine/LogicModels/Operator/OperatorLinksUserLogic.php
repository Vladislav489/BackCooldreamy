<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Operator;

use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class OperatorLinksUserLogic extends CoreEngine
{
    public function __construct($params = [],$select = ["*"],$callback = null){
        $this->engine = new OperatorLinkUsers();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params,$select);
    }




    protected function defaultSelect(){
        $tab = $this->engine->tableName();
        $this->default = [];
        return $this->default;
    }

    protected function getFilter(){
        $tab = $this->engine->getTable();
        $this->filter = [
            [   "field" =>$tab.'.operator_id', "params" => 'operator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" => $tab.'.user_id', "params" => 'user',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
            ],
            [   "field" => $tab.'.operator_work', "params" => 'work',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
            ],
            [   "field" => $tab.'.description', "params" => 'description',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'LIKE', "concat" => 'AND'
            ],
            [   "field" => ( new User())->getTable().'.name', "params" => 'name',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'LIKE', "concat" => 'AND',
                'relatedModel'=>"Operator"
            ],
            [   "field" => ( new User())->getTable().'.online', "params" => 'online',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'LIKE', "concat" => 'AND',
                'relatedModel'=>"Operator"
            ],

            [   "field" => $tab.'.disabled', "params" => 'disabled',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
            ],
            [   "field" => 'DATE('.$tab.'.updated_at)', "params" => 'date_to',
                "validate" => ["date" => true,"empty" => true],
                "type" => 'date', "action" => '<=',"concat" =>'AND'
            ],

            [   "field" => 'DATE('.$tab.'.updated_at)', "params" => 'date_from',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '>=', "concat" => 'AND'
            ],
        ];
        $this->filter = array_merge($this->filter,parent::getFilter());
        return $this->filter;
    }
    protected function compileGroupParams() {
        $this->group_params = [
            "select" => [],
            "by" => [
                'operator_id'=>'operator_id'

            ],
            "custom_select" => [],
            "relatedModel" => [
                "Operator" => [
                    "entity" => new User(),
                        "relationship" => ['id','operator_id'],
                ],
                "User" => [
                    "entity" => new User(),
                    "relationship" => ['id','user_id'],
                ],

            ]
        ];
        return $this->group_params;
    }


}
