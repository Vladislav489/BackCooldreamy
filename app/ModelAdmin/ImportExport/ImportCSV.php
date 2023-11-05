<?php
namespace App\ModelAdmin\ImportExport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class ImportCSV {

    const  FILE_TYPE = '.csv';

    protected  $fileCsv;
    protected  $fileColumn;
    protected  $fileCsvSize;
    protected  $filePath;
    protected  $fileSeparator;
    protected  $columnIndex;
    protected  $columnToParse;
    protected  $model;
    protected  $callBackParser = null;
    protected  $addToInsertParams = false;
    protected  $limitChank = 2000;
    protected  $targetForUpdate = false;
    protected  $targetDataForUpdate = false;
    private    $flagOneMulti = true ;


    public function  __construct($model){
        if($model instanceof  Model) {
            $this->model = $model;
        }else{
            throw  new \Exception("Это не объект Model Laravel");
        }
    }
    public function setTargetDataForUpdate(array $data){
        $this->targetDataForUpdate = $data;
    }
    public function setAddParamsImport(array $data){
         $column  =  $this->model->getFillable();
         $flag = true;
         $error_field = [];
         $tempData = $data;
         foreach ($column as $column_name){
             if(key_exists($column_name,$tempData)){
                    unset($tempData[$column_name]);
             }
         }
         if(count($tempData) > 0){
             dd(array_keys($tempData),$column_name);
         }
         $this->addToInsertParams = $data;
         return $this;
    }
    public function setCallBackParser($callBack){
        if(is_callable($callBack)) {
            $this->callBackParser = $callBack;
        }
        return $this;
    }
    public function setTargetForUpdate(array $data){
        $this->targetForUpdate = $data;
        return $this;
    }

    public function getFileColumn(){
        return $this->fileColumn;
    }
    public function getFileSeparator(){
        return $this->fileSeparator;
    }
    public function getColumnToParse(){
        return $this->columnToParse;
    }
    public function getModel(){
        return $this->model;
    }
    public function getAddToInsertParams(){
        return $this->addToInsertParams;
    }
    public function parser(){
        $this->loadFile();
    }

    public function setFile(string $filePath){
        if(file_exists($filePath)) {
            $this->filePath = $filePath;
            $this->fileCsv = fopen($this->filePath, "a+");
        }else{
            throw  new \Exception("Файл неверного фората ".self::FILE_TYPE);
        }
        return $this;
    }
    public function setLimitSizeChank(int $limit){
        if(is_numeric($limit)) {
            $this->limitChank = $limit;
        }else{
            throw  new \Exception("Должно быть число");
        }
        return $this;
    }


    private function loadFile(){
        if(!$this->findColunmFile()){
           throw new \Exception('У вас не совпадают название колонок с правилами перенеоса
           проверьте файл и правила');
        }
        $stackLoadChankFile = $this->chankFile();
        if($stackLoadChankFile){
           foreach ($stackLoadChankFile['list'] as $key => $value){
               fseek($this->fileCsv,  $value['start']);
               $data = fread($this->fileCsv, $value['end']);
               if(!is_null($this->callBackParser)) {
                   $function = $this->callBackParser;
                   $function($data,$this,$key);
               } else {
                  if($this->targetForUpdate == false && $this->targetDataForUpdate == false)
                    ($this->flagOneMulti)?$this->multiData($data, $key):$this->aloneData($data, $key);
                  else
                    $this->dataUpdate($data, $key);
               }
           }
        }
    }
    /*если в конце цв нет сепаратара добавляем для упрощенного добавления и возращает сепаратор */
    private function addToEndSeparation($chankfileText){
        $writeToEnd = false;
        fseek($this->fileCsv, 0);
        $string = fread($this->fileCsv, 1000);
        $writeToEnd = "";
        if (strripos($string, "\r") !== false || strripos($string, "\n")) {
            if (strripos($string, "\r\n") !== false) {
                $writeToEnd = "\r\n";
            } else if (strripos($string, "\n\r") !== false) {
                $writeToEnd = "\n\r";
            } else if (strripos($string, "\r") !== false) {
                $writeToEnd = "\r";
            } else if (strripos($string, "\n") !== false) {
                $writeToEnd = "\n";
            }
            fseek($this->fileCsv, $this->fileCsvSize);
            fwrite($this->fileCsv, $writeToEnd);
            $this->fileCsvSize += strlen($writeToEnd);
        } else {
            fseek($this->fileCsv, $this->fileCsvSize);
            fwrite($this->fileCsv, "\r\n");
            $writeToEnd = "\r\n";
             $this->fileCsvSize += 2;
        }
        return $writeToEnd;
    }
    /*вычисляет на сколько шагов разбить файл  для загрузки можно использовать как пошаговую так и мульти загрухку по потокам*/
    private function chankFile(){
        $pos = [];
        $start = $count = 0;
        if(file_exists($this->filePath)) {
            $this->fileCsvSize = filesize($this->filePath);
            fseek($this->fileCsv, ($this->fileCsvSize - 10));
            $searchEndsympol = fread($this->fileCsv, $this->fileCsvSize);
             $this->fileSeparator = $this->addToEndSeparation($searchEndsympol);

            if ($this->fileCsvSize > $this->limitChank) {
                $limitByte = substr($this->fileCsvSize, 0, 1)+1;
                $limitByte .= str_repeat("0", strlen($this->fileCsvSize) - 3);
                $limitByte = (int)$limitByte;
            } else {
                $limitByte = 10000;
            }
            fseek($this->fileCsv, 0);
            while (!feof($this->fileCsv)) {
                fseek($this->fileCsv, $start);
                if (feof($this->fileCsv)) {
                    break;
                }
                $string = fread($this->fileCsv, $limitByte);
                if ($this->fileSeparator) {
                    if (strripos($string, $this->fileSeparator) !== false ) {
                        $endpos = (strripos($string, $this->fileSeparator)) + strlen($this->fileSeparator)/2;
                        $count += substr_count(substr($string, 0, $endpos), $this->fileSeparator);
                    }
                }
                $pos[] = array("start" => $start, "end" => $endpos, "name_file" => $this->filePath);
                $start = $endpos + $start;
            }
            return array("list" => $pos, "count" => $count);
        }
        return false;
    }
    /* ищет совпадение колонок с правилами колонок*/
    private function findColunmFile(){
            $column  = fgetcsv($this->fileCsv);
            $flag = true;
            foreach ($this->columnToParse as $key => $value){
                if(!in_array($key,$column))
                    $flag = false;
            }

            if($flag)
                $this->fileColumn = $column;
            return $flag;
    }

    /*быстрая загрузка один запрос на 5000 за один раз  */
    protected function multiData($data,$index){
        if($index == 0) {
            if ($this->fileColumn) {
                $EndCol = strpos($data, $this->fileSeparator)+strlen($this->fileSeparator);
                $data = substr($data, $EndCol);
            }
        }
        $insert ="";
        $list = explode($this->fileSeparator,trim($data));
        $arrayKeyColum = array_keys($this->columnToParse);
        $takeIndex = [];
        foreach ($arrayKeyColum as $column_){
            $takeIndex[] = array_search($column_,$this->fileColumn);
        }
        foreach ($list as $key => $item){
            $items = str_getcsv($item);
            $newItems = [];
            foreach ($takeIndex as $index__){
                    if(count($items) == count($this->fileColumn)) {
                        $newItems[] = addslashes($items[$index__]);
                    }
            }
            if($this->addToInsertParams){
                $newItems = array_merge($newItems,array_values($this->addToInsertParams));
            }
            $list[$key] = "'".implode("','",$newItems)."'";
        }

        if(is_array($this->addToInsertParams)){
            $colunInsert = implode(",",array_merge($this->columnToParse,array_keys($this->addToInsertParams)));
        }else{
            $colunInsert = implode(",",$this->columnToParse);
        }
        $insert = "INSERT INTO ".$this->model->getTable()."  (".$colunInsert.") VALUES (".implode("),(",$list).")";

        DB::beginTransaction();
        try {
            DB::insert($insert);
            DB::commit();
        }catch (\Exception $e){
            dd($e->getMessage());
            DB::rollBack();
        }

    }
    /* добавление одиночных инсертов*/
    protected function aloneData($data,$index){
        if($index == 0) {
            if ($this->fileColumn) {
                $EndCol = strpos($data, $this->fileSeparator)+strlen($this->fileSeparator);
                $data = substr($data, $EndCol);
            }
        }
        $list = explode($this->fileSeparator,$data);
        $arrayKeyColum = array_keys($this->columnToParse);
        $takeIndex = [];
        foreach ($arrayKeyColum as $column_){
            $takeIndex[] = array_search($column_,$this->fileColumn);
        }
        foreach ($list as $key => $item){
            $items = explode(',',$item);
            $newItems = [];

            foreach ($takeIndex as $index){
                $newItems[$this->columnToParse[$this->fileColumn[$index]]] = $items[$index];
            }
            $list[$key] = $newItems;
        }
        $this->model::query()->insertOrIgnore($list);
    }

    protected function dataUpdate($data,$index){
       try {
           if ($index == 0) {
               if ($this->fileColumn) {
                   $EndCol = strpos($data, $this->fileSeparator) + strlen($this->fileSeparator);
                   $data = substr($data, $EndCol);
               }
           }
          $t = $list = explode($this->fileSeparator, trim($data));
           $arrayKeyColum = array_keys($this->columnToParse);
           $takeIndex = [];
           foreach ($arrayKeyColum as $column_) {
               $takeIndex[] = array_search($column_, $this->fileColumn);
           }

           foreach ($list as $key => $item) {
               if(!empty($item)) {
                   $items = explode(',', $item);
                   $newItems = [];
                   foreach ($takeIndex as $index__) {
                       $newItems[$this->columnToParse[$this->fileColumn[$index__]]] = $items[$index__];
                   }
                   $list[$key] = ($this->addToInsertParams) ? array_merge($newItems, $this->addToInsertParams) : $newItems;
               }else{
                   unset($list[$key]);
               }
           }
           $targetData = array_combine($this->targetDataForUpdate, $this->targetDataForUpdate);

           foreach ($list as $update) {
               if(count(array_intersect_key($update, $targetData)) > 0) {
                   $query = DB::table($this->model->getTable());

                   foreach ($this->targetForUpdate as $filter) {
                       $query->where($filter, "=", trim($update[$filter]), "AND");
                   }
                   $update = array_intersect_key($update, $targetData);
                   foreach ($update as $key =>$upd){
                       $update[$key] = (empty($update[$key]))?0:$update[$key];
                   }
                   $query->update($update);
               }
           }
       }catch (\Throwable $e){
           dd($e->getMessage(),$this->fileColumn,$this->columnToParse, $takeIndex,$this->fileColumn,$items,$t,$this->fileSeparator,$data);
       }
    }


    public function setRuls($array){
        $this->columnToParse = $array;
        return $this;
    }
}
