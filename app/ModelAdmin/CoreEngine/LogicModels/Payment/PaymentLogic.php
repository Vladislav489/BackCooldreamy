<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\Payment;


use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\User\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PaymentLogic extends CoreEngine {
    public function __construct($params = [], $select = ["*"], $callback = null){
        $this->engine = new Payment();
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



    protected function getFilter(){
        $tab = $this->engine->getTable();
        $this->filter = [
            ["field" => $tab . '.user_id', "params" => 'user',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            ["field" => $tab . '.status', "params" => 'status',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '=', "concat" => 'AND',
            ],
            ["field" => $tab . '.status', "params" => 'price',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            ["field" => 'DATE(created_at)', "params" => 'date_from',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND',
            ],
            ["field" => 'DATE(created_at)', "params" => 'date_to',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND',
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
                'model_id'=>'model_id',
            ],
            "custom_select" => [],
            "relatedModel" => [
                "User" => [
                    "entity" => new User(),
                    "relationship" => ['id', 'model_id'],
                    "type"=>"left"
                ],
            ]
        ];
        return $this->group_params;
    }
}
