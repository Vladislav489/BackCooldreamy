<?php
namespace App\ModelAdmin\CoreEngine\Core;


class CodeValidation{
    private $format  = "Y-m-d";
    private $format_  = "Y-m-d H:i:s";
    private $data = [];
    private $validataRuls = [];
    private $statusValid = false;
    private $type = [];
    private $notValidData = [];
    private $errorMessage = [];
    private $validateError = [];
    private $callback = [];

    public function __construct($data = false){
        if($data != false){
            if(is_array($data['data']) && count($data['data']) > 0 ) {
                $this->setCheckData($data['data']);
            }else{
                throw  new \Exception("Нет данных для валидации");
            }
            if(is_array($data['validate']) && count($data['validate']) > 0 ) {
                $this->setValidationRuls($data['validate']);
            }else{
                throw  new \Exception("Нет правил валидации");
            }
            if(is_array($data['validate']) && count($data['validate']) > 0 ) {
                $this->setTypeValue($data['type']);
            }else{
                throw  new \Exception("Нет типов валидации");
            }
            $this->setValidataError($data['error']);
        }
        $this->errorMessage = $this->errorValidata();
    }
    public function setCheckData($data){
        if($data) {
            $this->data = $data;
        }
        return $this;
    }
    public function setValidationRuls($rules){
        if(is_array($rules)) {
            $this->validataRuls = $rules;
        }else{
            throw new \Exception("setValidationRuls параметр не массив");
        }
        return $this;
    }
    public function setTypeValue($type){
        if(is_array($type)){
            $this->type = $type;
        }else{
            throw new \Exception("setTypeValue параметр не массив");
        }
        return $this;
    }
    public function setValidataError($error){
        if(is_array($error)) {
            $this->errorMessage = array_merge($this->errorMessage,$error);
        }
        return $this;
    }
    public function setRulesArray($arrayValidate){
        if(isset($arrayValidate['data']) && is_array($arrayValidate['data'])){
            $this->setCheckData($arrayValidate['data']);
        }
        if(isset($arrayValidate['type']) && is_array($arrayValidate['type'])){
            $this->setTypeValue($arrayValidate['type']);
        }
        if(isset($arrayValidate['validate']) && is_array($arrayValidate['validate'])){
            $this->setValidationRuls($arrayValidate['validate']);
        }
        if(isset($arrayValidate['error_validate']) && is_array($arrayValidate['error_validate'])){
            $this->setValidataError($arrayValidate['error_validate']);
        }
        return $this;
    }

    public function getRulesAsArray(){
        return ['validate'=>$this->validataRuls,
            'data'=>$this->data,
            'type'=>$this->type,
            'error'=>$this->errorMessage
        ];
    }
    public function getValidStatus(){
        return $this->statusValid;
    }
    public function getData(){
        return $this->data;
    }
    public function getRuls(){
        return $this->validataRuls;
    }
    public function getType(){
        return $this->type;
    }
    public function getDataKey(){
        if(is_array($this->data) && count($this->data)){
            return array_keys($this->data);
        }else{
            throw new \Exception("getDataKey Нету данных \$data");
        }
    }
    public function getDataByKey($key){
        return $this->data[$key];
    }
    public function getRulsByKey($key){
        return $this->validataRuls[$key];
    }
    public function getTypeByKey($key){
        return $this->type[$key];
    }
    public function getNotValidData(){
        return $this->notValidData;
    }

    public function getNotValidDataByName($key){
        if(!@is_null($this->notValidData[$key])) {
            return $this->notValidData[$key];
        }else{
            return null;
        }
    }
    public function getErrorValidation(){
        return $this->validateError;
    }

    public function validata(){
        if(count($this->validataRuls) > 0 ){
            $this->checkParams();
            return $this->statusValid;
        }else{
            $this->statusValid = true;
        }
    }
    private function errorValidata($data = NULL,$key = NULL){
        $error =  [
            "maxlen"          =>["code"=>601,"text"=>"Превышена длина значения"],
            "minlen"          =>["code"=>602,"text"=>"Длина значения меньше"],
            "notempty"        =>["code"=>603,"text"=>"Неможет быть пустым"],
            "email"           =>["code"=>604,"text"=>"Неверный формат почты"],
            "phone"           =>["code"=>605,"text"=>"Неверный формат номера телефона"],
            "date"            =>["code"=>606,"text"=>"Неверный формат даты"],
            "datetime"        =>["code"=>606,"text"=>"Неверный формат даты и время"],
            "file"            =>["code"=>607,"text"=>"Не файл"],
            "number"          =>["code"=>608,"text"=>"Присутствуют недопустимые символы"],
            "float"           =>["code"=>609,"text"=>"Число не является десятичным"],
            "min"             =>["code"=>610,"text"=>"Число меньше указанного лимита"],
            "max"             =>["code"=>611,"text"=>"Число привышено лимит"],
            "speshelsymbol"   =>["code"=>612,"text"=>"Присутствует недопустимые символы"],
            "empty"           =>["code"=>612,"text"=>"Не пустой"],
            "template"        =>["code"=>666,"text"=>"Ошибка на template"],
            "list"            =>["code"=>613,"text"=>"Несответствует списку значений"],
            "array"           =>["code"=>614,"text"=>"Не массив"],
            "array|string"    =>["code"=>615,"text"=>"Несответствует списку значений"],
            "list"            =>["code"=>616,"text"=>"Несответствует списку значений"],
            "listKey"         =>["code"=>617,"text"=>"Несответствует списку значений"],
            "listArray"       =>["code"=>618,"text"=>"Несответствует списку значений"]];
        if(is_null($data) && is_null($key )){
            return $error;
        }else{
            foreach ($error as &$item){
                $item['field'] = $data;
                $item['data'] = $key;
            }
            return $error;
        }
    }
    private function checkValueOneList($pattern,$data){
        if(is_array($data)){
            $res = preg_grep($pattern, $data);
            return (count($res) == count($data))? true : false;
        }else {
            return (preg_match($pattern, $data)) ? true : false;
        }
    }

    private function validationParams($param,$feild,$data){
        $result = array();
        foreach ($param as $key => $item){
            $serch = (strpos($key,"_")!==false)?implode('_',$key)[0]:$key;
            switch ($serch){
                case  "maxlen":
                    $result[$key] = (strlen($data) <= $item)?true:false;
                    break;
                case  "minlen":
                    $result[$key] = (strlen($data) >= $item)?true:false;
                    break;
                case  "notempty":
                    $result[$key] = (!empty($data))?true:false;
                    break;
                case  "empty":
                    $result[$key] = ($data == "")?true:false;
                    break;
                case "min":
                    $result[$key] = ($data >= $item)?true:false;
                    break;
                case "max":
                    $result[$key] = ($data <= $item)?true:false;
                    break;
                case "string":
                    $pattern = '/.*/';
                    $result[$key] = $this->checkValueOneList($pattern,$data);
                    break;
                case "list":
                    $result[$key] = (in_array($data,$item))? true :false;
                    break;
                case "listArray":
                    $flag = false;
                    foreach ($data as $Item){
                        if(in_array($Item,$item)!== false){
                            $flag = true;
                        }else{
                            $flag = false;
                            break;
                        }
                    }
                    return $flag;
                    break;
                case "listKey":
                    $flag = false;
                    foreach ($item as $keyItem){
                        if(key_exists($keyItem,$data)!== false){
                            $flag = true;
                        }else{
                            $flag = false;
                            break;
                        }
                    }
                    $result[$key] = $flag;
                    break;
                case "date":
                    $pattern = '/^\d{2,4}[\/\-\.]{1}\d{2,4}[\/\-\.]\d{2,4}$/';
                    $result[$key] = $this->checkValueOneList($pattern,$data);
                    break;
                case "datetime":
                    $pattern = '/^\d{2,4}[\/\-\.]{1}\d{2,4}[\/\-\.]\d{2,4} \d{2}:\d{2}:\d{2}$/';
                    $result[$key] = $this->checkValueOneList($pattern,$data);
                    break;

                case  "email":
                    $pattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
                    $result[$key] = $this->checkValueOneList($pattern,$data);
                    break;
                case  "phone":
                    $pattern = '/^[+]?(\d)?[(]+(\d{0,3})+[)]+\d{1,3}[-]+\d{1,2}[-]+\d{1,2}$/';
                    $result[$key] = $this->checkValueOneList($pattern,$data);
                    break;
                case  "number":
                    $pattern = '/^\d+$/';
                    $result[$key] = $this->checkValueOneList($pattern,$data);
                    break;
                case "float":
                    $pattern = '/^[-+]?[0-9]*[.,]?[0-9]+$/';
                    $result[$key] = $this->checkValueOneList($pattern,$data);
                    break;
                case "speshelsymbol":
                    $pattern = "/['\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:\"\<\>,\.\?\\\]/";
                    $result[$key] = !$this->checkValueOneList($pattern,$data);
                    break;
                case "template":
                    $result[$key] =  $this->checkValueOneList($item,$data);
                    break;
                case "array":
                    $result[$key] = is_array($data);
                    break;
                case "empty":
                    $result[$key] = ($data == "")?true:false;
                    break;
                default:
                    if(isset($this->callback['specialValueCallBack']) && is_callable($this->callback['specialValueCallBack'])){
                        $resback  =   $this->callback['specialValueCallBack']($data,$param,$feild,$this);
                        $result = array_merge($result,$resback);
                    }
                    break;
            }
        }

        foreach ($result as $key => $item){
            if(!$item){
                $this->validateError[$key] = $this->errorValidata($feild,$data)[$key];
            }
        }
        if($this->rulesEmpty($result)){
            unset($this->validateError["empty"]);
        }
    }

    private function rulesEmpty($dataResultValid){
        if(key_exists("empty",$dataResultValid) === true){
            if(count($dataResultValid) > 1){
                return true;
            }
        }else{
            return false;
        }
    }
    private function checkParams(){
        $tempData = $this->data;

        foreach ($this->validataRuls as $keyRuls => $itemRuls) {
            if (!@is_null($this->data[$keyRuls])){
                if (is_array($this->type) && count($this->type) > 0 &&
                    $this->checkType($this->data[$keyRuls],
                        $this->type[$keyRuls],
                        $keyRuls)){

                    $this->validationParams(
                        $itemRuls,
                        $keyRuls,
                        $this->data[$keyRuls]
                    );
                } else {
                    $this->statusValid = false;
                    $this->validateError['type'][] = $keyRuls . " " . $this->type[$keyRuls]
                        . " - Ошибка формата данных";
                }
            } else {
                if(key_exists('required',$itemRuls)){
                    $this->statusValid = false;
                    $this->validateError['required'][] = $keyRuls. " Обязательное поле";
                }
                unset($this->data[$keyRuls]);
            }
        }
        $this->notValidData = array_diff_key($tempData,$this->validataRuls);
        if(count($this->validateError) > 0){
            $this->statusValid = false;
            return ['error'=>["validate"=>$this->validateError]];
        }
        $this->statusValid = true;
    }
    private function preConvertValue($type,$data){
        switch ($type){
            case "int|array":
                break;
            case "int":
                break;
            case "string":
                break;
            case "datetime":
            case "datetime_end_day":
            case "date":
                $date =\DateTime::createFromFormat($this->format, $data);
                $date_ = \DateTime::createFromFormat($this->format_, $data);
                if($date !==false)
                    return $date->format("Y-m-d");
                if($date_ !==false)
                    return $date_->format("Y-m-d H:i:s");
                break;
        }
        return $data;
    }
    private function checkType($data,$type,$keyParam){
        switch ($type){
            case "int|array":
                if(is_array($data)){
                    foreach ($data as $item){
                        if(!is_numeric($item)) {return false;}
                    }
                    return true;
                }
                if(is_numeric($data) && !is_array($data)){return true;}
                break;
            case "array":
                if(is_array($data)) return true;
                break;
            case "int":
                if(is_numeric($data)) return true;
                break;
            case "int|empty":
                if(is_numeric($data) || empty($data)) return true;
                break;
            case "string":
                if(is_string($data)) return true;
                break;
            case "string|array":
                if(is_array($data)){
                    foreach ($data as $item){
                        if(!is_string($item)) {return false;}
                    }
                    return true;
                }
                if(is_string($data) && !is_array($data)) return true;
                break;
            case "date":
                $date =\DateTime::createFromFormat($this->format, $data);
                return ($date !== false)?true:false;
                break;
            case "datetime":
                $date_ = \DateTime::createFromFormat($this->format_, $data);
                if($date_===false){
                    $this->data[$keyParam] = $data . " 00:00:00";
                    $date_ = \DateTime::createFromFormat($this->format_, $this->data[$keyParam]);
                }
                return ($date_ !== false)?true:false;
                break;
            case "datetime_end_day":
                $date_ = \DateTime::createFromFormat($this->format_, $data);
                if($date_===false){
                    $this->data[$keyParam] = $data . " 23:59:59";
                    $date_ = \DateTime::createFromFormat($this->format_, $this->data[$keyParam]);
                }
                return ($date_ !== false)?true:false;
                break;
            case "none":
                return true;
                break;
        }
        return false;
    }
}
