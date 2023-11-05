<?php


namespace App\ModelAdmin\ImportExport;


class ExportFronDBCSV {
    private array $data = [];
    private array $column = [];
    private string $fileName = "";
    private string $resurnData ="";


    public function setFileName(string $name){
        $this->fileName = $name;
    }
    public function setDataFrom(array $data,$addColumn = []){
        $this->data = $data;
        if(isset($data[0])){
           if(is_array($data[0]))
            $this->column = array_keys($data[0]);
        }
        if(count($addColumn)){
            $this->column = array_merge($this->column,$addColumn);
        }
        return $this;
    }
    private function buildData(){
        if(count($this->column)){
            $this->resurnData = '"' . implode('","', $this->column) . '"' . PHP_EOL;
        }
        foreach ($this->data as $item) {
            if(is_array($item)) {
                foreach ($item as $key__ => $val) {
                    $item[$key__] = $val;
                }
                $this->resurnData .= '"' . implode('","', $item) . '"' . PHP_EOL;
           } else {
                $this->resurnData .= '"'.$item.'"' . PHP_EOL;
           }
        }
        return $this->resurnData;
    }
    public function getFileName(){
        return $this->fileName.".csv";
    }

    public function getHeader(){
        $now = gmdate("D, d M Y H:i:s");
        return [
        "Expires"=>" Tue, 03 Jul 2001 06:00:00 GMT",
        "Cache-Control"=> "max-age=0, no-cache, must-revalidate, proxy-revalidate",
        "Last-Modified" => "{$now} GMT",
        "Content-Type" => "application/force-download",
        "Content-Type"=> "application/octet-stream",
        "Content-Type"=> "application/download",
        "Content-Disposition" =>"attachment;filename={$this->fileName}.csv",
        "Content-Transfer-Encoding"=> "binary",
        ];
    }

    public function run(){
        $this->buildData();
        return $this->resurnData;
    }
}
