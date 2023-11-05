<?php
namespace App\ModelAdmin\CoreEngine\Core;
use stringEncode\Exception;

class CoreParam{
    private $format  = "Y-m-d";//ФОРМАТ ДАТЫ ОТ ФРОНТА
    private $format_  = "Y-m-d H:i:s"; //ФОРМАТ ДАТЫ ОТ ФРОНТА
    private $request_; // ОБЪЕКТ ДЛЯ ВХОДНЫХ ПАРАМЕТРОВ
    private $params; //ПАРАМЕТРЫ ДЛЯ ОБРАБОТКИ ВХОДНЫЕ ДАННЫЕ ОТ ФРОНТА

    private $paramsArray;

    private $ParamsToWhere = []; //МАССИВ ПРАВИЛ ДЛЯ ФОРМИРОВАНИЯ WHERE
    private $objValidate; //ОБЪЕКТ ОТВЕЧАЮЩИЙ ЗА ВАЛИДАЦИЮ ВХОДНЫХ ПАРАМЕТРОВ
    private $error = false; // ОШИБКИ  РАБОТЫ APIBOX
    private $callback = null;  // МАССИВ ДЛЯ ХРАНЕНИЯ CALLBACK FUNCTION НКЖЕН ДЛЯ ИСКЛЮЧИТЕЛЬНЫХ СИТУВЦИЙ
    private $checkParamsRule; //ХРАНИЛЕШЕ ПРАВИЛ РАБОТЫ ВСЕГО КЛАССА

    public function __construct(){

    }
    //ДЛЯ ПЕРЕДАЧИ ОБЪЕКТА ВАЛИДАЙИИ
    public function setValidateObj($ValidatorCustom){
        if($ValidatorCustom instanceof CodeValidation) {
            $this->objValidate = $ValidatorCustom;
        }else{
            throw new Exception("Передан не соответствующий
                    класс ValidatorCustom(!=)".get_class($ValidatorCustom));
        }
        return $this;
    }
    //ДЛЯ ДОБАВЛЕНИЯ ВХОДНОГО ПАРАМЕТРА
    public function setParamsFilterNew($key,$value){
        $this->params[$key] = $value;
        return $this;
    }
    //Yii ОБЪЕКТ REQUEST
    public function setRequest(&$request){
        if(!is_null($request) && is_object($request)){
            $this->request_ = $request;
            $this->params = $this->request_->getQueryParams();
        }else{
            throw new Exception('Критическая ошибка!!');
        }
        return $this;
    }
    //  ПРЯМАЯ ПЕРЕДАЧА ПАРАМЕТРОВ
    public function setParams($params){
        if(is_array($params)){
            $this->paramsArray = $params;
            $this->params = $params;
        }else{
            throw new Exception('Критическая ошибка должен быть массив!!');
        }
        return $this;
    }
    //УСТАНОВКА ПРАВИЛ
    public function setRules($rules){
        unset($this->objValidate);
        $this->objValidate = new CodeValidation();
        $this->checkParamsRule = $rules;
        $this->params = ($this->request_)?$this->getAllRequestParams():$this->paramsArray;
        $this->error = $this->checkParams();
        return $this;
    }
    // пОЛУЧИТЬ ВСЕ ОШИБКИ ВАЛИДАЦИИ
    public function getErrorValidate(){
        return ['error'=>['validate'=>$this->objValidate->getErrorValidation()]];
    }
    //ПОЛУЧИТЬ СТАНДАРТНЫЙ ФОРМАТ ДАТЫ (ФОРМАТ ФРОНТА)
    public function getFormatDate(){
        return $this->format;
    }
    //ПОЛУЧИТЬ ОБЪЕКТ ВАЛИДАЦИИ В APIBOX
    public function getObjValidate(){
        return $this->objValidate;
    }
    //ПОЛУЧИТЬ ВСЕ ВХОДНЫЕ ПАРАМЕТРЫ
    public function getAllRequestParams(){
        return  ($this->request_)?$this->request_->getQueryParams():$this->paramsArray;
    }
    //ПОЛУЧИТЬ КОНКРЕТНЫЙ ВХОДНОЙ ПАРАМЕТР
    public function getParamsFilter($key){
        if(isset($this->params[$key])) {
            return $this->params[$key];
        }else{
            return false;
        }
    }
    public function getParamsAndField(){
        if(is_array($this->checkParamsRule)){
            $return = [];
            foreach ($this->checkParamsRule as $item){
                if(key_exists('params',$item) && key_exists('field',$item)){
                    $return[$item['params']] = $item['field'];
                }
            }
            return $return;
        }else{
            $this->error[] = "Возникла ошибка checkParamsRule не массив или пуст";
            return [];
        }

    }
    //ПОЛУЧИТЬ ВСЕ ПАРАМЕТРЫ
    public function getAllParams(){
        return $this->params;
    }
    //ПОЛУЧИТЬ СТАТУС ВАЛИДАЦИ ПРОЙДЕН ИЛИ НЕТ
    public function getStatusValidate(){
        return $this->objValidate->getValidStatus();
    }
    // ПОЛУЧИТЬ ПРАВИЛА ВАЛИДАЦИИ
    public function getRulesValidate(){
        return $this->objValidate->getRulesAsArray();
    }
    // ПОЛУЧИТЬ ПАРАМЕТРЫ КОТОРЫЕ НЕ ПРОШЛИ ПРОВЕРКУ
    public function getNotValidateParams(){
        return $this->objValidate->getNotValidData();
    }

    // ПОЛУЧИТЬ ПАРАМЕТРЫ КОТОРЫЕ НЕ ПРОШЛИ ПРОВЕРКУ ПО ИМЕНИ
    public function getNotValidateParamsByName($key){
        return $this->objValidate->getNotValidDataByName($key);
    }

    //есди нужеН специфическая обработка входного параметра
    public function specialValueCallBack($callFunction){
        $this->callback['specialValueCallBack'] = $callFunction;
        return $this;
    }
    // удалить входной параметр
    public function removeParamertFiltrer($key){
        unset($this->params[$key]);
        return $this;
    }
    //строит where
    public function getconsertParams($type,$data){
        return $this->preConvertValue($type,$data);
    }
    public function getWhereSql(){
            $return = [];

            if (count($this->ParamsToWhere)>0){
                foreach ($this->ParamsToWhere as $filed => $value) {
                    $filed_where = preg_replace('/_СLONE_\\d*/', '', $filed, -1, $count);
                    $rez = $this->preprocessingWhereAction(
                    $this->checkParamsRule[$value['key']]["action"],
                    $filed_where,
                    $value['data']);
                    $rez[] = $this->checkParamsRule[$value['key']]['concat'];
                    $rez[] = $value['type'];
                    $return[] = $rez;
                }
            }
         return $return;
    }
    //ВЫПОЛНЯЕТ ОСНОВНУЮ ЛОГИКУ ПРОВЕРКИ И СОЗДАНИЯ ПАРАМЕТРОВ ДЛЯ WHERE
    private function checkParams(){
        $this->buildDefaultValue($this->checkParamsRule);
        $this->buildValidationRules($this->checkParamsRule);
        $this->objValidate->validata();
        if(count($this->objValidate->getErrorValidation()) > 0){
            return ['error'=>["validate"=>$this->objValidate->getErrorValidation()]];
        }
        $this->params = $this->objValidate->getData();
        $this->ParamsToWhere = [];
        if(!empty($this->checkParamsRule)) {
            foreach ($this->checkParamsRule as $key => $ApiParam) {
                if (!@is_null($this->params[$ApiParam['params']])) {
                    if (isset($ApiParam['field'])) {
                        $this->params[$ApiParam['params']] = $this->specialValue($this->params[$ApiParam['params']], $ApiParam['params'], $ApiParam['field']);
                        $this->checkWherekey(
                            $ApiParam['field'],
                            $this->preConvertValue($ApiParam['type'], $this->params[$ApiParam['params']]),
                            $key, $ApiParam['type']);
                    }
                }
            }
        } else {
            throw new Exception("Filter array not set, call method filter in constructor");
        }
    }
    //если находит с одинаковыми ключами создает дополнительный ключ
    private function checkWherekey($field,$data,$key,$type){
        if(key_exists($field,$this->ParamsToWhere)!==true){
            $this->ParamsToWhere[$field] = array("data"=>$data,"key"=>$key,'type'=>$type);
        }else {
            $index = 0;
            $keys = array_keys($this->ParamsToWhere);
            foreach ($keys as $item) {
                if (strpos($field . "_СLONE_", $item) === true) {
                    $index += 1;
                }
            }
            $this->ParamsToWhere[$field . "_СLONE_" . $index] = array("data"=>$data,"key"=>$key,'type'=>$type);
        }
    }

    public function convertDate($date){
        $dateObject =\DateTime::createFromFormat($this->format, $date);
        if($dateObject !== false) return $dateObject->format("Y-m-d");

        $dateObject = \DateTime::createFromFormat($this->format_, $date);
        if($dateObject != false) return $dateObject->format("Y-m-d H:i:s");

        return false;
    }

    //обработки для конвертации данных
    private function preConvertValue($type,$data){
        switch ($type){
            case "int|array":
                if(is_array($data)) {
                    foreach ($data as $key => $item){
                        $data[$key] = urldecode($item);
                    }
                    return $data;
                } else {
                    return (string) urldecode($data);
                }
                break;
            case "int":
                break;
            case "string":
                return (string) urldecode($data);
                break;
            case "string|array":
                    if(is_array($data)) {
                        foreach ($data as $key => $item){
                            $data[$key] = urldecode($item);
                        }
                        return $data;
                    } else {
                        return (string) urldecode($data);
                    }
                break;
            case "datetime":
            case "datetime_end_day":
            case "date":
                return $this->convertDate($data);
        }
        return $data;
    }
    //ОБРАБОТКА ЭКШЕНОВ WHERE
    private function preprocessingWhereAction($action,$filed_where,$data){
        $whereArray = [];
        switch($action){
            case "OR LIKE":
                if(!is_array($data)){
                    $data = [$data];
                }
                $whereArray = [$filed_where,$action,$data];
                break;
            case "%LIKE%":
            case "%LIKE":
            case "LIKE%":
                $searchText = str_replace("LIKE",$data,$action);
                $whereArray = [$filed_where,str_replace("%","",$action),$searchText];
                break;
            case "IN":
            case "NOT IN":
                $whereArray = [$filed_where,$action,$data];
                break;
            default:
                $whereArray = [$filed_where,$action,$data];
                break;

        }
        return $whereArray;
    }

    //для обработки параметров
    private function specialValue($data,$params,$fieldName){
        switch ($fieldName){
            default:
                if(isset($this->callback['specialValueCallBack']) && is_callable($this->callback['specialValueCallBack'])){
                    return $this->callback['specialValueCallBack']($data,$params,$fieldName,$this);
                }else{
                    return $data;
                }
                break;
        }
    }
    //если нет параметра request значит он дефолтный и должно быть указано defaultValue
    private function  buildDefaultValue($checkParamsRule){
        if(is_array($checkParamsRule) && !empty($checkParamsRule)) {
            foreach ($checkParamsRule as $key => $item) {
                if (key_exists('defaultValue', $item)) {
                    $this->params[$item['params']] = $item['defaultValue'];
                }
            }
            return $this->params;
        }
        return false;
    }
    //ПОСТРОЕНИЯ ПРАВИЛА ВАЛИДАЦИ НА ОСНОВЕ ПРАВИЛ
    private function buildValidationRules($dataValidation){
        if(is_array($this->params) && count($this->params) > 0) {
            $rulesValidation = $this->buildValidateRulesArray($dataValidation);
            $rulesValidation['data'] = $this->params;
            $this->objValidate->setRulesArray($rulesValidation);
            return $rulesValidation;
        }
        return true;
    }
    //РАСПАРСИТЬ ПРАВИЛА APIBOX ПОД ПРАВИЛА ВАЛИДАЦИИ
    private function buildValidateRulesArray($dataValidation){
        $rulesValidation = array();
        try {
            foreach ($dataValidation as $key => $item) {
                if (key_exists('validate', $item) !== false) {
                    $rulesValidation['validate'][$item['params']] = $item['validate'];
                }
                if (key_exists('type', $item) !== false) {
                    $rulesValidation['type'][$item['params']] = $item['type'];
                }
                if (key_exists('error_validate', $item) !== false) {
                    $rulesValidation['error_validate'][$item['params']] = $item['error_validate'];
                }
            }
        }catch (\Throwable $e){
            dd(
                "CoreParasm",
                [$dataValidation,$this->params  ],
                " Проверьте правельность написание фильтров пример ".
                "[   'field'=>tab.'.','id'  ,'params'=> 'id',
                    'validate' =>['string'=>true,'empty'=>true],
                    'type' => 'string|array',
                    'action' => '=', 'concat' => 'AND'
                ]"
            );
        }
        return $rulesValidation;
    }

    public static function lowerKey($arr){
        return array_map(function($item){
            if(is_array($item))
                $item = self::lowerKey($item);
            return $item;
        },array_change_key_case($arr,CASE_LOWER));
    }
}
