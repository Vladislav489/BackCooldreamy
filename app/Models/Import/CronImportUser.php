<?php
namespace App\Models\Import;

use App\ModelAdmin\ImportExport\ImportCSV;
use App\Models\User;
use App\Services\NextCloud\NextCloud;
use App\Services\NextCloud\SaveImage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CronImportUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'data_import',
    ];


    public static function  imporUserWithImage($file,$cron = null){
        $userListData = [];
        $GLOBALS['userListData'] = [];
        $path = $file->getRealPath();
        $import = $import = new ImportCSV(new User());
        $import->setFile($path);
        $import->setRuls(['id'=>'id','email'=>'email',
            'name' => 'name','state' => 'state',
            'country' => 'country', 'birthday' => 'birthday',
            'gender' => 'gender', 'about_self' => 'about_self',
            'is_real' => 'is_real','password' => 'password',
            'profile_type_id'=>'profile_type_id',
            'imagefolder' => 'imagefolder']);
        $import->setCallBackParser(function ($data,$obj,$index){
            $userListData = [];
            $takeIndex = [];
            if($index == 0) {
                if ($obj->getFileColumn()) {
                    $EndCol = strpos($data, $obj->getFileSeparator())+strlen($obj->getFileSeparator());
                    $data = substr($data, $EndCol);
                }
            }

            $list = explode($obj->getFileSeparator(),trim($data));
            $arrayKeyColum = array_keys($obj->getColumnToParse());
            foreach ($arrayKeyColum as $column_)
                $takeIndex[] = array_search($column_,$obj->getFileColumn());

            foreach ($list as $key => $item){
                $items = str_getcsv($item);
                $newItems = [];
                foreach ($takeIndex as $index__){
                    if(count($items) == count($obj->getFileColumn()))
                        $newItems[] = addslashes($items[$index__]);
                }
                if($obj->getAddToInsertParams())
                    $newItems = array_merge($newItems,array_values($obj->getAddToInsertParams()));

                //dd($arrayKeyColum,$newItems);

                $userListData[] = array_combine( $arrayKeyColum,$newItems);
            }
            $GLOBALS['userListData'] = array_merge($GLOBALS['userListData'],$userListData);
        });
        $import->parser();

        if(!is_null($cron)) {
            $dataInsert = [];
            foreach ($GLOBALS['userListData'] as $item){
                $dataInsert[] = [
                    'status'=> 0,
                    'data_import' => json_encode($item)
                ];
            }
            CronImportUser::insert($dataInsert);
           return null;
        }else{
            return $GLOBALS['userListData'];
        }
    }

    public static function fastImporInfoUser($file){
        $path = $file->getRealPath();
        $import = $import = new ImportCSV(new User());
        $import->setFile($path);
        $import->setRuls(['id'=>'id','email'=>'email',
            'name' => 'name','state' => 'state',
            'country' => 'country', 'birthday' => 'birthday',
            'gender' => 'gender', 'about_self' => 'about_self',
            'is_real' => 'is_real','password' => 'password','imagefolder'=>'imagefolder']);
        $import->setCallBackParser(function ($data,$obj,$index){
            $insert = "";
            $insertList = $updateList = $takeIndex = [];
            $flagInsert = false;
            if($index == 0) {
                if ($obj->getFileColumn()) {
                    $EndCol = strpos($data, $obj->getFileSeparator())+strlen($obj->getFileSeparator());
                    $data = substr($data, $EndCol);
                }
            }
            $list = explode($obj->getFileSeparator(),trim($data));

            $arrayKeyColum = array_keys($obj->getColumnToParse());
            foreach ($arrayKeyColum as $column_){
                $takeIndex[] = array_search($column_,$obj->getFileColumn());
            }

            foreach ($list as $key => $item){
                $items = str_getcsv($item);
                $newItems = [];
                $flagInsert = false;
                foreach ($takeIndex as $index__){
                    if(count($items) == count($obj->getFileColumn())) {
                        if(array_search('id',$obj->getFileColumn()) == $index__){
                            if(empty($items[$index__])){
                                $flagInsert = true;
                                continue;
                            }
                        }
                        $newItems[] = addslashes($items[$index__]);
                    }
                }
                if($obj->getAddToInsertParams()){
                    $newItems = array_merge($newItems,array_values($obj->getAddToInsertParams()));
                }
                $newItems['birthday'] = date("Y-m-d",strtotime($newItems['birthday']));

                if($flagInsert){
                    if(isset($newItems['password'])){
                        $newItems['password'] = \Illuminate\Support\Facades\Hash::make($newItems['password']);
                    }
                    $insertList[] = "'".implode("','",$newItems)."'";
                }else{
                    $updateList[] = "'".implode("','",$newItems)."'";
                }

            }

            if(is_array($obj->getAddToInsertParams())){
                $colunInsert = implode(",",array_merge($obj->getColumnToParse(),array_keys($obj->getAddToInsertParams())));
            }else{
                $colunInsert = implode(",",$obj->getColumnToParse());
            }


            if(count($insertList)){
                $newColum = explode(",",$colunInsert);
                unset($newColum[array_search('id',$newColum)]);
                $newColum = implode(",",$newColum);
                $insert = "INSERT INTO ".$obj->getModel()->getTable()."  (".$newColum.") VALUES (".implode("),\n(",$insertList).")";
                DB::beginTransaction();
                try {
                    DB::insert($insert);
                    DB::commit();
                }catch (\Exception $e){
                    dd($e->getMessage());
                    DB::rollBack();
                }
            }

            if(count($updateList)){
                $insert = "REPLACE INTO ".$obj->getModel()->getTable()."  (".$colunInsert.") VALUES (".implode("),\n(",$updateList).")";
                DB::beginTransaction();
                try {
                    DB::insert($insert);
                    DB::commit();
                }catch (\Exception $e){
                    dd($e->getMessage());
                    DB::rollBack();
                }
            }
        });
        $import->parser();
        return true;
    }

    public static function  loadUserWithImage($userdata){
        $listFile = [];
        $categoryList = ['ava'=>1,'18+'=>4,'content'=>3,'profile'=>2];
        $ApiStoreg = new NextCloud('dmitry','Ag@19836373!','nc.cooldreamy.com');
        if (!empty($userdata)) {
            $user = null;
            if(!empty($userdata['id']))
                $user = User::find($userdata['id']);
                if(is_null($user)) {
                    $user = new User();
                    $user->id = $userdata['id'];
                }
                $user->email = $userdata['email'];
                $user->name = $userdata['name'];
                $user->state = $userdata['state'];
                $user->country = $userdata['country'];
                $user->birthday = Carbon::createFromFormat('d.m.Y', $userdata['birthday'])->toDateTimeString();
                $user->about_self = $userdata['about_self'];
                if(isset($userdata['password']) && !empty($userdata['password'])){
                    if(strpos($userdata['password'],'$2y$10$') !== false){
                        $user->password = $userdata['password'];
                    }else{
                        $user->password = \Illuminate\Support\Facades\Hash::make($userdata['password']);
                    }
                }
                $user->profile_type_id = $userdata['profile_type_id'];
                $user->is_real = false;
                $user->gender = 'female';
                $user->save();
               // $folder = $ApiStoreg->getFolder($pathToDir)->getResponse();

                foreach ($categoryList as $key => $folderItem){
                    $pathToDir  = "/media/{$userdata['imagefolder']}/{$key}/";
                    $listFile[$key] = $ApiStoreg->getFolder($pathToDir)->getResponse();
                }
                try {
                    $imgObject = [];
                    foreach ($listFile as $category_ => $images__) {
                        if (!is_null($listFile[$category_])) {
                            foreach ($listFile[$category_] as $ItemImage) {
                                $imgObject[] = (new SaveImage())->saveImgUsers($user, $ItemImage,$categoryList[$category_]);
                            }
                        }
                    }
                }catch (\Throwable $e){
                    return  false;
                }
        }
        return  $imgObject;
    }

    public static function runCron(){
        try {
                $list = CronImportUser::query()->where("status", '=', 0)->limit(1)->offset(rand(1, 2))->get()->toArray()[0];
                var_dump($list);
                if ($list['id']) {
                    if ($list['status'] !== 2) {
                        CronImportUser::query()->where('id', '=', $list['id'])->update(['status' => 2]);
                        $res =  CronImportUser::loadUserWithImage(json_decode($list['data_import'], true));
                        if($res) {
                            CronImportUser::query()->where('id', '=', $list['id'])->update(['status' => 1]);
                        } else {
                            CronImportUser::query()->where('id', '=', $list['id'])->update(['status' => 6]);
                        }
                    }
                }
        }catch ( \Throwable $e){
            $text = "";
            ob_start();
            var_dump($e->getMessage());
            var_dump($e->getFile());
            var_dump($e->getLine());
            var_dump($e->getTrace());
            $text = ob_get_contents();
            ob_clean();
            logger("cron Id ".$list['id']."\n\n".$text);
            CronImportUser::query()->where('id','=',$list['id'])->update(['status' => 4]);
        }
    }
}
