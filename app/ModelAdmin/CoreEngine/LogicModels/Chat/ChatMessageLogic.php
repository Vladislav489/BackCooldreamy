<?php
namespace App\ModelAdmin\CoreEngine\LogicModels\Chat;
use App\ModelAdmin\CoreEngine\Core\CoreEngine;
use App\Models\Chat\Chat;
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
        $this->engine = new \App\Models\Chat\ChatMessage();
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


    public function getList(){
       $result =  parent::getList();
       $result['result'] = $this->getCurrentChatMessageable($result['result']);
       return $result;
    }


    public function getAverageCountSendManMessage(){
       $this->setJoin(['UserSender']);
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
        $chechData = ['text','wink','image','gift','sticker'];
        $chat_ids = (!is_array($chat_ids))?[$chat_ids]:$chat_ids;
        $chatMessageJoin = new ChatMessageLogic([
            'chat_id' => $chat_ids,
        ],[ DB::raw("MAX(chat_messages.id) as id")]);
        $ids = $chatMessageJoin->setGroupBy(['chat_id'])->executeGroup()->getQuery()->pluck('id')->toArray();
        if(count($ids) == 0)
            return  [];
        foreach ($ids as &$item)
            $item = (string)$item;

        $chatMessage = new ChatMessageLogic(['message_id'=>$ids],
            ['id','chat_id','sender_user_id','recepient_user_id','chat_messageable_id','chat_messageable_type',
                'is_read_by_recepient', 'disabled', 'created_at', 'updated_at', 'is_payed' , 'is_ace']);
        $lastMessage = $chatMessage->offPagination()
            ->setJoin(['TextMessageSub','WinkMessageSub','ImageMessageSub','GiftMessageSub','StickerMessageSub'])
            ->getList();
        if(isset($lastMessage['result'])){
            $lastMessage = $lastMessage['result'];
            foreach ($lastMessage as $key => $item){
                foreach ($chechData as $field){
                    if(isset($item['chat_messageable_'.$field]) && !is_null($item['chat_messageable_'.$field])) {
                        $item['chat_messageable'] = json_decode($item['chat_messageable_' . $field], true);
                        unset($item['chat_messageable_' . $field]);
                    } else {
                        unset($item['chat_messageable_'.$field]);
                    }
                }
                $lastMessage[$key] = $item;
            }
            return $lastMessage;
        }
        return [];
    }

    public function  getChatMessage($user_id,$chat_ids){

        $chat_ids = (!is_array($chat_ids))?[$chat_ids]:$chat_ids;
        $chatMessage = new ChatMessageLogic(['chat_id'=>$chat_ids],
            ['id','chat_id','chat_messageable_id','chat_messageable_type',
                'is_read_by_recepient', 'disabled', 'created_at', 'updated_at', 'is_payed' , 'is_ace']);
        return  $chatMessage->offPagination()
            ->setJoin(['TextMessageSub','WinkMessageSub','ImageMessageSub','GiftMessageSub','StickerMessageSub'])
            ->getList();
    }

    public function getCurrentChatMessageable($lastMessage){
        $chechData = ['text','wink','image','gift','sticker'];
        foreach ($lastMessage as $key => $item){
            foreach ($chechData as $field){
                if(isset($item['chat_messageable_'.$field]) && !is_null($item['chat_messageable_'.$field])) {
                    $item['chat_messageable'] = json_decode($item['chat_messageable_' . $field], true);
                    unset($item['chat_messageable_' . $field]);
                } else {
                    unset($item['chat_messageable_'.$field]);
                }
            }
            $lastMessage[$key] = $item;
        }
        return $lastMessage;
    }


    protected function getFilter(){
        $tab = $this->engine->getTable();
        $tabUser = (new User())->getTable();
        $this->filter = [
            [   "field" =>$tab.'.id', "params" => 'message_id',
                "validate" => ["string" => true, "empty" => true],
                "type" => 'string|array', "action" => 'IN', "concat" => 'AND',
            ],
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
                         (chat_messageable_id = TextMessageSub.id  AND
                         chat_messageable_type LIKE '%ChatTextMessage%')"),
                    'field'=>['IF(TextMessageSub.id IS NULL,NULL,json_object("id",TextMessageSub.id,"text",TextMessageSub.text,"gifts",NULL,"sticker",NULL)) as chat_messageable_text']
                ],
                "WinkMessageSub" => [
                    "entity" =>DB::raw((new ChatWinkMessage())->getTable()." as WinkMessageSub  ON
                         (chat_messageable_id = WinkMessageSub.id  AND
                         chat_messageable_type LIKE '%ChatWinkMessage%')"),
                    'field'=>['IF(WinkMessageSub.id IS NULL,NULL,json_object("id",WinkMessageSub.id,"from_user_id",WinkMessageSub.from_user_id,
                    "to_user_id ",WinkMessageSub.to_user_id,"gifts",NULL,"sticker",NULL)) as chat_messageable_wink']
                ],
                "ImageMessageSub" => [
                    "entity" =>DB::raw((new ChatImageMessage())->getTable()." as ImageMessageSub  ON
                         (chat_messageable_id = ImageMessageSub.id  AND
                         chat_messageable_type LIKE '%ChatImageMessage%')"),
                    'field'=>['IF(ImageMessageSub.id IS NULL,NULL, json_object("id",ImageMessageSub.id,"thumbnail_url",
                    ImageMessageSub.thumbnail_url,"image_url",ImageMessageSub.image_url,"gifts",NULL,"sticker",NULL)) as chat_messageable_image']
                ],
                "GiftMessageSub" => [
                    "entity" =>DB::raw((new ChatGiftMessage())->getTable()." as GiftMessageSub ON
                         (chat_messageable_id = GiftMessageSub.id  AND
                         chat_messageable_type LIKE '%ChatGiftMessage%')"),
                    'field'=>['IF(GiftMessageSub.id IS NULL,NULL,json_object("id",GiftMessageSub.id,"gifts",NULL,"sticker",NULL)) as chat_messageable_gift']
                ],
                "StickerMessageSub" => [
                    "entity" => new ChatStickerMessage(),
                    "entity" =>DB::raw((new ChatStickerMessage())->getTable()." as StickerMessageSub ON
                         (chat_messageable_id = StickerMessageSub.id  AND
                         chat_messageable_type LIKE '%ChatStickerMessage%')"),
                    'field'=>['IF(StickerMessageSub.id IS NULL,NULL,json_object("id",StickerMessageSub.id,"gifts",NULL,"sticker",NULL)) as chat_messageable_sticker']
                ],
            ]
        ];
        return $this->group_params;
    }

}
