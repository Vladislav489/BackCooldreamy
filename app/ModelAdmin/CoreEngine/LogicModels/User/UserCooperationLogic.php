<?php


namespace App\ModelAdmin\CoreEngine\LogicModels\User;

use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserCooperationLogic extends CoreEngine
{
    public function __construct($params = [], $select = ["*"], $callback = null)
    {
        $this->engine = new User\UserCooperation();
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
            ["field" => $tab . '.user_id', "params" => 'user',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
            ],
            ["field" => 'User.gender', "params" => 'gender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel' => "User"
            ],

            ["field" => 'User.search_gender', "params" => 'search_gender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel' => "User"
            ],
            ["field" => 'User.state', "params" => 'state',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel' => "User"
            ],

            ["field" => 'User.country', "params" => 'country',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel' => "User"
            ],

            ["field" => 'DATE(' . $tab . '.created_at)', "params" => 'date_to',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '<=', "concat" => 'AND'
            ],

            ["field" => 'DATE(' . $tab . '.created_at)', "params" => 'date_from',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND'
            ],

            ["field" =>"(SELECT COUNT(*) FROM ".(new ChatMessage())->getTable()." as message_chat
            WHERE  message_chat.sender_user_id = ". $tab .".user_id  )",
                "params" => 'count_message_more',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '>=', "concat" => 'AND'
            ],
            ["field" =>"(SELECT COUNT(*) FROM ".(new ChatMessage())->getTable()." as message_chat
            WHERE  message_chat.sender_user_id = ". $tab .".user_id  )",
                "params" => 'count_message_less',
                "validate" => ["datetime" => true, "empty" => true],
                "type" => 'datetime', "action" => '=<', "concat" => 'AND'
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
                    "entity" => (new User())->getTable() . " as User",
                    "relationship" => ['id', 'user_id'],
                    "type" => "left"
                ],

            ]
        ];
        return $this->group_params;
    }
}

