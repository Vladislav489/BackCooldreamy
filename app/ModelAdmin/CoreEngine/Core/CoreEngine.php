<?php
namespace App\ModelAdmin\CoreEngine\Core;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;
use function Sodium\library_version_major;

class CoreEngine{
    const DEFAULT_LIMI = 100;

    protected $engine = null;
    protected $select;
    protected $group_by = [];
    protected $join_by = [];
    protected $group_params;
    protected $query;
    protected $default = [];

    protected $coreParams;
    protected $filter;
    protected $params;

    protected $pagination = ['totalCount' =>0,'countPage'=>0,'pageSize' => false,'page'=> 1];
    protected $paginationOff = false;
    protected $limit = self::DEFAULT_LIMI;
    protected $union = false;

    private   $joinTable = [];
    private   $checkJoin = [];
    private   $debug_sql = false;
    private   $query_debug_sql = true;
    private   $error = [];

    public function __construct($params,$select = [],$callback = null ){
        $this->setSelect((is_array($select) && count($select) > 0)? $select:$this->defaultSelect());
        $this->union = (key_exists('union',$params))?true:false;
        $this->params = $params;
        $this->coreParams = new CoreParam();
        $this->coreParams->setParams($this->params);
        if(!is_null($callback)) $this->coreParams->specialValueCallBack($callback);
        $this->coreParams->setRules($this->filter);
    }

    public function getRealExistFilter() {
        $return = [];
        foreach ($this->filter as $item)
            if (isset($this->params[$item['params']]))
                $return[$item['params']] = $this->params[$item['params']];

        return $return;
    }
    public function getParamsFilterAll() {
        $return = [];
        if (is_array($this->filter) && count($this->filter) > 0 ) {
            foreach ($this->filter as $item)
                $return[] = $item['params'];
            return $return;
        }
        return [];
    }
    //ДЛЯ ДЕБАГА
    public function OffQuery() {
        $this->query_debug_sql = false;
        return $this;
    }
    //ВКЛЮЧАЕТ ВЫВОД ДЕБАНА ТЕКСТ ЗАПРОСА И РЕЗУЛЬТАТ
    public function OnDebug() {
        $this->debug_sql = true;
        return $this;
    }
    public function offPagination() {
        $this->paginationOff = true;
        return $this;
    }
    public function onPagination() {
        $this->paginationOff = false;
        return $this;
    }
    ///////////////////////////////////////
    public function setGroupBy($group_by) {
        $this->group_by = $this->prepareRequest($group_by);
        return $this;
    }
    public function setModel($model) {
        if( get_parent_class($model) ==  "Model") {
            $this->engine = $model;
            $this->query = $this->engine->newQuery();
        }else{
            throw  new \Exception("Это не Модель");
        }
        return $this;
    }
    public function setQuery($query) {
        $this->query = $query;
        return $this;
    }
    //Входны данные для построения WHERE
    public function setRequired($name) {
        $this->filter[$this->getFilterByName($name)['key']]['validate']['required'] = true;
        return $this;
    }
    public function setParams($params) {
        $this->coreParams = new CoreParam();
        $this->params = $this->prepareRequest($params);
        $this->coreParams->setParams($this->params)->setRules($this->filter);
        if($this->debug_sql) {
            var_dump("params",$this->params);
            echo "<br>";
        }
        return $this;
    }
    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }
    public function setSelect($select) {
        $this->select = $this->prepareRequest($select);
        return $this;
    }
    //ПРИНУДИТЕЛЬНО ПОДКЛЮЧЕНИЕ ПОДКЛЮЧАЕМЫХ ТАБЛИЦ.
    public function setJoin($join) {
        $join = (is_string($join))?[$join]:$join;
        $this->joinTable = $join;
        foreach ($join as $item)
            if(key_exists($item,$this->group_params['relatedModel']))
                if($item && !in_array($item,$this->group_by))
                    $this->join_by[$item] = $item;

        return $this;
    }
    public function getcoreParamsClass() {return $this->coreParams;}
    public function getSchema() {
      if(is_object($this->engine))
          return get_class($this->engine)::getTableSchema();
      return [];
    }
    public function getEngine() {return $this->engine;}
    public function getQuery() {return $this->query;}
    public function getParams() {return  $this->params;}
    public function getGroupBy() {return $this->group_by;}
    //ПОЛУЧЕНИЕ СВОЙСТВ ПО ИМЕНИ ПАРАМЕТРА
    public function getPropertyFilterByParam($name) {
        $res = $this->getFilterByName($name);
        return ($res)? $res['value']:$res;
    }
    //УДАЛИТЬ ИЗ ФИЛЬТРОВ ОПРЕДЕЛЕННЫЙ ФИЛЬТ ПО ПАРАМЕТРУ
    public function removeFilterByParam($name) {
        unset($this->filter[$this->getFilterByName($name)['key']]);
    }
    //ПРИМИТИВНАЯ ВЫБОРКА БЕЗ УЧЕТА ФИЛЬРА
    public function getSandartResultOne() {
        $result = [];
        $result['pagination'] = $result['result'] = [];
        if($this->coreParams->getStatusValidate()) {
            if($this->debug_sql) $this->debugQuery();
            if($this->query_debug_sql) $result = $this->query->get()->toArray();

            $result = (count($result) > 0 )? $result[0]:[];

            if(in_array('url_icon',$result))
                if (is_null($result['url_icon']) && empty($result['url_icon']))
                    $result['url_icon'] = "icon_currence/NONE.png";

            return $result;
        }
        return $this->coreParams->getErrorValidate();
    }
    public function removeParams($nameParam) {
        unset($this->params[$nameParam]);
        $return = $this->coreParams->getParamsFilter($nameParam);
        $this->coreParams->removeParamertFiltrer($nameParam);
        return $return;
    }
    //ДЛЯ ГРУПОВОГО ПЕЗУЛЬТАТА
    public function getSandartResultGroup() {
        $result  = [];
        $result['result'] = $result['pagination'] = [];
        if($this->coreParams->getStatusValidate()){
            if($this->debug_sql) $this->debugQuery();
            if($this->query_debug_sql) $result['result'] = $this->query->get()->toArray();
            $result['pagination'] = $this->pagination;
            return $result;
        }
        return $this->coreParams->getErrorValidate();
    }
    public function getSandartResultList() {
        $result = [];
        $result['result'] = $result['pagination'] = [];
        if ($this->coreParams->getStatusValidate()) {
            if ($this->debug_sql) $this->debugQuery();
            $result['pagination'] = $this->pagination;
            $result['totalCount'] = ($this->pagination['totalCount'] != 0) ? (int)$this->pagination['totalCount'] : 0;
            if ($this->query_debug_sql)
                if ($this->union) {
                    if (!isset($this->params['union_type'])) $this->params['union_type'] = "UNION";
                    if (!isset($this->params['union_group'])) $this->params['union_group'] = null;
                    $result['result'] = $this->unionQueryOneObjectCore($this->params['union'], $this->params['union_group'], $this->params['union_type']);
                } else {
                    $result['result'] = $this->query->get()->toArray();
                }
            return $result;
        }
        return $this->coreParams->getErrorValidate();
    }

    public function getFullQuery(){
        return (count($this->group_by))? $this->executeGroup():$this->executeFilter();
    }

    public function getSqlToStrFromQuery(){
        $data = $this->query->getBindings();
        foreach ($data as $key => $item) $data[$key] = "'".$item."'";
        return Str::replaceArray("?",$data,$this->query->toSql());
    }

    public function getSqlToStr(){
        (count($this->group_by))? $this->executeGroup():$this->executeFilter();

         $data = $this->query->getBindings();
        foreach ($data as $key => $item) $data[$key] = "'".$item."'";
        return Str::replaceArray("?",$data,$this->query->toSql());
    }

    //СТАНДАРТНЫЙ ВЫВОД ДАНЫХ
    public function getTotal() {
        return $this->executeFilter()->count();
    }
    public function getGroup() {
        $this->executeGroup();
        return $this->getSandartResultGroup();
    }
    public function getList() {
        $this->executeFilter();
        return $this->getSandartResultList();
    }
    public function getOne() {
        $this->executeFilter();
        return $this->getSandartResultOne();
    }
    public function Exist() {
        $this->offPagination();
        $this->executeFilter();
        if($this->coreParams->getStatusValidate()) {
            if ($this->debug_sql) $this->debugQuery();
            return $this->query->exists();
        }
        return false;
    }
    // ОСНОВНЫЕ ФУНКЦИИ ДЛЯ  ВЫПОЛНЕНИЯ ЗАПРОСОВ
    public function executeFilter() {
        $this->selectQuery()->connectEntityJoin()->whereQuery($this->coreParams->getWhereSql());
        if (!$this->paginationOff) {
            $this->pagination();
        } else {
            ($this->limit && $this->limit !== self::DEFAULT_LIMI)? $this->query->limit($this->limit):"";
        }
        $this->order();
        if ($this->debug_sql) $this->debugQuery();
        return $this->query;
    }
    public function executeGroup() {
        $this->selectQueryGroup()->connectEntityJoin()->whereQuery($this->coreParams->getWhereSql());
        if (!$this->paginationOff) {
             $this->pagination();
        } else {
            if($this->limit &&  $this->limit !== self::DEFAULT_LIMI)
                $this->query->limit($this->limit);
        }
        $this->groupQuery()->order();
        if ($this->debug_sql) $this->debugQuery();
        return $this->query;
    }
    // ПАГИНАЦИЯ ВКЛЮЧАЕТСЯ ТОЛЬКО ЕСЛИ ЕСТЬ PAGESIZE ИЛИ LIMIT
    public function pagination($page = null,$pageSize = null) {
        $query = clone $this->query;
        $totalCount = $query->count();
        if(is_null($page) && is_null($pageSize)){
            $pageSize = $this->coreParams->getParamsFilter('pageSize');
            $page = $this->coreParams->getParamsFilter('page');
            if (!$pageSize) $pageSize = (empty($this->limit))?false:$this->limit;
            if ($pageSize == -1) $pageSize = false;
            $page = ($page > 0 && !is_null($page))?($page-1):0;
        }
        if($pageSize) {
        $countPage = (int)($totalCount / $pageSize);
        $countPage += (($totalCount % $pageSize) == 0)?0:1;
        $this->pagination = ['totalCount' => $totalCount,'countPage' => $countPage,'pageSize' => $pageSize,'page' => $page+1];

            $this->limit = $pageSize;
            $this->query->offset($page*$this->limit)->limit($this->limit);
        }else{
            $this->pagination = ['totalCount' => $totalCount,'countPage' => 0,'pageSize' => 0,'page' => 1];
        }
        return $this->pagination;
    }
    //СОРТИРОВКА
    public function order($sort_dir = null, $sort_by = null) {
        if(is_null($sort_dir) && is_null($sort_by)) {
            $sort_dir = $this->coreParams->getParamsFilter('sort_dir') ?: 'asc';
            $sort_by = $this->coreParams->getParamsFilter('sort_by') ?: false;
        }

        if($sort_by) {
            $sort_by = (is_array($sort_by))? $sort_by:[$sort_by];
                foreach ($sort_by as $item_sort)
                    $this->query->orderBy($item_sort, $sort_dir);
        }
        return $this;
    }
    public function unionQueryOneObjectCore($queryParasm,$group = null,$typeUnion = "UNION") {
        $query = $listOject = [];
        $add_to_all_select = null;
        $class = get_class($this);

        $field = $this->getFilterByName($group);
        if($field && key_exists('field',$field['value']))
                $field = $field['value']['field'];
        else
            $field = ($field)?$group:$field;
        if (preg_match("/.*\..*/",$field)) {
            $group = explode(".", $field)[1];
            if (in_array($group, $this->engine->getFillable()))
                $this->select[] = $group;
            else
                $add_to_all_select = $group;
        } else {
            $add_to_all_select = $group;
        }

        foreach ($queryParasm as $key => $paramsOneWhere) {
            $selectUnion = $this->select;
            if (!empty($this->params))
                $paramsOneWhere =  array_merge($paramsOneWhere,$this->params);
            if (!is_null($add_to_all_select))
                $selectUnion[] = DB::raw("'".$paramsOneWhere[$add_to_all_select]."' as `".$add_to_all_select."`");

            $listOject[$key] = new $class($paramsOneWhere,$selectUnion);

            if (!empty($this->joinTable))
                $listOject[$key]->setJoin($this->joinTable);

            (isset($paramsOneWhere['group']) && $paramsOneWhere['group'] == true) ? $listOject[$key]->executeGroup() : $listOject[$key]->executeFilter();
            $query[] = "(".$listOject[$key]->queryText().")";
        }
        $res = json_decode(json_encode(DB::select(implode("\n".$typeUnion."\n",$query))),true);

        if (!is_null($group)) {
            $group_list = [];

            foreach ($res as $item)  $group_list[$item[$group]][] = $item;

            $res = $group_list;
            unset($group_list);
        }
        return $res;
    }
    public function queryText() {
        $dataWhere = $this->query->getBindings();

        foreach ($dataWhere as &$item)  $item = "'".str_replace(['"',"'"],['\"',"\'"],$item)."'";

        return Str::replaceArray("?", $dataWhere,$this->query->toSql());
    }
    // в перспективе
    public function unionQueryBuild($listObjct ,$group = null,$groupQuery = false){
        $unionQuery =  DB::query();
        $select = [];
        foreach ($listObjct as $class){
            if(strpos(get_parent_class($class),"CoreEngine")  !== false ){
                ($groupQuery) ? $class->executeGroup() : $class->executeFilter();
                $select[] = $class->engine->getFillableTable();
            }
        }
    }
    //ДЛЯ ГРУПИРОВКИ ПОДКЛЮЧЕНИЕ ПОЛЕЙ ГЛАВНОЙ ТАБЛИЦЫ БЕЗ УЧЕВТА ГРУППИРОВКИ
    private function selectQueryGroup() {
        $this->select = $this->prepareRequest($this->select);
        foreach ($this->select as $value) {
            if (count($this->group_params['select']) > 0) {
                if (isset($this->group_params['select'][$value]))
                    $this->query->addSelect($this->group_params['select'][$value]);
            }else{
                if(!is_null($value))
                    $this->query->addSelect($value);
            }
        }
        return $this;
    }
    private function selectQuery() {
        $select = $this->coreParams->getParamsFilter('select');
        if ($select) {
            $this->select = $this->prepareRequest($select);
            $flagMainColum = true;
            foreach ($this->select as  $key => $value) {
                if (is_numeric($key)) {
                    $flagMainColum  = false;
                    $this->query->addSelect($this->engine->getTable() . "." . $value);
                } else {
                    if (is_array($this->group_params['relatedModel'][$key]['entity'])) {
                        $join = each($this->group_params['relatedModel'][$key]['entity']);
                        $tab = $join['key'];
                    } else if (is_string($this->group_params['relatedModel'][$key]['entity'])) {
                        $tab = $this->group_params['relatedModel'][$key]['entity'];
                    } else {
                        $tab = $this->group_params['relatedModel'][$key]['entity']::getTableName();
                    }
                    $this->setJoin([$key]);
                    if (is_array($value)) {
                        foreach ($value as $item) $this->query->addSelect($tab . "." . $item);
                    } else {
                        $this->query->addSelect($tab . "." . $value);
                    }
                }
            }

            if($flagMainColum)
                foreach ($this->defaultSelect() as $value) $this->query->addSelect($value);
        } else {
            if(!is_null($this->select)) {
                foreach ($this->select as $value)
                    ($value instanceof \Illuminate\Database\Query\Expression) ?
                        $this->query->addSelect($value) : $this->query->addSelect($this->engine->getTable() . "." . $value);
            }
        }
        return $this;
    }
    //ДЛЯ ГРУППИРОВКИ ПОДКЛЮЧЕНИЕ ДРУГИХ ТАБЛИ И ДОПОЛНИТЕЛЬНЫХ ПОЛЕЙ (ИСПОЛЬЗУЕТСЯ ТОЛЬКО ДЛЯ ГРУППИРОВКИ)
    private function groupQuery() {
        $this->group_by = $this->prepareRequest($this->group_by);
        foreach ($this->group_by as $key => $value) {
            if(isset($this->group_params['relatedModel'][$key])){
                if(!in_array($key,$this->checkJoin)) {
                    $this->checkJoin[] = $key;
                    $this->relatedData($this->group_params['relatedModel'][$key]);
                }
            } else if (isset($this->group_params['custom_select'][$value])){
                $this->customSelect($this->group_params['by'][$value], $this->group_params['custom_select'][$value]);
            } else if (isset($this->group_params['by'][$value])) {
                $this->standartField($this->group_params['by'][$value]);
            }
        }
        return $this;
    }
    // ДЛЯ РАБОТЫ С ФИЛЬТРАМИ ПОИСК ПО НАЗВАНИЮ ПАРАМЕТРА ФИЛЬТРА
    private function getFilterByName($name) {
        foreach ($this->filter as $key => $value)
            if($value['params'] == $name)
                return ["key" => $key,"value" => $value];

        return false;
    }
    //УСТАНОВКА ФИЛЬТРОВ WHERE
    private function whereQuery($where) {
        if($this->debug_sql) var_dump($where);
    //0 => полк 1 => действие 2 => значае 3=> AND OR
        foreach ($where as $item){
           switch ($item[1]){
               case 'IN':
                   $this->query->whereIn(DB::raw($item[0]),is_array($item[2])?$item[2]:[$item[2]]);
                   break;
               case "NOT IN":
                   $this->query->whereNotIn(DB::raw($item[0]),is_array($item[2])?$item[2]:[$item[2]]);
                   break;

               case "RAW":
                    if(preg_match("/~\?\d+~/",$item[0])){
                        $ar_tes = [];
                        preg_match_all("/~\?\d+~/",$item[0],$ar_tes);
                        if(is_array($item[2])){
                            if(count($ar_tes[0]) > 0){
                                $temp = [];
                                foreach ($ar_tes[0] as $item__){
                                    $data_ = str_replace(["?", '~'], '', $item__);
                                    $temp[$item__] = "'".$item[2][$data_ - 1]."'";
                                }
                                $item[0] = str_replace(array_keys($temp), $temp, $item[0]);
                                $this->query->whereRaw($item[0], null, $item[3]);
                            }else {
                                $data_ = str_replace(["?", '~'], '', $ar_tes[0][0]);
                                $item[0] = str_replace($ar_tes[0][0], '?', $item[0]);
                                $this->query->whereRaw($item[0], $item[2][$data_ - 1], $item[3]);
                            }
                        }else{
                            $item[0] = str_replace($ar_tes[0][0],'?',$item[0]);
                            $this->query->whereRaw($item[0], $item[2], $item[3]);
                        }
                    }else{

                        $this->query->whereRaw($item[0], $item[2], $item[3]);
                    }
                   break;

               case "CHECK_NULL":
                   if((int)$item[2] == 0)
                        $this->query->whereNull(DB::raw($item[0]));
                   else
                       $this->query->whereNotNull(DB::raw($item[0]));
                   break;
               default:
                   $this->query->where(DB::raw($item[0]),$item[1],$item[2],$item[3]);
                   break;
           }
       }
       return $this;
    }
    //ДЛЯ СПИСКА ПОДКЛЮЧЕНИЕ ДРУГИХ ТАБЛИ
    private function filterJionQuery(){
        foreach ($this->join_by as $key => $value) {
            if (isset($this->group_params['relatedModel'][$key])) {
                if(!in_array($key,$this->checkJoin)){
                    $this->checkJoin[] = $key;
                    $this->relatedData($this->group_params['relatedModel'][$key]);
                }
            }
        }
        return $this;
    }
    //ЕСЛИ ПРИШЛО ЗНАЧЕНИЕ НЕ МАСИВ ПЕРЕВОДИМ В МАССИВ
    private function prepareRequest($data) {
        $data = ($data) ? $data : [];
        $data = (is_array($data)) ? $data : [$data];
        return $data;
    }
    // ВСПОМАГАТЕЛЬНЫ ФУНКЦИИ ДЛЯ ЗАПРОСОВ ОБЩИЕ ДЛЯ ГРУПП И СПИСКОВ
    private function customSelect($code, $value) {
        if(!empty($value))
            $this->query->addSelect($value);
        $this->query->groupBy($code);
        return $this;
    }
    //ДЛЯ ГРУППИРОВКИ ПОЛЕЙ  (ВЫБОРКА И ГРУППИРОВКА ПО ПОЛЮ)

    private function  getTableSchema(){
        $table = $this->engine->getTable();
        $list = DB::select("DESCRIBE ".$table);
        $newList = [];
        foreach ($list as $item) $newList[$item->Field] = $item;

        return $newList;
    }

    private function standartField($field) {
        $colums = $this->getTableSchema();
        $keys =  array_keys($colums);


        if(!is_array($field)) {
            if (in_array($field, $keys)) {
                if (in_array($colums[$field]->Type, array("datetime", "date", 'timestamp'))) {
                    $this->query->groupBy(DB::raw("DATE(" . $this->engine->getTable() . '.' . $field . ")"));
                    $this->query->addSelect(DB::raw("DATE(" . $this->engine->getTable() . '.' . $field . ")"));
                } else {
                    if (is_object($field) && $field::class == 'Illuminate\Database\Query\Expression') {
                        $this->query->groupBy($field);
                        if (!empty($field)) {
                            $this->query->addSelect(DB::raw($field));
                        }
                    } else {
                        $this->query->groupBy($this->engine->getTable() . '.' . $field);
                        if (!empty($field)) {
                            $this->query->addSelect($this->engine->getTable() . '.' . $field);
                        }
                    }
                }
            } else {
                $this->query->groupBy(DB::raw($field));
                if (!empty($field)) {
                    $this->query->addSelect(DB::raw($field));
                }
            }
        }else{
            if (is_object($field['group']) && $field['group']::class == 'Illuminate\Database\Query\Expression') {
                $this->query->groupBy($field['group']);
                if (!empty($field['field'])) {
                    $this->query->addSelect(DB::raw($field['field']));
                }
            } else {
                $this->query->groupBy($this->engine->getTable() . '.' . $field['group']);
                if (!empty($field['field'])) {
                    $this->query->addSelect($this->engine->getTable() . '.' . $field['field']);
                }
            }
        }
        return $this;
    }
    // ДЕЛАЙЕТ ДЖОЙН ТАБЛИЦ ДЛЯ ПОДКЛЮЧЕНИЯ   ЕСЛИ НЕ ПОЛЯ ТО ГРУПИРОВКА И ЫЕЛЕК ПО ПОЛЮ НЕ ДЕЛАЕТСЯКАК
    private function relatedData($related){
        $table ="";
        if(is_string($related['entity'])) {
            $table = $related['entity'];
            $joinQuery = DB::raw( $table."  ON ".$this->relatedConfig($related));
        }else if(is_array($related['entity'])){
            $join = each($related['entity']);
            $table = $join['key'];
            $joinQuery = DB::raw( $table."  ON ".$this->relatedConfig($related));
        }else if($related['entity']::class == 'Illuminate\Database\Query\Expression'){
            $joinQuery = $related['entity'];
        }else{
            $table = $related['entity']->getTable();
            $joinQuery = DB::raw( $table."  ON ".$this->relatedConfig($related));
        }

       if(isset($related['type']) && $related['type'] == "right")
            $this->query->rightJoin( $joinQuery,function(){});
       else if (isset($related['type']) && $related['type'] == "inner")
            $this->query->join( $joinQuery,function(){});
       else if(isset($related['type']) && $related['type'] == "left")
            $this->query->leftJoin( $joinQuery,function(){});
       else
            $this->query->leftJoin( $joinQuery,function(){});


        if(isset($related['field']))
            foreach ($related['field'] as $item ) {
                if($table != "")
                    $this->query->addSelect(DB::raw($table . "." . $item));
                else
                    $this->query->addSelect(DB::raw($item));
            }

        return $this;
    }
    //КОНФИГУРАЦИИ СВЯЗ ТАБЛИЦ
    private function relatedConfig($config)
    {
        $joinOn = "";
        if (is_string($config['entity'])) {
            if (stripos($config['entity'], " as ") != false) {
                $joinOn = sprintf("%s.%s", substr($config['entity'], stripos($config['entity'], " as ") + strlen(" as ")), $config['relationship'][0]);
            } else {
                $joinOn = sprintf("%s.%s", $config['entity'], $config['relationship'][0]);
            }
        } else if (is_array($config['entity'])) {
            $join = each($config['entity']);
            $joinOn = (empty($join['value'])) ? $config['relationship'][0] : sprintf("%s.%s", $join['value'], $config['relationship'][0]);
        } else {
            $joinOn = sprintf("%s.%s", $config['entity']->getTable(), $config['relationship'][0]);
        }
        $joinOnMore = "";
        if (isset($config['relationship_more'])) {
            if (is_array($config['relationship_more'])) {
                foreach ($config['relationship_more'] as $field => $value)
                    $joinOnMore .= (!is_array($value)) ?
                        sprintf(" AND %s = %s", $field, $value) : sprintf(" AND %s %s %s", $value[0], $value[2], $value[1]);

            } else if (is_string($config['relationship_more'])) {
                $joinOnMore .= "AND " . $config['relationship_more'];
            }
        }
        //собирает условие в одну кучу джойна
        if (strpos($config['relationship'][1],'.') === false){
            $return = sprintf("%s.%s  =  %s %s",
                $this->engine->getTable(), $config['relationship'][1],
                $joinOn, $joinOnMore
            );
        } else {
            $return = sprintf("%s  =  %s %s",
                $config['relationship'][1],
                $joinOn, $joinOnMore
            );
        }
        return  (isset($config['pаrams_filter']))?$this->replaceParamsFlag($return,$config['pаrams_filter']):$return;
    }
    private function replaceParamsFlag($strSql,$params){
        foreach ($params as $key=>$value)
            $strSql = (is_callable($value))? str_replace("%f_".$key,$value($this),$strSql):str_replace("%f_".$key,$value,$strSql);
        return $strSql;
    }
    //ДЛЯ СПИСКА ПОДКЛЮЧЕНИЕ ДРУГИХ ТАБЛИЦ ПО ПАРАМЕТРАМ ФИЛЬТРОВ
    private function connectEntityJoin(){
        $existFilte = $this->coreParams->getAllParams();
        foreach ($existFilte as $key => $valFilter) {
            $optionFiltret = $this->getPropertyFilterByParam($key);
            if($optionFiltret) {
                // если массив запиывается нсколько
                $join = (key_exists('relatedModel', $optionFiltret)) ? $optionFiltret['relatedModel'] : false;
                if (is_array($join)) {
                    foreach ($join as $item) {
                        if ($item && !in_array($item, $this->join_by))
                            $this->join_by[$item] = $item;
                    }
                } else {
                    if ($join && !in_array($join, $this->join_by))
                        $this->join_by[$join] = $join;
                }
            }

        }

        $this->filterJionQuery();
        return $this;
    }
    //СЕЛЕКТ ПО УМОЛЧАНИЮ
    protected function defaultSelect(){
        return $this->default;
    }
    //ПАРАМЕТРЫ ФИЛЬТРАЦИИ
    protected function getFilter(){
        $this->filter =[
            [ 'field'=>$this->engine->getTable().'.id','params'=>'id',
                'type'=>'string','action'=>"=",'concat'=>"AND",'validate' =>['number'=>true,'empty'=>true]],
            [   'params'=>'page','type'=>'string','validate' =>['number'=>true,'empty'=>true]],
            [   'params'=>'pageSize','type'=>'string','validate' =>['string'=>true,'empty'=>true]],
            [   'params'=>'sort_by','type'=>'string|array','validate' =>["template" =>"/.*/",'empty'=>true]],
            [   'params'=>'sort_dir','type'=>'string','validate' =>['list'=>['asc','desc'],'empty'=>true]],
            [   'params' => 'select',   'type' => 'array','validate' => ["array" =>true,'empty'=>true]],
        ];
        return  $this->filter;
    }
    private function checkInsertColumn($data,$type = self::INSERT){
          $column = $this->engine->getFillable();
          $column1 = array_keys($data);
          if($type == self::INSERT || $type == self::INSERT_IGNOR){
              return (count(array_diff($column1,$column)) == 0)?true:false;
          }else{
              return (count(array_diff($column1[0],$column)) == 0)?true:false;
          }
    }

    protected function claerQuery(){
        $this->coreParams = new CoreParam();
        $this->coreParams->setParams($this->params);
        $this->coreParams->setRules($this->filter);
        $this->checkJoin = [];
        $this->query = $this->engine->newQuery();
        return $this;
    }
    public function debug() {$this->debugQuery();}
    private function debugQuery(){
        var_dump($this->queryText());
        echo "<br>";
    }

    public static function getTable(){
        return (new static())->engine->getTable();
    }
}
