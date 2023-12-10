<?php

namespace App\Repositories\Operator;

use App\Enum\User\RoleEnum;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatLogic;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatTextMessage;
use App\Models\Letter;
use App\Models\LetterMessage;
use App\Models\LetterTextMessage;
use App\Models\Operator\Forwarded;
use App\Models\Operator\OperatorLetterLimit;
use App\Models\OperatorChatLimit;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OperatorRepository
{
    /** @var int */
    const PER_PAGE = 10;

    /**
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator {
        $operators = User::query()->setEagerLoads([])->with(['ancets', 'isInWork', 'delays'])->whereHas('roles', function ($query) {
            $query->where('id',  RoleEnum::OPERATOR);
        });
        return $operators->paginate(self::PER_PAGE);
    }

    public function getOperationUsers(User $user): Collection {
        return OperatorLinkUsers::query()->where("operator_id", $user->id)->get();
    }

    public function findUser($id): User {
        return User::findOrFail($id);
    }

    public function getAnotherUser(Forwarded $forwarded){ return $forwarded;}

    public function getRecepientUserByAncets(Forwarded $entity, array $anketUserIds = []): string|int|null {
        if (!count($anketUserIds)) {
            return null;
        }
        return in_array($entity->first_user_id, $anketUserIds) ? $entity->second_user_id : $entity->first_user_id;
    }


    public function getOperatorByAncets(Forwarded $entity, array $anketUserIds = []): string|int|null{
        if (!count($anketUserIds)) {
            return null;
        }
        return in_array($entity->first_user_id, $anketUserIds) ? $entity->first_user_id : $entity->second_user_id;
    }

    public function getOperatorLastMessages8(User $operator, $page,$pageSize, array $requestData = []){
        $query = ChatMessage::with('sender_user','recepient_user','chat_messageable.gifts', 'chat_messageable.sticker','operator_ansver');
        $ankets = OperatorLinkUsers::all()->pluck('user_id');
        $query->whereIn('sender_user_id',$ankets);
        $query->where("is_ace","=","0");
        $query->where("users.gender",'=','female');
        $query->where("users.is_real",'=',0);
        $query->leftJoin("users",'users.id','=',"sender_user_id");
        $dataFrom = new \DateTime(date("Y-m-d H:i:s"));
        $dataFrom->modify("+3 hours");
        $dataFrom->modify("-8 hours");
        $dataTo = new \DateTime(date("Y-m-d H:i:s"));
        $dataTo->modify("+3 hours");
        $query->whereBetween('chat_messages.created_at',[$dataFrom->format("Y-m-d H:i:s"),$dataTo->format("Y-m-d H:i:s")]);
        $query->orderBy('chat_messages.created_at', 'desc');
        $query->select(DB::raw("chat_messages.*"));
        if(is_null($pageSize))
            $pageSize = 200;
        return $query->paginate($pageSize);
    }


    //возвращает все сообщения аккаунтов который в агенстве за периуд от текущего времени -8 ч
    public function getOperatorLastMessages8Chat(User $operator, $page,$pageSize, array $requestData = []){

        if ($operator->getRoleNames()->toArray()[0] != 'admin') {
            $ankets = OperatorLinkUsers::all()->pluck('user_id');
        }else{
            $ankets = $operator->ancets()->with([])->pluck('user_id');
        }

        $dataFrom = new \DateTime(date("Y-m-d H:i:s"));
        $dataFrom->modify("-8 hours");
        $dataTo= new \DateTime(date("Y-m-d H:i:s"));
        $chats = new ChatLogic([
            'ancet' => $ankets->toArray(),
            'date_to'=>$dataTo->format("Y-m-d H:i:s"),
            'date_from'=>$dataFrom->format("Y-m-d H:i:s")
        ]);
        $combinedBuilder = $chats->setJoin(['ChatMessage'])->setLimit(false)->offPagination()->getFullQuery()->with(['firstUser', 'secondUser','chat_messages8hour'])->orderBy('updated_at', 'desc');

        $results = $combinedBuilder->paginate($pageSize);
        $results->getCollection()->each(function ($item) {
            $firstUser = User::query()->setEagerLoads([])->findOrFail($item->first_user_id);
            $secondUser = User::query()->setEagerLoads([])->findOrFail($item->second_user_id);
            $item->self_user = $secondUser->is_real ? $firstUser : $secondUser;
            $item->other_user = $secondUser->is_real ? $secondUser : $firstUser;
            if ($item->type_of_model == 'chat') {
                $item->last_message = ChatMessage::setEagerLoads([])->with('chat_messageable')->where('chat_id', $item->id)->latest()->first();
            } else {
                $item->last_message = LetterMessage::setEagerLoads([])->with('letter_messageable')->where('letter_id', $item->id)->latest()->first();
            }
            return $item;
        });
        return $results;
    }

    public function getAdminOperatorLastMessages(User $admin, $page, array $requestData = [])
    {

        $ankets = $admin->adminAncets()->with(['user', 'user.rating', 'user.rating.history'])->get();



        $chats = resolve(ChatRepository::class)
            ->index(['anket_ids' => $ankets->pluck('user_id'),

                'search_message' => Arr::get($requestData, 'search'),
                'is_query' => true])
            ->with([])
            ->selectRaw("*, 'chat' as type_of_model");

        $letters = resolve(LetterRepository::class)
            ->index(['anket_ids' => $ankets->pluck('user_id'), 'search_message' => Arr::get($requestData, 'search'), 'is_query' => true])
            ->with([])
            ->selectRaw("*, 'letter' as type_of_model");

        $combinedBuilder = $chats->union($letters)->orderBy('updated_at', 'desc');

        $results = DB::table(DB::raw("({$combinedBuilder->toSql()}) as combined"))
            ->mergeBindings($combinedBuilder->getQuery())
            ->paginate(5);

        $results->getCollection()->each(function ($item) {
            $firstUser = User::query()->setEagerLoads([])->findOrFail($item->first_user_id);
            $secondUser = User::query()->setEagerLoads([])->findOrFail($item->second_user_id);
            $item->self_user = $secondUser->is_real ? $firstUser : $secondUser;
            $item->other_user = $secondUser->is_real ? $secondUser : $firstUser;
            if ($item->type_of_model == 'chat') {
                $item->last_message = ChatMessage::with('chat_messageable')->where('chat_id', $item->id)->latest()->first();
            } else {
                $item->last_message = LetterMessage::with('letter_messageable')->where('letter_id', $item->id)->latest()->first();
            }
            return $item;
        });

        return $results;
    }

    public function getOperatorLastMessages(User $operator, $page,$pageSize, array $requestData = [])
    {
        $params = [
            'search_message' => Arr::get($requestData, 'search'),
            'is_query' => true,
        ];

        if ($operator->getRoleNames()->toArray()[0] != 'admin') {
            $params['operator_id'] = $operator->id;
        }
            $chats = resolve(ChatRepository::class)
                ->index($params)
                ->with([])
                ->where('deleted_by_first_user','=', '0')
                ->where('deleted_by_second_user','=', '0')
                ->where('is_answered_by_operator','=', '0')
                ->selectRaw("'chat' as type_of_model");

        $combinedBuilder = $chats->where('is_answered_by_operator','=', '0')->orderBy('updated_at', 'desc');
        $results = $combinedBuilder->paginate($pageSize);


        $results->getCollection()->each(function ($item) {
            $firstUser = User::query()->setEagerLoads([])->findOrFail($item->first_user_id);
            $secondUser = User::query()->setEagerLoads([])->findOrFail($item->second_user_id);
            $item->self_user = $secondUser->is_real ? $firstUser : $secondUser;
            $item->other_user = $secondUser->is_real ? $secondUser : $firstUser;
            if ($item->type_of_model == 'chat') {
                $item->last_message = ChatMessage::setEagerLoads([])->with('chat_messageable')->where('chat_id', $item->id)->latest()->first();
            } else {
                $item->last_message = LetterMessage::setEagerLoads([])->with('letter_messageable')->where('letter_id', $item->id)->latest()->first();
            }
            return $item;
        });
        return $results;
    }

    public function getOperatorLetterLimits(User $operator, $page,$pageSize, array $requestData = [])
    {
        if ($operator->getRoleNames()->toArray()[0] == 'admin') {
            $ankets = OperatorLinkUsers::all()->pluck('user_id');
        }else{
            $ankets = $operator->ancets()->with([])->pluck('user_id');
        }
        $letters = OperatorLetterLimit::query()->whereIn('girl_id', $ankets)
               ->where(function ($query) {
                $query->where('letter_id', null);
            })->where('limits', ">=", 1)
            ->selectRaw("*, 'letter' as type_of_model");
           if ($filterSearch = Arr::get($requestData, 'search')) {
             $letters->whereHas('man', function ($query) use ($filterSearch) {
                $query->where('name', 'like', "%$filterSearch%")->orWhere('email', 'like', "%$filterSearch%");
            });
        }
        $results = $letters->orderByDesc('updated_at')->paginate($pageSize);
        $results->getCollection()->each(function ($item) {
            $item->girl = User::query()->find($item->girl_id);
            $item->man = User::query()->find($item->man_id);

            return $item;
        });
        return $results;
    }

    public function getOperatorLimits(User $operator, $page,$pageSize, array $requestData = [])
    {

        if ($operator->getRoleNames()->toArray()[0] == 'admin') {
            $ankets = OperatorLinkUsers::all()->pluck('user_id');
        }else{
            $ankets = $operator->ancets()->with([])->pluck('user_id');
        }
        $chats = OperatorChatLimit::query()
            ->leftJoin(DB::raw( " users as man ON man.id  = man_id " ),function(){})
            ->leftJoin(DB::raw( " users as girl ON girl.id  = girl_id" ),function(){})
            ->whereIn('girl_id', $ankets)
            ->whereRaw("(girl.email IS NOT NULL AND man.email IS NOT NULL)",[],'AND')
            ->where(function ($query) {$query->where('chat_id', null);})
            ->where('limits', ">=", 1)
            ->selectRaw("operator_chat_limits.*, 'chat' as type_of_model");

        if ($filterSearch = Arr::get($requestData, 'search')) {
            $chats->whereHas('man', function ($query) use ($filterSearch) {
                $query->where('name', 'like', "%$filterSearch%")->orWhere('email', 'like', "%$filterSearch%");
            });
        }

        $results = $chats->orderByDesc('updated_at')->paginate(40,['*']);

        $results->getCollection()->each(function ($item) {
            $item->girl = User::query()->find($item->girl_id);
            $item->man = User::query()->find($item->man_id);
            return $item;
        });



        return $results;
    }
}
