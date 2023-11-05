<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\Operator;


use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\Operator\OperatorForfeit;
use App\Models\User;
use App\Repositories\Operator\ChatRepository;
use Illuminate\Database\Eloquent\Model;

class OperatorForfeitsLogic extends CoreEngine
{
    public function __construct($params = [], $select = ["*"], $callback = null)
    {
        $this->engine = new OperatorForfeit();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }



    public  static function add($operator_id,$message_id,$chat_id){
        OperatorForfeit::insert(
           [
               'operator_id'=>$operator_id,
               'message_id'=>$message_id,
               'chat_id'=>$chat_id,
               'created_at' => date("Y-m-d H:i:s"),
               'updated_at' => date("Y-m-d H:i:s")
           ]
       );
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
