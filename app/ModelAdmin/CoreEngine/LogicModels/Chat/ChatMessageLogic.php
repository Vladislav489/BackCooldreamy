<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Chat;
use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\ChatGiftMessage;
use App\Models\ChatImageMessage;
use App\Models\ChatMessage;
use App\Models\ChatStickerMessage;
use App\Models\ChatTextMessage;
use App\Models\ChatWinkMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChatMessageLogic extends CoreEngine {
    public function __construct($params = [],$select = ["*"],$callback = null){
        $this->engine = new ChatMessage();
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

    public function getAverageTimeFirstMessage(){
        $this->setJoin(['OperatorChat','OperatorUser']);
        $this->setSelect([
            DB::raw("sec_to_time(CEILING(SUM(CEILING((SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE tt.chat_id = chat_messages.chat_id LIMIT 1,1 ) -(SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE tt.chat_id = chat_messages.chat_id LIMIT 0,1)))/SUM(1))) AS time_first_message"),
        ]);
        return $this->offPagination()->getGroup();
    }

    public function getAverageTimeFirstMessageOperator(){
       $this->setJoin(['OperatorChat','OperatorUser']);
       $this->setGroupBy(['model_id']);
       $this->setSelect([
           DB::raw("DISTINCT sec_to_time(CEILING(( (SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE tt.chat_id = chat_messages.chat_id LIMIT 1,1 ) -
                    (SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE tt.chat_id = chat_messages.chat_id LIMIT 0,1 )) ) ) AS time_first_message"),
           DB::raw("OperatorChat.moAVGdel_id as operator_id"),
           DB::raw("users.name as operator_name"),
       ]);
       return $this->offPagination()->OnDebug()->getGroup();
   }

    public function getAverageCountSendManMessage(){
       $this->setJoin(['OUserSender']);
       $this->getTotal();
   }

    public function getAverageTimeMessage(){
        $this->setJoin(['OperatorChat','OperatorUser']);
        $this->setSelect([
            DB::raw("DISTINCT IFNULL(sec_to_time(
                (SELECT sec_to_time(AVG(UNIX_TIMESTAMP(created_at))) FROM chat_messages AS tt WHERE   tt.chat_id = chat_messages.chat_id) -
                (SELECT  UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE   tt.chat_id = chat_messages.chat_id
               )'нет Ответа') AS time_first_message"),
        ]);
        return $this->offPagination()->getGroup();
    }

    public function  getTimeFirstMessage(){
        $this->setJoin(['OperatorChat','OperatorUser']);
        $this->setSelect([
            DB::raw("DISTINCT IFNULL(sec_to_time(
                (SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE  tt.chat_id = chat_messages.chat_id LIMIT 1,1 ) -
                (SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE  tt.chat_id = chat_messages.chat_id  LIMIT 0,1 )
                                         ),
               'нет Ответа') AS time_first_message"),
            DB::raw("chat_messages.chat_id"),
            DB::raw("chat_messages.sender_user_id"),
            DB::raw("chat_messages.recepient_user_id"),
            DB::raw("OperatorChat.model_id as operator_id"),
            ]);
        return $this->offPagination()->getList();
    }

    public function getChatNotReadUser($user_id,$chat_ids){
        $chat_ids = (!is_array($chat_ids))?[$chat_ids]:$chat_ids;
        $chatMessage = new ChatMessageLogic([
            'chat_id' => $chat_ids,
            'recepient' => (string)$user_id,
            'read_by_recepient' =>'0'
        ],
            [DB::raw("COUNT(*) as unread_messages_count")]);
        $countNotReadMessage =  $chatMessage->setGroupBy(['chat_id'])->offPagination()->getGroup()['result'];
        return $countNotReadMessage;
    }
    public function  getChatLastMessage($user_id,$chat_ids){
        $chat_ids = (!is_array($chat_ids))?[$chat_ids]:$chat_ids;
        $chatMessage = new ChatMessageLogic([
            'chat_id' => $chat_ids,
        ],[DB::raw("MAX(".$this->engine->getTable().".id) as id"),
            DB::raw("ChatMessageSub.chat_messageable_type"),
            DB::raw("ChatMessageSub.disabled"),
            DB::raw("ChatMessageSub.operator_get_ansver"),
            DB::raw("ChatMessageSub.is_payed"),
            DB::raw("ChatMessageSub.is_read_by_recepient")]);
        //['ChatMessageSub','TextMessageSub','ImageMessageSub','GiftMessageSub','StickerMessageSub','WinkMessageSub']
        $lastMessage = $chatMessage->setJoin(
            ['ChatMessageSub','TextMessageSub','GiftMessageSub','StickerMessageSub','WinkMessageSub'])
            ->setGroupBy(['chat_id'])->offPagination()->getGroup()['result'];
        foreach ($lastMessage as &$item){
            $item['chat_messageable'] = json_decode($item['chat_messageable'],true);
        }
        return $lastMessage;
    }


    protected function getFilter(){
        $tab = $this->engine->getTable();
        $tabUser = (new User())->getTable();
        $this->filter = [
            [   "field" =>$tab.'.sender_user_id', "params" => 'sender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.sender_user_id', "params" => 'not_sender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '!=', "concat" => 'AND',
            ],
            [   "field" =>$tab.'.recepient_user_id', "params" => 'recepient',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
            [   "field" =>$tabUser.'.gender', "params" => 'gender',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel'=>['UserSender','UserRecepient']
            ],

            [   "field" => $tabUser.'.state', "params" => 'state',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND'
            ],
            [   "field" =>$tabUser.'.country', "params" => 'country',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND'
            ],

            [   "field" => $tabUser.'.is_real', "params" => 'is_real',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND'
            ],

            [   "field" =>$tab.'.is_ace', "params" => 'is_ace',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND'
            ],
            [   "field" =>$tab.'.chat_id', "params" => 'chat_id',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],

            [   "field" => 'DATE('.$tab.'.created_at)', "params" => 'date_to',
                "validate" => ["date" => true,"empty" => true],
                "type" => 'date', "action" => '<=',"concat" =>'AND'
            ],

            [   "field" => 'DATE('.$tab.'.created_at)', "params" => 'date_from',
                "validate" => ["date" => true, "empty" => true],
                "type" => 'date', "action" => '>=', "concat" => 'AND'
            ],

            [   "field" => 'is_read_by_recepient', "params" => 'read_by_recepient',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string', "action" => '=', "concat" => 'AND',
                'relatedModel'=>'OperatorChat'
            ],

            [   "field" => 'OperatorChat.model_id', "params" => 'operator',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '=', "concat" => 'AND',
                'relatedModel'=>'OperatorChat'
            ],

            [   "field" => 'OperatorChat.model_id', "params" => 'operator_not',
               // "defaultValue"=>'null',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => '!=', "concat" => 'AND',
                'relatedModel'=>'OperatorChat'
            ],

            [   "field" => 'OperatorChat.role_id', "params" => 'role',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
                'relatedModel'=>'OperatorChat'
            ],

        ];
        $this->filter = array_merge($this->filter,parent::getFilter());
        return $this->filter;
    }
    protected function compileGroupParams() {
        $this->group_params = [
            "select" => [],
            "by" => [
                'operator_id'=>'operator_id',
                'chat_id'=>'chat_id',
                'model_id'=>'OperatorChat.model_id'
            ],
            "custom_select" => [],
            "relatedModel" => [
                "UserSender" => [
                    "entity" => new User(),
                    "relationship" => ['id','sender_id'],
                ],
                "UserRecepient" => [
                    "entity" => new User(),
                    "relationship" => ['id','recepient_id'],
                ],
                "ChatMessageSub" =>[
                "entity" => DB::raw((new ChatMessage())->getTable()." as ChatMessageSub  ON
                         chat_messages.id = ChatMessageSub.id "),
            ]   ,
                "OperatorChat" =>[
                    "entity" => DB::raw("(SELECT  Message.chat_id,Message.sender_user_id, model_has_roles.model_id ,model_has_roles.role_id  from `model_has_roles`
                                              left join chat_messages as Message ON
                                                  Message.sender_user_id IN (
                                                           (SELECT user_id
                                                            FROM operator_link_users
                                                            WHERE operator_id = model_id)
                                                      )
                                                where Message.sender_user_id != 'null'
                                                GROUP BY Message.chat_id,Message.sender_user_id,model_has_roles.model_id,model_has_roles.role_id ) AS OperatorChat
                                                ON  chat_messages.chat_id =  OperatorChat.chat_id
                                                "),

                ],
                "OperatorUser" => [
                    "entity" => new User(),
                    "relationship" => ['id','OperatorChat.model_id'],
                ],
                "TextMessage" => [
                    "entity" => new ChatTextMessage(),
                    "relationship" => ['id','chat_messageable_id'],
                    'field'=>['text as message_body']
                ],
                "WinkMessage" => [
                    "entity" => new ChatWinkMessage(),
                    "relationship" => ['id','chat_messageable_id'],
                    'field'=>['from_user_id','to_user_id']
                ],
                "ImageMessage" => [
                    "entity" => new ChatImageMessage(),
                    "relationship" => ['id','chat_messageable_id'],
                    'field'=>['thumbnail_url as message_body','image_url as message_body']
                ],
                "GiftMessage" => [
                    "entity" => new ChatGiftMessage(),
                    "relationship" => ['id','chat_messageable_id'],
                    'field'=>['id as message_body']
                ],
                "StickerMessage" => [
                    "entity" => new ChatStickerMessage(),
                    "relationship" => ['id','recepient_id'],
                    'field'=>['id as  message_body']
                ],
                "TextMessageSub" => [
                    "entity" =>DB::raw((new ChatTextMessage())->getTable()." as TextMessageSub  ON
                         (ChatMessageSub.chat_messageable_id = TextMessageSub.id  AND
                         ChatMessageSub.chat_messageable_type LIKE '%ChatTextMessage%')"),
                    'field'=>['json_object("id",TextMessageSub.id,"text",TextMessageSub.text,"gifts",NULL,"sticker",NULL) as chat_messageable']
                ],
                "WinkMessageSub" => [
                    "entity" =>DB::raw((new ChatWinkMessage())->getTable()." as WinkMessageSub  ON
                         (ChatMessageSub.chat_messageable_id = WinkMessageSub.id  AND
                         ChatMessageSub.chat_messageable_type LIKE '%ChatWinkMessage%')"),
                    'field'=>['json_object("id",WinkMessageSub.id,"from_user_id",WinkMessageSub.from_user_id,
                    "to_user_id ",WinkMessageSub.to_user_id,"gifts",NULL,"sticker",NULL) as chat_messageable']
                ],
                "ImageMessageSub" => [
                    "entity" =>DB::raw((new ChatImageMessage())->getTable()." as ImageMessageSub  ON
                         (ChatMessageSub.chat_messageable_id = ImageMessageSub.id  AND
                         ChatMessageSub.chat_messageable_type LIKE '%ChatImageMessage%')"),
                    'field'=>['json_object("id",ImageMessageSub.id,"thumbnail_url",
                    ImageMessageSub.thumbnail_url,"image_url",ImageMessageSub.image_url,"gifts",NULL,"sticker",NULL) as chat_messageable']
                ],
                "GiftMessageSub" => [
                    "entity" =>DB::raw((new ChatGiftMessage())->getTable()." as GiftMessageSub ON
                         (ChatMessageSub.chat_messageable_id = GiftMessageSub.id  AND
                         ChatMessageSub.chat_messageable_type LIKE '%ChatGiftMessage%')"),
                    'field'=>['json_object("id",GiftMessageSub.id,"gifts",NULL,"sticker",NULL) as chat_messageable']
                ],
                "StickerMessageSub" => [
                    "entity" => new ChatStickerMessage(),
                    "entity" =>DB::raw((new ChatStickerMessage())->getTable()." as StickerMessageSub ON
                         (ChatMessageSub.chat_messageable_id = StickerMessageSub.id  AND
                         ChatMessageSub.chat_messageable_type LIKE '%ChatStickerMessage%')"),
                    'field'=>['json_object("id",StickerMessageSub.id,"gifts",NULL,"sticker",NULL) as chat_messageable']
                ],
            ]
        ];
        return $this->group_params;
    }

}
