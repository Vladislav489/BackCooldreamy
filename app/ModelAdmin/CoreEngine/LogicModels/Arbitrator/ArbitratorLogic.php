<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Arbitrator;


use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\Arbitrator\Arbitrator;
use App\Models\Arbitrator\ArbitratorComingidUrl;
use App\Models\Arbitrator\ArbitratorHistoryPay;
use App\Models\Arbitrator\ArbitratorPaymentRule;
use App\Models\Arbitrator\ArbitratorPayUser;
use App\Models\Arbitrator\ArbitratorUsers;
use Illuminate\Database\Eloquent\Model;

class ArbitratorLogic extends CoreEngine {
    public function __construct($params = [],$select = ["*"],$callback = null){
        $this->engine = new Arbitrator();
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
            [   "field" =>$tab.'.id', "params" => 'arbitrator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.id', "params" => 'arbitrator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
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
                "UserArbitrator" => [
                    "entity" => new ArbitratorUsers(),
                    "relationship" => ['id','arbitrator_id'],
                ],
                "UserPayHistory" => [
                    "entity" => new ArbitratorHistoryPay(),
                    "relationship" => ['id','arbitrator_id'],
                ],
                "UserPayUser" => [
                    "entity" => new ArbitratorPayUser(),
                    "relationship" => ['id','arbitrator_id'],
                ],
                "UserComing" => [
                    "entity" => new ArbitratorComingidUrl(),
                    "relationship" => ['id','arbitrator_id'],
                ],
            ]
        ];
        return $this->group_params;
    }
}

