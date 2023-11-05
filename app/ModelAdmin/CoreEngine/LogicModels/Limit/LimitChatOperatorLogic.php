<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Limit;

use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\AceSystems\AceSystemLimitAssignment;
use App\Models\LimitSystem\LimitSystemLimitAssignment;
use App\Models\LimitSystem\LimitSystemUser;
use App\Models\PromptCareer;
use App\Models\PromptFinanceState;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LimitChatOperatorLogic extends CoreEngine {

    public function __construct($params = [], $select = ["*"], $callback = null)
    {
        $this->engine = new  LimitSystemUser();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params, $select);
    }

    public function randProcent(array $list)
    {
        $rand = rand(1, 100);
        $summ = 0;
        $summ2 = 0;
        foreach ($list as $key => $value) {
            $summ2 = $summ - $summ2; // значение от
            $summ = $summ + $key;// значение до
            if ($summ > 100)
                return false;

            if ($rand <= $summ and $rand >= $summ2)
                return $value;
        }
    }

    public function addRegistaration(User $user)
    {

        $Assignment = LimitSystemLimitAssignment::query()
            ->where('group_id', '=', 1)->where('sort', '=', 1)->first();
        $data = [
            "user_id" => $user->id,
            "group_id" => 1,
            'active' => 0,
            "last_assignments_id" => $Assignment['id'],
            "last_assignments_sort" => $Assignment['sort'],
            "step_cron_counter" => rand($Assignment['step_from'], $Assignment['step_to']),
        ];
        get_class($this->engine)::create($data);
        return true;
    }

    public function changeUserGroup(User $user, $group_id){

        $Assignment = LimitSystemLimitAssignment::query()
                ->where('group_id', '=', $group_id)->where('sort', '=', 1)->first();
        $rezUser = ($this->engine)::query()->where('user_id','=',$user->id)->first();

        if($rezUser) {
            get_class($this->engine)::query()->where('user_id','=',$user->id)->update([
                "group_id" => $group_id,
                "last_assignments_id" => $Assignment['id'],
                "last_assignments_sort" => $Assignment['sort'],
                "step_cron_counter" => $Assignment['id'],
            ]);
        }
            return true;
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
            ["field" => $tab . '.user_id', "params" => 'user',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],


            ["field" => $tab . '.step_cron_counter', "params" => 'step',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            ["field" => $tab . '.step_cron_counter', "params" => 'step_not',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '!=', "concat" => 'AND',
            ],
            ["field" => $tab . '.active', "params" => 'active',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            ["field" => $tab . '.last_assignments_id', "params" => 'assignments',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            ["field" => $tab . '.last_assignments_sort', "params" => 'pos',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
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
                    "entity" => new User(),
                    "relationship" => ['id', 'user_id'],
                ],
            ]
        ];
        return $this->group_params;
    }
}
