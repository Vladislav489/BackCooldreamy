<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\Ace;



use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\AceSystems\AceSystemLimitAssignment;
use App\Models\AceSystems\AceSystemUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AceLogic extends CoreEngine {
    public function __construct($params = [],$select = ["*"],$callback = null){
        $this->engine = new AceSystemUser();
        $this->query = $this->engine->newQuery();
        $this->getFilter();
        $this->compileGroupParams();
        parent::__construct($params,$select);
    }

    public function  randProcent(array $list){
        $rand = rand(1,100);
        var_dump($rand);
        $summ = 0;
        $summ2 = 0;
        foreach ( $list as $key => $value){
            $summ2 = $summ - $summ2; // значение от
            $summ = $summ + $key;// значение до
            if($summ > 100)
                return false;

            if ($rand <= $summ and $rand >= $summ2  )
                return $value;
        }
    }

    public function addNewUser(User $user){
        //try {
           // $group_id = (empty($user->avatar_url)) ? 2 : 1;
            $Assignment = AceSystemLimitAssignment::query()
                ->where('group_id', '=', 1)->where('sort', '=', 1)->first();
               $data = [
                    "user_id" => $user->id,
                    "group_id" => 1,
                    'active' => 0,
                    "last_assignments_id" => $Assignment['id'],
                    "last_assignments_sort" => $Assignment['sort'],
                    "step_cron_counter" => rand($Assignment['step_from'],$Assignment['step_to']),
                ];


            get_class($this->engine)::create($data);
            return true;
        //}catch (\Throwable $e){

          //  logger("AceLogic addNewUser ".$e->getMessage());
          //  return  false;
       // }
    }

    public function changeUserGroup(User $user ,$group_id){
        try {
            $Assignment = AceSystemLimitAssignment::query()
                ->where('group_id', '=', $group_id)->where('sort', '=', 1)->first();
            get_class($this->engine)::create([
                "user_id" => $user->id,
                "group_id" => $group_id,
                'active' => 0,
                "last_assignments_id" => $Assignment['id'],
                "last_assignments_sort" => $Assignment['sort'],
                "step_cron_counter" => $Assignment['id'],
            ]);
            return true;
        }catch (\Throwable $e){
            logger("AceLogic addNewUser ".$e->getMessage());
            return  false;
        }
    }


    protected function defaultSelect(){
        $tab = $this->engine->tableName();
        $this->default = [];
        return $this->default;
    }

    protected function getFilter(){
        $tab = $this->engine->getTable();
        $this->filter = [
            [   "field" =>$tab.'.user_id', "params" => 'user',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.step_cron_counter', "params" => 'step',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.step_cron_counter', "params" => 'step_not',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '!=', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.active', "params" => 'active',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.last_assignments_id', "params" => 'assignments',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            [   "field" =>$tab.'.last_assignments_sort', "params" => 'pos',
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
            "by" => [],
            "custom_select" => [],
            "relatedModel" => [
                "User" => [
                    "entity" => new User(),
                    "relationship" => ['id','user_id'],
                ],
            ]
        ];
        return $this->group_params;
    }
}
