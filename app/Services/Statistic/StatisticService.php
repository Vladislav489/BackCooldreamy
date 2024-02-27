<?php

namespace App\Services\Statistic;

use App\Enum\Operator\WorkingShiftStatusEnum;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatMessageLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Limit\OperatorChatLimitLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorCreditsLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorForfeitsLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLinksUserLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorWorkingAnswerLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorWorkingShiftLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Payment\PaymentLogic;
use App\Models\Operator\WorkingShiftLog;
use App\Models\OperatorLinkUsers;
use App\Models\User;
use App\Models\UserPayedMessagesToOperators;
use App\Repositories\Operator\AnketRepository;
use App\Repositories\Operator\ChatRepository;
use App\Repositories\Operator\DelayRepository;
use App\Repositories\Operator\LetterRepository;
use App\Repositories\Operator\OperatorRepository;
use App\Repositories\Operator\PaymentRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticService
{
    /** @var OperatorRepository */
    private OperatorRepository $operatorRepository;

    /** @var AnketRepository */
    private AnketRepository $anketRepository;

    /** @var PaymentRepository */
    private PaymentRepository $paymentRepository;

    /** @var LetterRepository */
    private LetterRepository $letterRepository;

    /** @var ChatRepository */
    private ChatRepository $chatRepository;

    /** @var DelayRepository */
    private DelayRepository $delayRepository;

    public function __construct(
        PaymentRepository $paymentRepository,
        OperatorRepository $operatorRepository,
        AnketRepository $anketRepository,
        ChatRepository $chatRepository,
        LetterRepository $letterRepository,
        DelayRepository $delayRepository,
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->operatorRepository = $operatorRepository;
        $this->anketRepository = $anketRepository;
        $this->chatRepository = $chatRepository;
        $this->letterRepository = $letterRepository;
        $this->delayRepository = $delayRepository;
    }

    /**
     * @return array[]
     */
    public function getStatistic(): array
    {
        return [
            'count_ankets' => $this->anketRepository->getCount(),
            'count_last_ankets' => $this->anketRepository->getCount(['last_month' => 1]),
            'count_used_anket' => $this->anketRepository->getActive(),
            'count_message' => $this->chatRepository->getCountMessage(),
            'count_last_message' => $this->chatRepository->getCountMessage(['last_month' => 1]),
            'count_letters' => $this->letterRepository->getCountMessage(),
            'count_last_letters' => $this->letterRepository->getCountMessage(['last_month' => 1]),
            'month_balance' => $this->paymentRepository->getBalance(['last_month' => 1]),
            'last_month_balance' => $this->paymentRepository->getBalance(['last_month' => 2, 'to_month' => 1]),
            'day_balance' => $this->paymentRepository->getBalance(['last_day' => 0]),
            'last_day_balance' => $this->paymentRepository->getBalance(['last_day' => 1]),
            'avg_response' => $this->delayRepository->getAvgResponse(),
            'last_avg_response' => $this->delayRepository->getAvgResponse(['last_month' => 1]),
            'avg_delay' => $this->delayRepository->getDelay(),
            'last_avg_delay' => $this->delayRepository->getDelay(['last_month' => 1]),
        ];
    }

    public function getAverageMessageFirstTime(Request $request){
        $chatMessage = new ChatMessageLogic($request->all());
        return $chatMessage->getAverageTimeFirstMessage();
    }

    public function getAverageMessageFirstTimeOperator(Request $request){
        $chatMessage = new ChatMessageLogic($request->all());
        return $chatMessage->getAverageTimeFirstMessageOperator();
    }
    public function getMessageFirstTime(Request $request){
        $chatMessage = new ChatMessageLogic($request->all());
        return $chatMessage->getTimeFirstMessage();
    }

    public function getMessageTime(Request $request){
        $chatMessage = new ChatMessageLogic($request->all());
        return $chatMessage->getAverageTimeMessage();
    }

    public function getBalanceStripe(Request $request){
      $params = $request->all();
      $params['status'] = (isset($params['status']))?$params['status']:'success';
      $paySum = new  PaymentLogic($params,[DB::raw('IFNULL(SUM(price),0) as balance')]);
      return  $paySum->offPagination()->setLimit(false)->getList();
    }

    public function getCreditsBalance(Request $request)
    {
        $params = $request->all();
        $sum = new OperatorCreditsLogic($params, [DB::raw('IFNULL(SUM(credits), 0) as balance')]);
        $sum->getFullQuery()->whereRaw("operator_id IN (SELECT model_has_roles.model_id FROM model_has_roles WHERE role_id = 2)");
        return $sum->offPagination()->setLimit(false)->getList();
    }

    public function getOperatorListStatisticAdmin(Request $request){
        $params = $request->all();
        $operatorLiks = new OperatorLinksUserLogic([], [DB::raw("COUNT(id)")]);
        $operatorLiks->setQuery($operatorLiks->offPagination()->getFullQuery()->whereRaw("operator_id = model_has_roles.model_id"));
        //однотипные
        //date_from date_to
        $workingParams['status'] = WorkingShiftStatusEnum::INACTIVE;
        $workingShit =  new OperatorWorkingShiftLogic(array_merge($params,$workingParams), [DB::raw("COUNT(*)")]);
        $workingShit->setQuery($workingShit->offPagination()->getFullQuery()->whereRaw("user_id = model_has_roles.model_id",[],'AND'));

        $workingStatus =  new OperatorLinksUserLogic(array_merge([],$workingParams),
            [DB::raw("IF(COUNT(*) = SUM(operator_work),1,0)")]);
        $workingStatus->setQuery($workingStatus->offPagination()->getFullQuery()
            ->groupBy('operator_id')
            ->whereRaw("operator_id = model_has_roles.model_id",[],'AND'));



        $workingParams['status'] = WorkingShiftStatusEnum::ACTIVE;
        $workingShitTimeWork =  new OperatorWorkingShiftLogic(array_merge($params,$workingParams),
            [DB::raw("SEC_TO_TIME(SUM(TIME_TO_SEC(date_to) - TIME_TO_SEC(date_from))) as time_work")]);
        $workingShitTimeWork->setQuery($workingShitTimeWork->offPagination()->getFullQuery()->groupBy('user_id')->whereRaw("user_id = model_has_roles.model_id",[],'AND'));


        $workingParams['status'] = WorkingShiftStatusEnum::ACTIVE;
        $workingShitDayWorkTemp =  new OperatorWorkingShiftLogic(array_merge($params,$workingParams),
            [DB::raw("COUNT(*) as day,DATE(created_at) as created_at ,user_id")]);
        $workingShitDayWorkTemp->setQuery($workingShitDayWorkTemp->offPagination()->getFullQuery()
            ->groupBy(DB::raw('DATE(created_at)'))
            ->whereRaw("(date_to IS NOT NULL AND date_from IS NOT NULL )",'AND')
            ->whereRaw("user_id = model_has_roles.model_id",[],'AND'));


        $workingShitDayWork = new OperatorWorkingShiftLogic($params,[DB::raw("COUNT(*) as as day_work")]);
        $workingShitDayWork->setQuery($workingShitDayWork->offPagination()->setLimit(false)->getFullQuery()->select([DB::raw("COUNT(*)  as day_work")])->from(DB::raw("(".$workingShitDayWorkTemp->getSqlToStrFromQuery().") AS `work_day`"))
            ->whereRaw("work_day.user_id = model_has_roles.model_id",[],'AND'));
        $workingParamsTimePused['status'] = WorkingShiftStatusEnum::PAUSE;
        $workingShitTimePused =  new OperatorWorkingShiftLogic(array_merge($params,$workingParamsTimePused),
            [DB::raw("SEC_TO_TIME(SUM(TIME_TO_SEC(IFNULL(date_to,'0000-00-00 00:00:00')) - TIME_TO_SEC(IFNULL(date_from,'0000-00-00 00:00:00')))) as pused_work")]);
        $workingShitTimePused
            ->setQuery($workingShitTimePused->offPagination()->setLimit(false)->getFullQuery()->groupBy('user_id')
            ->whereRaw("user_id = model_has_roles.model_id",[],'AND'));


        $workingShitMessageOperator = new OperatorWorkingAnswerLogic($params,[DB::raw("IFNULL(COUNT(*), 0)  as count_message_operator")]);
        $workingShitMessageOperator->setQuery($workingShitMessageOperator->offPagination()->setLimit(false)
            ->getFullQuery()->whereRaw("working_shift_anser_operators.operator_id = model_has_roles.model_id",[],'AND'));


        $countMenAswer = new OperatorWorkingAnswerLogic($params,[DB::raw("COUNT(DISTINCT(man_id)) as count_message_operator")]);
        $countMenAswer->setQuery($countMenAswer->offPagination()->setLimit(false)
            ->getFullQuery()->whereRaw("working_shift_anser_operators.operator_id = model_has_roles.model_id",[],'AND'));


        ////////////

        $joinMessage = new  OperatorLogic(array_merge($params,['is_ace'=>'0']),[DB::raw("Message.chat_id,Message.sender_user_id,model_has_roles.model_id ,model_has_roles.role_id")]);
        $joinMessage->offPagination()->setJoin(['Message'])
            ->getFullQuery()->groupBy(['Message.chat_id','Message.sender_user_id','model_has_roles.model_id','model_has_roles.role_id']);


        $chatMessage = new ChatMessageLogic(array_merge($params,['is_ace'=>'0']),[
            DB::raw("DISTINCT
                        	SEC_TO_TIME(CEILING(SUM(CEILING(( (SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE tt.chat_id = chat_messages.chat_id LIMIT 1,1 ) -
                        (SELECT UNIX_TIMESTAMP(created_at) FROM chat_messages AS tt WHERE tt.chat_id = chat_messages.chat_id LIMIT 0,1 )) ) ) / COUNT(*))) AS time_first_message")
        ]);
        $chatMessage->setQuery($chatMessage->offPagination()->getFullQuery()
            ->leftJoin(DB::raw("(".$joinMessage->getSqlToStr().") as OperatorChat ON  chat_messages.chat_id =  OperatorChat.chat_id"),function(){})
           ->whereRaw("OperatorChat.model_id = model_has_roles.model_id",[],'AND')->groupBy(["OperatorChat.model_id"])
        );



        $chatMessageCount = new ChatMessageLogic(array_merge($params,['is_ace'=>'0']),[DB::raw("COUNT(*)")]);
        $chatMessageCount->setQuery( $chatMessageCount->offPagination()->getFullQuery()
            ->leftJoin(DB::raw("(".$joinMessage->getSqlToStr().") as OperatorChat ON  chat_messages.chat_id =  OperatorChat.chat_id"),function(){})
            ->whereRaw("OperatorChat.model_id = model_has_roles.model_id",[],'AND')->groupBy(["OperatorChat.model_id"])
        );

        $chatMessageCount = new ChatMessageLogic(array_merge($params,['is_ace'=>'0']),[DB::raw("COUNT(*)")]);
//        $chatMessageCount
        //date_from date_to
        $limitOperator = new OperatorChatLimitLogic($params,[DB::raw("COUNT(*)")]);
        $limitOperator->setQuery($limitOperator->offPagination()->setJoin(['OperatorAncet'])
            ->setLimit(false)
            ->getFullQuery()->groupBy('operator_link_users.operator_id')
            ->where('limits','>=','1')
            ->whereRaw("operator_link_users.operator_id = model_has_roles.model_id",[],'AND'));



       $joinAnsverMan = new ChatMessageLogic($params,[DB::raw("COUNT(*) AS m_count,chat_id,recepient_user_id")]);
       $joinAnsverMan->setQuery($joinAnsverMan->offPagination()->setLimit(false)
       ->getFullQuery()->groupBy(['chat_id','recepient_user_id'])->havingRaw("m_count <=1")
       );


       //  коректно не работает

       $operatorAnsverMan = new ChatLogic($params,[DB::raw("COUNT(*)")]);
       $operatorAnsverMan->setQuery($operatorAnsverMan->offPagination()->setLimit(false)->setJoin(['OperatorAncet'])->getFullQuery()
           ->whereRaw("OperatorAncet.operator_id = model_has_roles.model_id",[],'AND')
           ->where('is_answered_by_operator',"=",'0')
           ->groupBy('OperatorAncet.operator_id')
        );
        $workingShitForfeitsOperator =  new OperatorForfeitsLogic($params,[DB::raw("COUNT(*)  as operator_forfeits")]);
        $workingShitForfeitsOperator ->setQuery($workingShitForfeitsOperator->offPagination()->setLimit(false)
            ->getFullQuery()->whereRaw("operator_forfeits.operator_id = model_has_roles.model_id",[],'AND'));

        $chatMessageMen = new ChatMessageLogic($params, [DB::raw("COUNT(*) as message_count, COUNT(DISTINCT sender_user_id) as men_texted_count")]);
        $chatMessageMen = $chatMessageMen->setQuery($chatMessageMen->offPagination()->setLimit(false)
            ->getFullQuery()
            ->whereRaw("chat_messages.recepient_user_id IN (SELECT id FROM `users` WHERE is_real = 0 AND gender = 'female')"))->getOne();

        $operatorCredits = new OperatorCreditsLogic($params, [DB::raw('IFNULL(SUM(credits), 0)')]);
        $operatorCredits->setQuery($operatorCredits->offPagination()->getFullQuery()->whereRaw("operator_id = model_has_roles.model_id"));



        $operator = new OperatorLogic(['role' =>'2'],
            [
             DB::raw("users.id as id"),
             DB::raw("users.name as name"),
             DB::raw("users.created_at as created_at"),
             DB::raw("(".$workingStatus->getSqlToStrFromQuery()." )as status_work"),
             DB::raw("(".$operatorLiks->getSqlToStrFromQuery()." )as count_ancet"),
//             DB::raw("(".$workingShit->getSqlToStrFromQuery()." )as count_inactive"),
//             DB::raw("(".$workingShitDayWork->getSqlToStrFromQuery()." )as day_work"),
//             DB::raw("(".$workingShitTimeWork->getSqlToStrFromQuery()." )as time_work"),
//             DB::raw("(".$workingShitTimePused->getSqlToStrFromQuery()." )as time_paused"),
//             DB::raw(" '0' as  avg_time"),
//             DB::raw("(".$operatorCredits->getSqlToStrFromQuery().") as count_message"),
             DB::raw("(".$operatorCredits->getSqlToStrFromQuery().") as credits_sum"),
//             DB::raw(" (".$limitOperator->getSqlToStrFromQuery().") as man_whith_limit"),
//             DB::raw(" (".$operatorAnsverMan->getSqlToStrFromQuery().") as ancet_without_message"),
             DB::raw(" (".$workingShitMessageOperator->getSqlToStrFromQuery().") as ansver_operator_message"),
//             DB::raw(" (".$countMenAswer->getSqlToStrFromQuery().") as count_answer_men"),
//             DB::raw(" (".$workingShitForfeitsOperator->getSqlToStrFromQuery().") as operator_forfeits"),
            ]);
        $result =  $operator->setJoin(['User'])->getList();

        for ($i = 0; $i < count($result['result']); $i++) {
            $result['result'][$i]['count_messages_men'] = $chatMessageMen['message_count'];
            $result['result'][$i]['count_men'] = $chatMessageMen['men_texted_count'];
        }

        return  $result;

    }

    public function getOperator(Request $request){
        $params = $request->all();
        $params['role'] = '2';
        $operato = new OperatorLogic($params,['users.*']);
        $operato->setJoin(['User'])
            ->order("asc",'sender_user_id');
        return  $operato->getList();
    }

    public function getMessageSendOperator(Request $request){
        $params = $request->all();
        $params['date_from_message'] = $params['date_from'];
        $params['date_to_message'] = $params['date_to'];
        $params['role'] = '2';
        $operato = new OperatorLogic($params,[
            DB::raw("COUNT(*) as count_message"),
        ]);
        $operato->setJoin(['Message','User','Sender']);
        return  $operato->getGroup();
    }

    public function getMessageCount(Request $request){
        $params = $request->all();
        $params['date_from_message'] = $params['date_from'];
        $params['date_to_message'] = $params['date_to'];
        $params['role'] = '2';
        $params['is_ace'] = '0';
        $operato = new OperatorLogic($params,[
            DB::raw("COUNT(*) as count_message"),
        ]);
        $operato->setJoin(['Message','User','Sender']);
        return  $operato->getGroup();
    }

    public function getMessageCountByOperator(Request $request){
        $params = $request->all();
        $params['role'] = '2';
        $params['date_from_message'] = $params['date_from'];
        $params['date_to_message'] = $params['date_to'];
        $operato = new OperatorLogic($params,[
            DB::raw("COUNT(*) as count_message"),
           // DB::raw("users.name as operator_name"),
        ]);
        $operato->setJoin(['Message','User','Sender'])
            ->setGroupBy(['model_id'])
            ->order("asc",'users.id');
        var_dump($operato->getGroup());
        return  $operato->getGroup();
    }

    public function getMessageCountByAncet(Request $request){
        $params = $request->all();
        $params['date_from_message'] = $params['date_from'];
        $params['date_to_message'] = $params['date_to'];
        $params['role'] = '2';
        $operato = new OperatorLogic($params,[
            DB::raw("COUNT(*) as count_message"),
            DB::raw("Sender.name as anceta"),
            DB::raw("Sender.id as anceta_id"),
            DB::raw("users.name as operator_name"),
        ]);
        $operato->setJoin(['Message','User','Sender'])
            ->setGroupBy(['sender_user_id','model_id'])
            ->order("asc",'sender_user_id');
        return  $operato->getGroup();
    }

    public function getCountAncet(Request $request){
        $params = $request->all();
        $params['role'] = '2';
        $operato = new OperatorLogic($params,[
            DB::raw("COUNT(*) as count_ancet"),
        ]);
        $operato->setJoin(["Ancet"]);
        return  $operato->getGroup();
    }
    public function getCountAncetWork(Request $request){
        $params = $request->all();
        $params['role'] = '2';
        $params['is_work'] = '1';
        $operato = new OperatorLogic($params,[
            DB::raw("COUNT(*) as count_ancet"),
        ]);
        $operato->setJoin(["Ancet"]);
        return  $operato->getGroup();
    }

}

