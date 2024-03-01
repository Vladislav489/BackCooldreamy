<?php

namespace App\Http\Controllers;

use App\Enum\Auth\AuthLogTypeEnum;
use App\Exceptions\verifyEmailException;
use App\Mail\MessageUserMail;
use App\Mail\SendMail;
use App\Mail\Test;
use App\Mail\TestMail;
use App\Mail\VerificationMail;
use App\ModelAdmin\CoreEngine\LogicModels\Ace\AceCronLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Chat\ChatMessageLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Operator\OperatorLinksUserLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Statistic\RoutingLogic;
use App\ModelAdmin\CoreEngine\LogicModels\Statistic\UserInputsLogic;
use App\ModelAdmin\CoreEngine\LogicModels\UserLogic;
use App\ModelAdmin\ImportExport\ExportFronDBCSV;
use App\ModelAdmin\ImportExport\ImportCSV;
use App\Models\Auth\CreditLog;
use App\Models\ChatMessage;
use App\Models\Country;
use App\Models\Feed;
use App\Models\Image;
use App\Models\Import\CronImportUser;
use App\Models\ServicePrices;
use App\Models\StatisticSite\RoutingUser;
use App\Models\StatisticSite\UserInputs;
use App\Models\StatisticSite\UserWatch;
use App\Models\OperatorLinkUsers;
use App\Models\State;
use App\Models\User;
use App\Models\Video;
use App\Services\Mail\VerifyEmail;
use App\Services\NextCloud\NextCloud;
use App\Services\OneSignal\OneSignalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Operator;
use App\Models\Administrator;
use App\Models\Ace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Yajra\DataTables\DataTables;
use Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function test()
    {
        $user = User::where('id', 124518)->first();
        $user->tokens()->delete();
        $user->online = false;
        $user->save();
//        $this->authLogRepository->logAuth($user, AuthLogTypeEnum::LOGOUT);

        return response()->json(['message' => 'success']);
    }

    public function dashbord(Request $request){
     $data = json_decode($this->getCountStatistic($request)->content(),true);
     $data['country'] = Country::all()->toArray();
     $data['state'] = State::all()->toArray();
     $data['utm_source'] = User\UserCooperation::whereNotNull('utm_source')->groupBy('utm_source')->pluck('utm_source')->toArray();
     $data['utm_campaign'] = User\UserCooperation::whereNotNull('utm_campaign')->groupBy('utm_campaign')->pluck('utm_campaign')->toArray();
     $data['utm_medium'] = User\UserCooperation::whereNotNull('utm_medium')->groupBy('utm_medium')->pluck('utm_medium')->toArray();
     $data['utm_term'] = User\UserCooperation::whereNotNull('utm_term')->groupBy('utm_term')->pluck('utm_term')->toArray();
     $data['utm_advertiser'] = User\UserCooperation::whereNotNull('utm_advertiser')->groupBy('utm_advertiser')->pluck('utm_advertiser')->toArray();

     return view("admin.dashbord.index",$data);
    }

    public function getCountStatistic(Request $request){
        $params = ['is_real'=>'1'];
        if(!empty($request->all())){
            $params = array_merge($params,$request->all());
        }
        $count_message_male = new UserLogic($params);
        $pay_onsit = new UserLogic(array_merge(['payment_status'=>'success'],$params));
        $count_user = new UserLogic($params);

        $count_session = new UserLogic($params);
        $count_link = new RoutingLogic($params);
        $data =[
            'count_link' => $count_link->getTotal(),
            'count_session' =>$count_session->getCountSession(),
            'count_user' => $count_user->getCountUsers(),
            'count_message_male'=> $count_message_male->getAVGMessage(),
            'pay_onsite' => $pay_onsit->getPayUser()
        ];
        return response()->json($data);
    }

    public function getDataListUserStatistic(Request $request){
        $params = ['is_real'=>'1'];
        if(!empty($request->all()))
            $params = array_merge($params,$request->all());
        $users = new UserLogic($params,[
            DB::raw('UserCooperation.utm_source AS utm_source'),
            DB::raw('UserCooperation.utm_medium AS utm_medium'),
            DB::raw('UserCooperation.utm_campaign AS utm_campaign'),
            DB::raw('UserCooperation.utm_term AS utm_term'),
            DB::raw('UserCooperation.utm_advertiser AS utm_advertiser'),
            'id', 'email', 'name',DB::raw("UserCooperation.subid as subid"),DB::raw("UserCooperation.app_name as app_name"), 'state','country','birthday','birthday','about_self',
            DB::raw(" DATE_FORMAT(users.created_at,\"%Y-%m-%d %H:%i:%s\") as created_at"),
            DB::raw("CASE WHEN from_mobile_app = 0 THEN 'web' WHEN from_mobile_app = 1 THEN 'mobile' END as platform"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM " . (new ChatMessage())->getTable() . " WHERE recepient_user_id = users.id AND is_ace = 1),0) as received_aces"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM " . (new ChatMessage())->getTable() . " WHERE recepient_user_id = users.id AND is_ace = 1 AND is_read_by_recepient = 1),0) as read_aces"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM ".(new Feed())->getTable()." WHERE from_user_id = users.id),0) as like_"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM ".(new UserInputs())->getTable()." WHERE user_id = users.id),0) as coming"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM ".(new RoutingUser())->getTable()." WHERE user_id = users.id),0) as link"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM ".(new UserWatch())->getTable()." WHERE user_id = users.id),0) as view"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM ".(new ChatMessage())->getTable()." WHERE sender_user_id = users.id),0) as send_message"),
            DB::raw("IFNULL((SELECT COUNT(DISTINCT recepient_user_id) FROM ".(new ChatMessage())->getTable()." WHERE sender_user_id = users.id and is_ace = 0 AND recepient_user_id IN (SELECT id FROM users WHERE gender = 'female' AND is_real = 0)),0) as send_to_ankets_count"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM ".(new ChatMessage())->getTable()." WHERE recepient_user_id = users.id AND is_ace = 0),0) as received_message"),
            DB::raw("IFNULL((SELECT COUNT(*) FROM ".(new ChatMessage())->getTable()." WHERE recepient_user_id = users.id AND is_ace = 0 AND is_read_by_recepient = 1),0) as read_message"),
            DB::raw("IFNULL((SELECT SUM(credits) FROM ".(new CreditLog())->getTable()." WHERE user_id = users.id AND credits IS NOT NULL ),0) as credits"),
            DB::raw("IFNULL((SELECT SUM(real_credits) FROM ".(new CreditLog())->getTable()." WHERE user_id = users.id AND real_credits IS NOT NULL),0) as real_credits"),
            DB::raw("IFNULL((SELECT SUM(price) FROM ".(new User\Payment())->getTable()." WHERE user_id = users.id AND status ='success'),0) as pay") ,
        ]);
        $users->setLimit(false)->setJoin(['UserCooperationAll'])->order('desc','created_at');
        return response()->json($users->getList()['result']);
    }

    public function exportStatisticUser(Request $request){
        $data = json_decode($this->getDataListUserStatistic($request)->getContent(),true);
        $csv = new ExportFronDBCSV();
        $csv->setFileName("users_statistic".date("Y-m-d"));
        $csv->setDataFrom($data);
        $csvBody = $csv->run();
        return response()->streamDownload(function () use ($csvBody) {echo $csvBody;},$csv->getFileName());
    }

    public function getDataList(Request $request){
        return DataTables::of(json_decode($this->getDataListUserStatistic($request)->getContent(),true))->make();
    }



    public function exportCsvUser(){
        $params = \request()->all();
        $user = new UserLogic($params,['id','email','name','state','country','birthday','gender','about_self','is_real','password','profile_type_id']);
        $user->offPagination();
        $csv = new ExportFronDBCSV();
        $csv->setFileName("users_import");
        $csv->setDataFrom(json_decode(json_encode(DB::select($user->getSqlToStr())), true),['imagefolder']);
        $csvBody = $csv->run();
        return response()->streamDownload(function () use ($csvBody) {echo $csvBody;},$csv->getFileName());
    }
    public function exportCsvOperator(){
        $params = \request()->all();
        $operator = new OperatorLinksUserLogic($params,['user_id','operator_id']);
        $operator->offPagination();
        $csv = new ExportFronDBCSV();
        $nameFile = (isset($params['operator']) && !empty($params['operator']))? "operator_users_".$params['operator']:"operators_users";
        $csv->setFileName($nameFile);
        $csv->setDataFrom($operator->getList()['result']);
        $csvBody = $csv->run();
        return response()->streamDownload(function () use ($csvBody) {
                echo $csvBody;
        },$csv->getFileName());
    }
    public function importPage(){
        $country = Country::all()->toArray();
        $state = State::all()->toArray();
        return view('admin.import.index',
            ['state' => $state,'country'=>$country]);
    }
    public function importAces(){return view('admin.import-aces.index');}
    public function getOperatorsData(){
        $users = OperatorLinkUsers::query()->select(DB::raw("operator_link_users.*,users.name as operator_name"))
            ->leftJoin('users','operator_link_users.operator_id','=','users.id')->get();
        //$users = OperatorLinkUsers::user()->orderBy('created_at', 'desc')->get();
        return DataTables::of($users)->make();
    }
    public function uploadCountries(Request $request)
    {
        $this->validate($request, ['csv_file' => 'required|mimes:csv,xlsx']);
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map(function ($value) {
            return str_getcsv($value, ",");
        }, file($path));
        foreach ($data as $key => $row) {
            if ($key == 0) {continue;}
            if (!empty($row)) {
                if (!Country::where('title', $row[0])->first()) {
                    $country = new Country();
                    $country->title = $row[0];
                    $country->save();
                }
            }
        }
        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }
    public function uploadRegions(Request $request)
    {
        $this->validate($request, ['csv_file' => 'required|mimes:csv,xlsx']);
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map(function ($value) {
            return str_getcsv($value, ",");
        }, file($path));

        foreach ($data as $key => $row) {
            if ($key == 0) {continue;}
            if (!empty($row)) {
                if (!$country = Country::where('title', $row[1])->first()) {
                    $country = new Country();
                    $country->title = $row[1];
                    $country->save();
                }

                if (!State::where('title', $row[0])->where('country_id', $country->id)->first()) {
                    $state = new State();
                    $state->title = $row[0];
                    $state->country_id = $country->id;
                    $state->save();
                }
            }
        }
        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }
    public function operatorsMultichat(){
        $operator = new OperatorLinksUserLogic([],[DB::raw('users.name as name')]);
        $operator->setJoin(['Operator']);
        $listOperator  = $operator->setGroupBy(['operator_id'])->offPagination()->getGroup()['result'];
        return view('admin.import-operators.index',['listOperator'=>$listOperator]);
    }
    public function getTimezone($city, $country) {}
    public function loadTimezone(){
        // Пример использования
        $city = 'Moscow';
        $country = 'RU';
        $timezone = $this->getTimezone($city, $country);
        if ($timezone) {
            echo "Временная зона для города $city, $country: $timezone";
        } else {
            echo "Не удалось определить временную зону для города $city, $country";
        }

        die();
    }

    public function uploadFastUser(Request $request){
        $this->validate($request, [
            'csv_file' => 'required|mimes:csv,txt'
        ]);
        $file = $request->file('csv_file');
        CronImportUser::fastImporInfoUser($file);
        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }

    public function uploadUseImage(Request $request){
        $this->validate($request, ['csv_file' => 'required|mimes:csv,txt']);
        $file = $request->file('csv_file');
        $request->cron = (isset($request->cron))?$request->cron:null;
        $list = CronImportUser::imporUserWithImage($file,true);
        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }

    public function seveUserImage(Request $request){
     $userdata = $request->all()['userdata'];
     return response()->json(CronImportUser::loadUserWithImage($userdata));

    }


    public function uploadOperatorsAdminAnkets(Request $request){
        $this->validate($request, [
            'csv_file' => 'required|mimes:csv,txt'
        ]);
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $file_Name  = $file->getClientOriginalName();
        $id_operarora = null;
        if(str_contains($file_Name,'operator_users')){
            $id_operarora = explode("_",$file_Name)[2];
            $id_operarora = explode(" ",$id_operarora)[0];
            $id_operarora = str_replace(".csv",'',$id_operarora);
        }
        if(str_contains($file_Name,'operators_users')){$id_operarora = false;}


        if(!is_null($id_operarora)){
           if($id_operarora){
               DB::statement("DELETE FROM ".(new OperatorLinkUsers())->getTable()." WHERE operator_id ='{$id_operarora}'");
           }else{
               DB::statement("TRUNCATE TABLE ".(new OperatorLinkUsers())->getTable());
           }
        }else{
            return redirect()->back()->with('success', 'name file is don\'t valid');
        }
        $import = new ImportCSV(new OperatorLinkUsers());

        $import->setFile($path);
        $import->setRuls(['user_id'=>'user_id','operator_id'=>'operator_id']);
        $import->setAddParamsImport(['operator_work'=>'0','admin_work'=>'0','description'=>'','disabled'=>'0']);
        $import->parser();
        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }
    public function uploadAces(Request $request)
    {
        $this->validate($request, [
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');

        $path = $file->getRealPath();

        $data = array_map(function ($value) {
            return str_getcsv($value, ";");
        }, file($path));

        foreach ($data as $row) {
            if (!empty($row)) {
                $ace = new Ace();
                try {
                    $ace->message_type_id = $row[0];
                    $ace->text = $row[1];
                    if ($ace->message_type_id == 5 or $ace->message_type_id == 6) {
                        $ace->target_id = $row[2];
                    }
                    $ace->save();
                } catch (\Exception $e) {
//                    return  $e;
                }
            }
        }

        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }
    public function uploadOperators(Request $request){
        $this->validate($request, ['csv_file' => 'required|mimes:csv,txt']);
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map(function ($value) {
            return str_getcsv($value, ";");
        }, file($path));

        $operatorsDB = Operator::all();
        $operatorObj = [];
        foreach($operatorsDB as $operatorDB)
            $operatorObj[$operatorDB->id] = $operatorDB;

        $administratorsDB = Administrator::get();
        $administratorObj = [];

        foreach($administratorsDB as $administratorDB)
            $administratorObj[$administratorDB->id] = $administratorDB;

        foreach ($data as $row) {
            if (!empty($row)) {
                try {
                    $row[3] = Hash::make($row[3]);
                    Log::debug(json_encode($row, JSON_UNESCAPED_UNICODE));
                    if($row[4] == "admin") {
                        $elementList = $administratorObj;
                        $element = new Administrator();
                        $testName = "Админ";
                    } else {
                        $elementList = $operatorObj;
                        $element = new Operator();
                        $testName = "Оператор";
                    }
                    if(array_key_exists($row[0],$elementList)) {
                        Log::debug(json_encode("Редактируем ".$testName.", ".$row[1]." ID".$elementList[$row[0]]->id, JSON_UNESCAPED_UNICODE));
                        $elementList[$row[0]]->user_id = $row[0];
                        $elementList[$row[0]]->name = $row[1];
                        $elementList[$row[0]]->email = $row[2];
                        $elementList[$row[0]]->password = $row[3];
                        $elementList[$row[0]]->created_at = date("Y-m-d H:i:s");
                        $elementList[$row[0]]->save();
                    } else {
                        Log::debug(json_encode("Создаём ".$testName.", ".$row[1], JSON_UNESCAPED_UNICODE));
                        $element->user_id = $row[0];
                        $element->name = $row[1];
                        $element->email = $row[2];
                        $element->password = $row[3];
                        $element->created_at = date("Y-m-d H:i:s");
                        $result = $element->save();

                        Log::debug("Результат создания?  ".json_encode($result, JSON_UNESCAPED_UNICODE));
                    }

                } catch (\Exception $e) {
//                    return $e;
                }
            }
        }
        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }
    public function uploadAdministrators(Request $request){
        $this->validate($request, ['csv_file' => 'required|mimes:csv,txt']);
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map(function ($value) {
            return str_getcsv($value, ";");
        }, file($path));

        $administratorsDB = Administrators::select("user_id")->all();
        $administratorObj = [];

        foreach($administratorsDB as $administratorDB) {$administratorObj[$administratorDB->id] = $administratorDB;}
        foreach ($data as $row) {
            if (!empty($row)) {
                try {
                    if(array_key_exists($row[0],$administratorObj)) {
                        $administratorObj[$row[0]]->name = $row[1];
                        $administratorObj[$row[0]]->email = $row[2];
                        $administratorObj[$row[0]]->password = $row[3];
                        $administratorObj[$row[0]]->save();
                    } else {
                        $administrator = new Administrator();
                        $administrator->user_id = $row[0];
                        $administrator->name = $row[1];
                        $administrator->email = $row[2];
                        $administrator->password = $row[3];
                        $administrator->save();
                    }
                } catch (\Exception $e) {
//                    return $e;
                }
            }
        }
        return redirect()->back()->with('success', 'CSV file uploaded successfully');
    }
    public static function generateTargets($tableName, $anketType, $min, $max){
        $countTargets = rand($min, $max);
        $result = array();
        $values = self::getProbabilityList($tableName, $anketType);

        for ($i = 0; $i < $countTargets; $i++) {
            $rand = mt_rand() / mt_getrandmax(); // генерируем случайное число
            $cumulative_probability = 0;
            foreach ($values as $value) {
                $cumulative_probability += $value->probability;
                if ($rand < $cumulative_probability and $value->probability != 0) {
                    // добавляем новое значение, если не было уже выбрано ранее
                    if (!in_array($value->id, $result)) {
                        $result[] = $value->id;
                        break;
                    }
                }
            }
        }
        return $result;
    }
    public static function getProbabilityList($tableName, $anketType){
        $list = DB::table($tableName)->select('id', "$anketType as probability")->where('gender', '!=', 'male')->get();
        return $list;
    }
    public function getData(){


        /*$users = User::select(['id', 'email', 'name', 'state', 'country', 'birthday', 'about_self', 'password', 'created_at'])
            ->where('is_real', false)
            ->where('gender', 'female')->orderBy('created_at', 'desc');
*/

        $params = \request()->all('filter');
        $params =  (isset($params['filter']))?$params['filter']:[];
        $user = new UserLogic($params,['id', 'email', 'name', 'state', 'country',
            'birthday', 'about_self', 'password', 'created_at']);
        $user->offPagination();
        return DataTables::of($user->getList()['result'])->make();
    }
    public function getOperators(){
        $operators = Operator::select(['id', 'name', 'email', 'password', 'created_at'])->orderBy('created_at', 'desc');
        return DataTables::of($operators)->make();
    }
    public function getAdministrators(){
        $administrators = Administrator::select(['id', 'name', 'email', 'password', 'created_at'])->orderBy('created_at', 'desc');
        return DataTables::of($administrators)->make();
    }
    public function getAces(){
        $users = Ace::select(['message_type_id', 'text', 'target_id'])->orderBy('created_at', 'desc');
        return DataTables::of($users)->make();
    }


}
